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
import com.google.api.client.util.ExponentialBackOff;
import com.google.api.services.doubleclickbidmanager.DoubleClickBidManager;
import com.google.api.services.doubleclickbidmanager.DoubleClickBidManager.Queries.Reports;
import com.google.api.services.doubleclickbidmanager.model.DataRange;
import com.google.api.services.doubleclickbidmanager.model.FilterPair;
import com.google.api.services.doubleclickbidmanager.model.Parameters;
import com.google.api.services.doubleclickbidmanager.model.Query;
import com.google.api.services.doubleclickbidmanager.model.QueryMetadata;
import com.google.api.services.doubleclickbidmanager.model.QuerySchedule;
import com.google.api.services.doubleclickbidmanager.model.Report;
import com.google.api.services.doubleclickbidmanager.model.RunQueryRequest;
import com.google.bidmanager.api.samples.utils.ArgumentNames;
import com.google.bidmanager.api.samples.utils.CodeSampleParams;
import com.google.bidmanager.api.samples.utils.DownloadUtils;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

/**
 * This example creates a new basic query, runs the query, and downloads the report once finished.
 */
public class CreateAndRunQuery {
  /** Minimum amount of time between polling requests. Defaults to 5 seconds. */
  private static final int MIN_RETRY_INTERVAL_IN_MILLIS = 5_000;

  /** Maximum amount of time between polling requests. Defaults to 5 minutes. */
  private static final int MAX_RETRY_INTERVAL_IN_MILLIS = 5 * 60_000;

  /** Maximum amount of time to spend polling. Defaults to 5 hours. */
  private static final int MAX_RETRY_ELAPSED_TIME_IN_MILLIS = 5 * 60 * 60_000;

  private static class CreateAndRunQueryParams extends CodeSampleParams {

    @Parameter(
        names = ArgumentNames.TITLE,
        description = "The title of the query to be created.",
        required = true)
    public String title;

    @Parameter(
        names = ArgumentNames.ADVERTISER_ID_FILTER,
        description = "The advertiser ID to assign as a filter for the query to be created.",
        required = true)
    public String advertiserIdFilter;

    @Parameter(
        names = ArgumentNames.CAMPAIGN_ID_FILTERS,
        description = "The campaign IDs to assign as filters for the query to be created.")
    public List<String> campaignIdFilters;

    @Parameter(
        names = ArgumentNames.OUTPUT_FILE,
        description = "The path to download the resulting report to.",
        required = true)
    public String outputFile;
  }

  public static void main(String[] args) throws Exception {
    CreateAndRunQueryParams params = new CreateAndRunQueryParams();
    if (!params.parseArguments(args)) {
      // Either pass the required parameters for this example on the command line, or insert them
      // into the code here. See the parameter class definition above for descriptions.
      params.title = "INSERT_TITLE_HERE";
      params.advertiserIdFilter = "INSERT_ADVERTISER_ID_FILTER_HERE";
      params.campaignIdFilters = Arrays.asList("INSERT_CAMPAIGN_ID_FILTERS_HERE".split(","));
      params.outputFile = "INSERT_OUTPUT_FILE_HERE";
    }

    DoubleClickBidManager service =
        BidManagerFactory.getInstance(
            params.clientSecretsFile,
            params.useServiceAccount,
            params.serviceAccountKeyFile,
            params.additionalScopes);

    runExample(
        service,
        params.title,
        params.advertiserIdFilter,
        params.campaignIdFilters,
        params.outputFile);
  }

