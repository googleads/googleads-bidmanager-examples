/*
 * Copyright (c) 2015 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License
 * is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing permissions and limitations under
 * the License.
 */

package com.google.bidmanager.api.samples;

import com.google.api.client.googleapis.media.MediaHttpDownloader;
import com.google.api.client.googleapis.util.Utils;
import com.google.api.client.http.GenericUrl;
import com.google.api.client.http.HttpTransport;
import com.google.api.services.doubleclickbidmanager.DoubleClickBidManager;
import com.google.api.services.doubleclickbidmanager.model.ListQueriesResponse;
import com.google.api.services.doubleclickbidmanager.model.Query;
import com.google.common.base.Strings;
import java.io.BufferedReader;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStream;

/**
 * DBM users typically create scheduled reports, where the advertiser uses either the UI or API to
 * create a query that will be used to generate reports based on a daily, weekly, or monthly
 * schedule.
 *
 * In this example for a daily query we check to see if a report has run in the last 12 hours
 * and, if it has, download the data to a local CSV file.
 */
public class GetLatestReport {
  // Twelve hours in milliseconds.
  // In this example, reports created after this date are considered fresh.
  private static final long CURRENT_REPORT_WINDOW_MS = 12 * 60 * 60 * 1000;

  private static final HttpTransport HTTP_TRANSPORT = Utils.getDefaultTransport();

  public static void main(String[] args) throws Exception {
    // Get an authenticated connection to the API.
    DoubleClickBidManager service = DoubleClickBidManagerFactory.getInstance();

    long queryId = getQueryId();
    if (queryId == 0) {
      // Call the API, getting a list of queries.
      ListQueriesResponse queryListResponse = service.queries().listqueries().execute();
      // Print them out.
      System.out.println("Id\t\tName");
      // Starting with the first page.
      printQueries(queryListResponse);
      // Then all other pages.
      while (queryListResponse.getNextPageToken() != null
          && !queryListResponse.getNextPageToken().isEmpty()) {
        queryListResponse =
            service
                .queries()
                .listqueries()
                .setPageToken(queryListResponse.getNextPageToken())
                .execute();
        printQueries(queryListResponse);
      }
    } else {
      // Call the API, getting the latest status for the passed queryId.
      Query query = service.queries().getquery(queryId).execute();
      // If it is recent enough...
      if (query.getMetadata().getLatestReportRunTimeMs()
          > java.lang.System.currentTimeMillis() - CURRENT_REPORT_WINDOW_MS) {
        // Retrieve the download URL for the latest report.
        GenericUrl reportUrl =
            new GenericUrl(query.getMetadata().getGoogleCloudStoragePathForLatestReport());

        // Download the report file.
        try (OutputStream output = new FileOutputStream(query.getQueryId() + ".csv")) {
          MediaHttpDownloader downloader = new MediaHttpDownloader(HTTP_TRANSPORT, null);
          downloader.download(reportUrl, output);
        }

        System.out.println("Download complete.");
      } else {
        System.out.format("No reports for query Id %s in the last 12 hours.", queryId);
      }
    }
  }

  private static void printQueries(ListQueriesResponse queryListResponse) {
    for (Query query : queryListResponse.getQueries()) {
      System.out.format("%s\t%s%n", query.getQueryId(), query.getMetadata().getTitle());
    }
  }

  private static long getQueryId() throws IOException {
    System.out.printf("Enter the query ID or press enter to list queries.%n");

    String input = readInputLine();
    if (!Strings.isNullOrEmpty(input)) {
      return Long.parseLong(input);
    }

    return 0L;
  }

  private static String readInputLine() throws IOException {
    try (BufferedReader in = new BufferedReader(new InputStreamReader(System.in))) {
      return in.readLine();
    }
  }
}
