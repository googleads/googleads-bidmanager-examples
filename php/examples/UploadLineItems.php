<?php
/*
 * Copyright 2015 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// Require the base class.
require_once __DIR__ . "/../BaseExample.php";

/**
 * This sample uploads a csv file containing modified line items
 * See DownloadLineItems for details.
 * Note: DRYRUN = true in this example means that no actual changes will
 * happen - change this flag after initial testing.
 */
class UploadLineItems extends BaseExample {
  // When the DRYRUN flag is set to true, no actual changes will happen.
  const DRYRUN = true;

  // path for line items file - downloaded by DownloadLineItems
  const LINE_ITEMS_FILE = 'line_items.csv';

  /**
   * (non-PHPdoc)
   * @see BaseExample::run()
   */
  public function run() {
    print '<h2>Upload line items</h2>';
    if (self::DRYRUN) {
      print '<h3>DRYRUN is on - no changes will be made.</h3>';
    }

    if (file_exists(self::LINE_ITEMS_FILE)) {
      // Get the line items from the csv file.
      $lineItems = file_get_contents(self::LINE_ITEMS_FILE);

      // Set up the request details including the line items.
      $uliRequest = new
          Google_Service_DoubleClickBidManager_UploadLineItemsRequest();
      // When the DRYRUN flag is set to true, no actual changes will happen.
      $uliRequest->dryRun = self::DRYRUN;
      $uliRequest->lineItems = $lineItems;

      // Call the API, passing in the request details.
      $result = $this->service->lineitems->uploadlineitems($uliRequest);

      // Display the results.
      $this->printResult($result->uploadStatus);
    } else {
      printf('<p>Unable to find file: %s</p>', self::LINE_ITEMS_FILE);
      print '<p>Did you run the DownloadLineItems example?</p>';
    }
  }

  /**
   * (non-PHPdoc)
   * @see BaseExample::getName()
   */
  public function getName() {
    return 'Upload line items';
  }
}
