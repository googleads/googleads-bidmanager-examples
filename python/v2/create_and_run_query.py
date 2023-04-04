#!/usr/bin/python
#
# Copyright 2023 Google LLC
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#    https://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

"""This example creates and runs a query, downloading the resulting report."""

import argparse
import sys

from googleapiclient.errors import HttpError
from retry.api import retry_call

import util


# The following values control retry behavior while the report is processing.
# Minimum amount of time between polling requests. Defaults to 5 seconds.
MIN_RETRY_INTERVAL = 5
# Maximum amount of time between polling requests. Defaults to 5 minutes.
MAX_RETRY_INTERVAL = 5 * 60
# Maximum number of requests to make when polling. Defaults to 100 requests.
MAX_RETRY_COUNT = 100


def main(service, flags):
    """Creates and runs a query and downloads the resulting report file.

    Args:
      service: the googleapiclient.discovery.Resource instance used to interact
        with the Bid Manager API.
      flags: the parsed command-line arguments.

    Raises:
      RuntimeError: If report is not done generating after the maximum number
        of polling requests.
      HttpError: If an API request is not made successfully.
    """

    # Build list of filter pairs.
    filters = []
    if flags.campaign_filter_ids != None:
        filters = [
            {"type": "FILTER_MEDIA_PLAN", "value": id}
            for id in flags.campaign_filter_ids
        ]
    filters.append(
        {"type": "FILTER_ADVERTISER", "value": flags.advertiser_id_filter}
    )

    # Create a query object with basic dimension and metrics values.
    query_obj = {
        "metadata": {
            "title": flags.title,
            "dataRange": {"range": "LAST_7_DAYS"},
            "format": "CSV",
        },
        "params": {
            "type": "STANDARD",
            "groupBys": [
                "FILTER_ADVERTISER_NAME",
                "FILTER_ADVERTISER",
                "FILTER_ADVERTISER_CURRENCY",
                "FILTER_INSERTION_ORDER_NAME",
                "FILTER_INSERTION_ORDER",
                "FILTER_LINE_ITEM_NAME",
                "FILTER_LINE_ITEM",
            ],
            "filters": filters,
            "metrics": [
                "METRIC_IMPRESSIONS",
                "METRIC_BILLABLE_IMPRESSIONS",
                "METRIC_CLICKS",
                "METRIC_CTR",
                "METRIC_TOTAL_CONVERSIONS",
                "METRIC_LAST_CLICKS",
                "METRIC_LAST_IMPRESSIONS",
                "METRIC_REVENUE_ADVERTISER",
                "METRIC_MEDIA_COST_ADVERTISER",
            ],
        },
        "schedule": {"frequency": "ONE_TIME"},
    }

    # Create query object.
    query_response = service.queries().create(body=query_obj).execute()

    # Log query creation.
    print(f'Query {query_response["queryId"]} was created.')

    # Run query asynchronously.
    report_response = (
        service.queries()
        .run(queryId=query_response["queryId"], synchronous=False)
        .execute()
    )

    # Log information on running report.
    print(
        f'Query {report_response["key"]["queryId"]} is running, report '
        f'{report_response["key"]["reportId"]} has been created and is '
        "currently being generated."
    )

    # Configure the queries.reports.get request.
    get_request = (
        service.queries()
        .reports()
        .get(
            queryId=report_response["key"]["queryId"],
            reportId=report_response["key"]["reportId"],
        )
    )

    # Get current status of operation with exponential backoff retry logic.
    report = retry_call(
        poll_report,
        fargs=[get_request],
        exceptions=RuntimeError,
        tries=MAX_RETRY_COUNT,
        delay=MIN_RETRY_INTERVAL,
        max_delay=MAX_RETRY_INTERVAL,
        backoff=2,
        jitter=(0, 60),
    )

    if report["metadata"]["status"]["state"] == "FAILED":
        print(f'Report {report["key"]["reportId"]} finished in error.')
        sys.exit(1)

    print(
        f'Report {report["key"]["reportId"]} generated successfully. Now '
        "downloading."
    )

    # Download generated report file to the given output file.
    util.download_file_from_cloud_storage(
        report["metadata"]["googleCloudStoragePath"], flags.output_file
    )


def poll_report(getRequest):
    """Polls the given report and returns it if finished.

    Args:
      getRequest: the Bid Manager API queries.reports.get request object.

    Returns:
      The finished report.

    Raises:
      RuntimeError: If report is not finished.
    """

    print("Polling report...")

    # Get current status of operation.
    report = getRequest.execute()

    # Check if report is done.
    if (
        report["metadata"]["status"]["state"] != "DONE"
        and report["metadata"]["status"]["state"] != "FAILED"
    ):
        raise RuntimeError(
            "Report polling unsuccessful. Report is still running."
        )

    return report


if __name__ == "__main__":
    # Declare command-line flags.
    argparser = argparse.ArgumentParser(add_help=False)

    # General authentication flags.
    argparser.add_argument(
        "--use_service_account",
        action="store_true",
        help="Authenticate the requests using a service account.",
    )

    # Flags specific to the operation.
    argparser.add_argument(
        "title", help="The title of the query to be created."
    )
    argparser.add_argument(
        "advertiser_id_filter",
        help="The advertiser ID to assign as a filter for the query to be "
        "created.",
    )
    argparser.add_argument(
        "output_file",
        help="The desired path for the report that will be downloaded.",
    )
    argparser.add_argument(
        "--campaign_filter_ids",
        nargs="*",
        help="One or more campaign IDs to assign as filters for the query to be "
        "created. Multiple values can be listed after declaring the "
        'argument. Ex: "--campaign_filter-ids 11111 22222 33333"',
    )

    # Retrieve command line arguments.
    flags = util.get_arguments(sys.argv, __doc__, parents=[argparser])

    # Authenticate and construct service.
    service = util.get_service(
        version="v2", useServiceAccount=flags.use_service_account
    )

    try:
        main(service, flags)
    except HttpError as e:
        print(e)
        sys.exit(1)
