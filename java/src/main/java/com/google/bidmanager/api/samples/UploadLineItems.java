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

import com.google.api.services.doubleclickbidmanager.DoubleClickBidManager;
import com.google.api.services.doubleclickbidmanager.model.UploadLineItemsRequest;
import com.google.api.services.doubleclickbidmanager.model.UploadLineItemsResponse;
import com.google.common.base.Charsets;
import com.google.common.io.Files;

import java.io.File;
import java.util.List;

/**
 * This sample uploads a csv file containing modified line items
 * See DownloadLineItems for details.
 * Note: DRYRUN = true in this example means that no actual changes will
 * happen - change this flag after initial testing.
 */
public class UploadLineItems {
  // When the DRYRUN flag is set to true, no actual changes will happen.
  static final boolean DRYRUN = true;

  public static void main(String[] args) throws Exception {
    // Get an authenticated connection to the API.
    DoubleClickBidManager service = SecurityUtilities.getAPIService();

    // Get the line items from the csv file.
    File from = new File("line_items.csv");
    String lineItems = Files.toString(from, Charsets.UTF_8);

    // Set up the request details including the line items.
    UploadLineItemsRequest uliContent =
        new UploadLineItemsRequest().setDryRun(DRYRUN).setLineItems(lineItems);
    // Call the API, passing in the request details.
    UploadLineItemsResponse uliResponse = service.lineitems()
        .uploadlineitems(uliContent).execute();

    // Check for errors.
    List<String> errorList = uliResponse.getUploadStatus().getErrors();
    if (errorList == null) {
      System.out.println("Upload Successful");
    } else {
      for (String e : errorList) {
        System.out.println(e);
      }
    }
  }
}
