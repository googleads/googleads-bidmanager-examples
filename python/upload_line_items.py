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

"""This example demonstrates how to upload line items.

This sample uploads a CSV file containing modified line items. See
download_line_items.py for additional details.

Note: The dry_run argument is set to True by default, meaning that no actual
changes will result from making the API call. You can set the value to False
when running this script or update the default value of DRYRUN to False after
initial testing.
"""

import argparse
import os
import util


# Optional filtering arguments.
parser = argparse.ArgumentParser(description='Upload line items from the '
                                 'given file path with the authenticated '
                                 'account.')
parser.add_argument('--file_path', required=False,
                    default=('%s/line_items.csv' % os.path.dirname(
                        os.path.realpath(__file__))),
                    help=('The file containing line items being uploaded.'))
parser.add_argument('--dry_run', default=True, type=bool,
                    help=('A boolean indicating whether running this sample '
                          'will make changes. No changes will occur if this '
                          'is set True.'))
parser.add_argument('--client_id', required=False,
                    help=('Your client ID from the Google Developers Console.'
                          'This should be provided along with the '
                          'client_secret the first time you run an example.'))
parser.add_argument('--client_secret', required=False,
                    help=('Your client secret from the Google Developers '
                          'Console. This should be provided along with the '
                          'client_id the first time you run an example.'))


def main(doubleclick_bid_manager, body):
  # Construct the request.
  request = doubleclick_bid_manager.lineitems().uploadlineitems(body=body)
  response = request.execute()

  if 'uploadStatus' in response and 'errors' in response['uploadStatus']:
    for error in response['uploadStatus']['errors']:
      print error
  else:
    print 'Upload Successful.'


if __name__ == '__main__':
  args = parser.parse_args()

  file_path = args.file_path
  if not os.path.isabs(file_path):
    file_path = os.path.expanduser(file_path)

  with open(file_path, 'rb') as handle:
    line_items = handle.read()

  BODY = {
      'dryRun': args.dry_run,
      'lineItems': line_items
  }

  main(util.get_service(
      client_id=args.client_id, client_secret=args.client_secret), BODY)

