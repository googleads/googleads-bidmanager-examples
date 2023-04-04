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

"""This example downloads the most recent finished report under a query."""

import argparse
import sys

from googleapiclient.errors import HttpError

import util


# Set the maximum number of pages to report list responses to retrieve.
MAX_REPORT_LIST_PAGES = 10


def main(service, flags):
    """Downloads the most recent successfully finished report under a query.

    Args:
      service: the googleapiclient.discovery.Resource instance used to interact
        with the Bid Manager API.
      flags: the parsed command-line arguments.

    Raises:
      HttpError: If an API request is not made successfully.
    """
    most_recent_report = None
    next_page_token = ""

    # Retrieve consecutive pages of reports under the query.
    # Loop breaks when a finished report is found.
    for x in range(MAX_REPORT_LIST_PAGES):
        # Execute the list request.
        # Order reports by descending report ID to retrieve newest reports first.
        list_response = (
            service.queries()
            .reports()
            .list(
                queryId=flags.query_id,
                orderBy="key.reportId desc",
                pageToken=next_page_token,
            )
            .execute()
        )

        # Break loop if no reports are found.
        if "reports" in list_response:
            # Iterate over reports until one is finished successfully.
            for report in list_response["reports"]:
                if (
                    report.get("metadata", {})
                    .get("status", {})
                    .get("state", "")
                    == "DONE"
                ):
                    most_recent_report = report
                    break
        else:
            break

        # Retrieve token for next page.
        # Break loop if not found, as there are no more reports to retrieve.
        if "nextPageToken" in list_response:
            next_page_token = list_response["nextPageToken"]
        else:
            break

    if most_recent_report is None:
        print(
            "No reports have been successfully generated for query "
            f"{flags.query_id}."
        )
        return

    print(f'Downloading report {most_recent_report["key"]["reportId"]}.')

    # Download generated report to the given output file.
    util.download_file_from_cloud_storage(
        most_recent_report["metadata"]["googleCloudStoragePath"],
        flags.output_file,
    )
    print(
        f'Report {most_recent_report["key"]["reportId"]} successfully '
        f"downloaded to {flags.output_file}."
    )


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
        "query_id", help="The title of the query to be created."
    )
    argparser.add_argument(
        "output_file",
        help="The desired path for the report that will be downloaded.",
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
