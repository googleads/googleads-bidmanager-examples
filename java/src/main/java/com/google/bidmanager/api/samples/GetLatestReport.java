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
import com.google.api.client.http.GenericUrl;
import com.google.api.services.doubleclickbidmanager.DoubleClickBidManager;
import com.google.api.services.doubleclickbidmanager.model.ListQueriesResponse;
import com.google.api.services.doubleclickbidmanager.model.Query;

import java.io.BufferedReader;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStream;

/**
 * DBM users typically create scheduled reports, where the advertiser uses 
 * either the UI or API to create a query that will be used to generate reports
 * based on a daily, weekly, or monthly schedule.
 *
 * In this example for a daily query we check to see if a report has run in
 * the last 12 hours and, if it has, download the data to a local csv file.
 *
 */

public class GetLatestReport {
  // Twelve hours in milliseconds: in this example, reports created after this
  // date are considered fresh.
  private static final long CURRENT_REPORT_WINDOW_MS = 12 * 60 * 60 * 1000;

  public static void main(String[] args) throws Exception {
    // Get an authenticated connection to the API.
    DoubleClickBidManager service = SecurityUtilities.getAPIService();

    long queryId = getQueryId();
    if (queryId == 0) {
      // Call the API, getting a list of queries.
      ListQueriesResponse queryListResponse = service.queries()
          .listqueries().execute();
      // Print them out.
      System.out.println("Id\t\tName");
      for (Query q : queryListResponse.getQueries()) {
        System.out.format("%s\t%s%n", q.getQueryId(), 
            q.getMetadata().getTitle());
      }
    } else {
      // Call the API, getting the latest status for the passed queryId.
      Query queryResponse = service.queries().getquery(queryId).execute();

      // If it is recent enough ...
      if (queryResponse.getMetadata().getLatestReportRunTimeMs()
          > java.lang.System.currentTimeMillis() - CURRENT_REPORT_WINDOW_MS) {
        // Grab the report.
        GenericUrl reportPath = new GenericUrl(queryResponse.getMetadata()
            .getGoogleCloudStoragePathForLatestReport());
        OutputStream out = new FileOutputStream(queryResponse.getQueryId() 
            + ".csv");
        MediaHttpDownloader downloader = new MediaHttpDownloader(
            SecurityUtilities.getTransport(), null);
        downloader.download(reportPath, out);
        System.out.println("Download complete.");
      } else {
        System.out.format("No reports for query Id %s in the last 12 hours.", 
            queryId);
      }
    }
  }

  protected static Long getQueryId() throws IOException {
    System.out.printf("Enter the query id or press enter to list queries%n");
    String input = readInputLine();
    if (input != null && !input.isEmpty()) {
      return Long.parseLong(input);
    } else {
      return 0L;
    }
  }

  protected static String readInputLine() throws IOException {
    BufferedReader in = new BufferedReader(new InputStreamReader(System.in));
    return in.readLine();
  }
}
