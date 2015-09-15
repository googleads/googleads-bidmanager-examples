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

"""This example demonstrates how to retrieve the latest report.

DBM users typically create scheduled reports, where the advertiser user either
the UI or API to create a query that will be used to generate reports based on
a daily, weekly, or monthly schedule.

In this example, we check to see if a daily query has run in the last 12 hours.
If it has, we download the data to a local CSV file. If no queryId is provided,
this will print a list of existing queries.
"""

import argparse
from datetime import datetime
from datetime import timedelta
import os
import urllib2
import util


# Optional filtering arguments.
parser = argparse.ArgumentParser(description='Downloads a report if it has '
                                 'been created in the given timeframe.')
parser.add_argument('--client_id', required=False,
                    help=('Your client ID from the Google Developers Console.'
                          'This should be provided along with the '
                          'client_secret the first time you run an example.'))
parser.add_argument('--client_secret', required=False,
                    help=('Your client secret from the Google Developers '
                          'Console. This should be provided along with the '
                          'client_id the first time you run an example.'))
parser.add_argument('--output_directory', default=(os.path.dirname(
    os.path.realpath(__file__))), help=('Path to the directory you want to '
                                        'save the report to.'))
parser.add_argument('--query_id', default=0, type=int,
                    help=('The id of a query used to generate a report.'))
parser.add_argument('--report_window', default=12, type=int,
                    help=('The age a report must be in hours at a maximum to '
                          'be considered fresh.'))


def main(doubleclick_bid_manager, output_dir, query_id, report_window):
  if query_id:
    # Call the API, getting the latest status for the passed queryId.
    response = (doubleclick_bid_manager.queries().getquery(queryId=query_id)
                .execute())
    try:
      # If it is recent enough...
      if (is_in_report_window(response['metadata']['latestReportRunTimeMs'],
                              report_window)):
        # Grab the report and write contents to a file.
        save_report_to_file(output_dir, response['queryId'],
                            (response['metadata']
                             ['googleCloudStoragePathForLatestReport']))
        print 'Download complete.'
      else:
        print('No reports for queryId "%s" in the last %s hours.' %
              (response['queryId'], report_window))
    except KeyError:
      print'No report found for queryId "%s".' % query_id
  else:
    # Call the API, getting a list of queries.
    response = doubleclick_bid_manager.queries().listqueries().execute()
    # Print queries out.
    print 'Id\t\tName'
    if 'queries' in response:
      for q in response['queries']:
        print '%s\t%s' % (q['queryId'], q['metadata']['title'])
    else:
      print 'No queries exist.'


def is_in_report_window(run_time_ms, report_window):
  """Determines if the given time in milliseconds is in the report window.

  Args:
    run_time_ms: str containing a time in milliseconds.
    report_window: int identifying the range of the report window in hours.
  Returns:
    A boolean indicating whether the given query's report run time is within
    the report window.
  """
  report_time = datetime.fromtimestamp(int((run_time_ms))/1000)
  earliest_time_in_range = datetime.now() - timedelta(hours=report_window)
  return report_time > earliest_time_in_range


def save_report_to_file(output_dir, query_id, report_url):
  """Saves the contents of the report_url to the given output directory.

  Args:
    output_dir: str containing the path to the directory you want to save to.
    query_id: str containing the Id of the query that generated the report.
    report_url: str containing the url to the generated report.
  """
  # Create formatter for output file path.
  if not os.path.isabs(output_dir):
    output_dir = os.path.expanduser(output_dir)
  output_fmt = output_dir + '/%s.csv'
  with(open(output_fmt % query_id, 'wb')) as handle:
    handle.write(urllib2.urlopen(report_url).read())


if __name__ == '__main__':
  args = parser.parse_args()
  # Retrieve the query id of the report we're downloading, or set to 0.
  QUERY_ID = args.query_id
  if not QUERY_ID:
    try:
      QUERY_ID = int(raw_input('Enter the query id or press enter to '
                               'list queries: '))
    except ValueError:
      QUERY_ID = 0

  main(util.get_service(
      client_id=args.client_id, client_secret=args.client_secret),
       args.output_directory, QUERY_ID, args.report_window)

