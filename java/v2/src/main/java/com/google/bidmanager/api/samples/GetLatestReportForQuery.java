// Copyright 2022 Google LLC
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//    https://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

package com.google.bidmanager.api.samples;

import com.beust.jcommander.Parameter;
import com.google.api.services.doubleclickbidmanager.DoubleClickBidManager;
import com.google.api.services.doubleclickbidmanager.DoubleClickBidManager.Queries.Reports;
import com.google.api.services.doubleclickbidmanager.model.ListReportsResponse;
import com.google.api.services.doubleclickbidmanager.model.Report;
import com.google.bidmanager.api.samples.utils.ArgumentNames;
import com.google.bidmanager.api.samples.utils.CodeSampleParams;
import com.google.bidmanager.api.samples.utils.DownloadUtils;
import com.google.common.base.Strings;

/** This example downloads the most recent finished report under a query. */
public class GetLatestReportForQuery {

  private static class GetLatestReportForQueryParams extends CodeSampleParams {

    @Parameter(
        names = ArgumentNames.QUERY_ID,
        description = "The ID of the Query to download the latest report for.",
        required = true)
    public Long queryId;

    @Parameter(
        names = ArgumentNames.OUTPUT_FILE,
        description = "The path to download the resulting report to.",
        required = true)
    public String outputFile;
  }

  public static void main(String[] args) throws Exception {
    GetLatestReportForQueryParams params = new GetLatestReportForQueryParams();
    if (!params.parseArguments(args)) {
      // Either pass the required parameters for this example on the command line, or insert them
      // into the code here. See the parameter class definition above for descriptions.
      params.queryId = Long.valueOf("INSERT_QUERY_ID_HERE");
      params.outputFile = "INSERT_OUTPUT_FILE_HERE";
    }

    DoubleClickBidManager service =
        BidManagerFactory.getInstance(
            params.clientSecretsFile,
            params.useServiceAccount,
            params.serviceAccountKeyFile,
            params.additionalScopes);

    runExample(service, params.queryId, params.outputFile);
  }

  public static void runExample(DoubleClickBidManager service, long queryId, String outputFile)
      throws Exception {

    Report mostRecentReport = null;

    // Build the queries.reports.list request
    Reports.List reportListRequest = service.queries().reports().list(queryId);

    // Order reports by descending report ID to retrieve newest reports first.
    reportListRequest = reportListRequest.setOrderBy("key.reportId desc");

    // Create the response and nextPageToken variables.
    ListReportsResponse response;
    String nextPageToken = null;

    // Retrieve consecutive pages of reports under the query until you find one that is done.
    do {
      // Create and execute the list request.
      response = reportListRequest.setPageToken(nextPageToken).execute();

      // Check if response is empty.
      if (!response.isEmpty()) {
        for (Report report : response.getReports()) {
          if (report.getMetadata().getStatus().getState().equals("DONE")) {
            mostRecentReport = report;
            break;
          }
        }
      }

      // Update the next page token.
      nextPageToken = response.getNextPageToken();
    } while (mostRecentReport == null && !Strings.isNullOrEmpty(nextPageToken));

    if (mostRecentReport == null) {
      System.out.printf(
          "No reports have been successfully generated for query %s. Exiting.%n", queryId);
      return;
    }

    // Download report file.
    System.out.printf("Downloading report %s.%n", mostRecentReport.getKey().getReportId());

    DownloadUtils.downloadFileFromCloudStorage(
        mostRecentReport.getMetadata().getGoogleCloudStoragePath(), outputFile);
    System.out.printf(
        "Report %s successfully downloaded to %s.%n",
        mostRecentReport.getKey().getReportId(), outputFile);
  }
}