  public static void runExample(
      DoubleClickBidManager service,
      String title,
      String advertiserIdFilter,
      List<String> campaignIdFilters,
      String outputFile)
      throws Exception {

    // Create the query structure.
    Query query = new Query();

    // Build and set the metadata object.
    QueryMetadata metadata = new QueryMetadata();
    metadata.setTitle(title);
    metadata.setDataRange(new DataRange().setRange("LAST_7_DAYS"));
    metadata.setFormat("CSV");
    query.setMetadata(metadata);

    // Build the parameters object.
    Parameters parameters = new Parameters();
    parameters.setType("STANDARD");

    // Build a list of filters.
    List<FilterPair> filters = new ArrayList<FilterPair>();
    filters.add(new FilterPair().setType("FILTER_ADVERTISER").setValue(advertiserIdFilter));

    // Build and add campaign ID filter pairs.
    if (campaignIdFilters != null) {
      for (String campaignId : campaignIdFilters) {
        filters.add(new FilterPair().setType("FILTER_MEDIA_PLAN").setValue(campaignId));
      }
    }

    // Set filters in parameters object.
    parameters.setFilters(filters);

    // Build and assign list of standard reporting dimensions to parameters object.
    List<String> dimensions = new ArrayList<String>();
    dimensions.add("FILTER_ADVERTISER_NAME");
    dimensions.add("FILTER_ADVERTISER");
    dimensions.add("FILTER_ADVERTISER_CURRENCY");
    dimensions.add("FILTER_INSERTION_ORDER_NAME");
    dimensions.add("FILTER_INSERTION_ORDER");
    dimensions.add("FILTER_LINE_ITEM_NAME");
    dimensions.add("FILTER_LINE_ITEM");
    parameters.setGroupBys(dimensions);

    // Build and assign list of standard reporting metrics to parameters object.
    List<String> metrics = new ArrayList<String>();
    metrics.add("METRIC_IMPRESSIONS");
    metrics.add("METRIC_BILLABLE_IMPRESSIONS");
    metrics.add("METRIC_CLICKS");
    metrics.add("METRIC_CTR");
    metrics.add("METRIC_TOTAL_CONVERSIONS");
    metrics.add("METRIC_LAST_CLICKS");
    metrics.add("METRIC_LAST_IMPRESSIONS");
    metrics.add("METRIC_REVENUE_ADVERTISER");
    metrics.add("METRIC_MEDIA_COST_ADVERTISER");
    parameters.setMetrics(metrics);

    // Set parameters object in query.
    query.setParams(parameters);

    // Build and set the schedule object.
    QuerySchedule schedule = new QuerySchedule();
    schedule.setFrequency("ONE_TIME");
    query.setSchedule(schedule);

    // Create the query.
    Query queryResponse = service.queries().create(query).execute();

    // Log query creation.
    System.out.printf("Query %s was created.%n", queryResponse.getQueryId());

    // Run query to run asynchronously.
    Report reportResponse =
        service
            .queries()
            .run(queryResponse.getQueryId(), new RunQueryRequest())
            .setSynchronous(false)
            .execute();

    // Log information on running report.
    System.out.printf(
        "Query %s is running, report %s has been created and is currently being generated.%n",
        reportResponse.getKey().getQueryId(), reportResponse.getKey().getReportId());

    // Poll report, waiting for it to be finished.
    Report finishedReport = pollReportUntilFinished(service, reportResponse);

    if (finishedReport != null) {
      if (finishedReport.getMetadata().getStatus().getState().equals("DONE")) {
        System.out.printf(
            "Report %s generated successfully.%n", finishedReport.getKey().getReportId());
      } else {
        System.out.printf(
            "Report %s failed to generate. Exiting.%n", finishedReport.getKey().getReportId());
        return;
      }
    } else {
      System.out.println("Abandoning report polling. Exiting.");
      return;
    }

    // Download report file.
    System.out.println("Downloading report file.");
    DownloadUtils.downloadFileFromCloudStorage(
        finishedReport.getMetadata().getGoogleCloudStoragePath(), outputFile);
    System.out.printf(
        "Report %s successfully downloaded at %s.%n",
        finishedReport.getKey().getReportId(), outputFile);
  }

  private static Report pollReportUntilFinished(DoubleClickBidManager service, Report report)
      throws Exception {
    // Configure reports.get request.
    Reports.Get reportGetRequest =
        service
            .queries()
            .reports()
            .get(report.getKey().getQueryId(), report.getKey().getReportId());

    // Configure exponential backoff for checking the status of our report.
    ExponentialBackOff backOff =
        new ExponentialBackOff.Builder()
            .setInitialIntervalMillis(MIN_RETRY_INTERVAL_IN_MILLIS) // setting initial interval
            .setMaxIntervalMillis(MAX_RETRY_INTERVAL_IN_MILLIS) // setting max interval
            .setMaxElapsedTimeMillis(MAX_RETRY_ELAPSED_TIME_IN_MILLIS) // setting max elapsed time
            .build();

    while (!report.getMetadata().getStatus().getState().equals("DONE")
        && !report.getMetadata().getStatus().getState().equals("FAILED")) {
      long backoffMillis = backOff.nextBackOffMillis();
      if (backoffMillis == ExponentialBackOff.STOP) {
        System.out.printf(
            "The report has taken more than %s minutes to generate.%n",
            MAX_RETRY_ELAPSED_TIME_IN_MILLIS / 60_000);
        return null;
      }
      System.out.printf(
          "The report has not yet completed. Waiting %s seconds before polling report again.%n",
          backoffMillis / 1000);
      Thread.sleep(backoffMillis);

      // Get current status of operation.
      report = reportGetRequest.execute();
    }

    return report;
  }
}
