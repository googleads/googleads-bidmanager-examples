#!/usr/bin/python
#
# Copyright 2015 Google Inc. All Rights Reserved.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#      http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

"""This example demonstrates how to download line items.

In DoubleClick Bid Manager, line items bid on impressions and deliver ads
through exchanges and networks. Once your line items are setup in the UI, you
can use the API to download a filtered list of line items to a csv file which
can then be modified and re-uploaded using upload_line_items.py.
"""

import argparse
import os
import sys
import util


# Optional filtering arguments.
parser = argparse.ArgumentParser(add_help=False,
                                 description='Downloads line items associated '
                                             'with the authenticated account.')
parser.add_argument('--file_path', default=('%s/line_items.csv'
                                            % os.path.dirname(
                                                os.path.realpath(__file__))),
                    help=('Path to the file you want to download the line '
                          'items to.'))
parser.add_argument('--filter_ids', required=True,
                    help=('Filter by the filter type ids. Provide them '
                          'separated by a comma with no spaces.'))
parser.add_argument('--filter_type', required=True,
                    help=('The type of filter you would like to use. Valid '
                          'options are: ADVERTISER_ID, INSERTION_ORDER_ID, '
                          'and LINE_ITEM_ID.'))


def main(doubleclick_bid_manager, file_path, body):
  # Construct the request.
  request = doubleclick_bid_manager.lineitems().downloadlineitems(body=body)

  # Execute request and save response contents.
  with open(file_path, 'wb') as handler:
    # Call the API, getting the (optionally filtered) list of line items.
    # Then write the contents of the response to a CSV file.
    handler.write(request.execute()['lineItems'].encode('utf-8'))
    print('Download complete.')


if __name__ == '__main__':
  request_body = {}
  valid_filter_types = ['ADVERTISER_ID', 'INSERTION_ORDER_ID', 'LINE_ITEM_ID']

  # If your download requests time out, you may need to filter to reduce the
  # number of items returned. Below we parse optional arguments to add these
  # filters to the body of the request.
  args = util.get_arguments(sys.argv, __doc__, parents=[parser])

  path = args.file_path
  if not os.path.isabs(path):
    FILE_PATH = os.path.expanduser(path)
  if args.filter_ids:
    request_body['filterIds'] = args.filter_ids.split(',')
  if args.filter_type and args.filter_type.upper() in valid_filter_types:
    request_body['filterType'] = args.filter_type
  else:
      raise ValueError('Invalid filterType. Acceptable values: %s' %
                       valid_filter_types)

  main(util.setup(args), path, request_body)
