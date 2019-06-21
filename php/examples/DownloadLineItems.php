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
 * In DoubleClick Bid Manager, line items bid on impressions and deliver ads
 * through exchanges and networks.  Once your line items are setup in the UI,
 * you can use the API to download a filtered list of line items to a csv file
 * which can then be modified and re-uploaded using UploadLineItems.
 */
class DownloadLineItems extends BaseExample {
  // path for downloaded line items file
  const LINE_ITEMS_FILE = 'line_items.csv';
  /**
   * (non-PHPdoc)
   * @see BaseExample::run()
   */
  public function run() {
    print '<h2>Download line items</h2>';

    // Setup any filtering on the API request
    $dliRequest = new
        Google_Service_DoubleClickBidManager_DownloadLineItemsRequest();
    /* If your download requests times out you may need to filter to reduce the
     * number of items returned - the commented code below filters on
     * ADVERTISER_ID, refer to the reference guide at
     * https://developers.google.com/bid-manager/ for other options.
     */
    // $dliRequest->setFilterType("ADVERTISER_ID");
    // $dliRequest->setFilterIds(array(0, 1, 2));
    try {
      $result = $this->service->lineitems->downloadlineitems($dliRequest);
    } catch (Google_Service_Exception $e) {
      printf('<p>Exception: %s</p>', $e->getMessage());
      print '<p>Consider filtering by ADVERTISER_ID</p>';
      return;
    } catch (Exception $e) {
      printf('<p>Exception: %s</p>', $e->getMessage());
      return;
    }

    if (empty($result['lineItems'])) {
      print '<p>No items found</p>';
      return;
    } else {
       file_put_contents(self::LINE_ITEMS_FILE, $result->lineItems);
       print '<p>Download complete</p>';
    }
  }

  /**
   * (non-PHPdoc)
   * @see BaseExample::getName()
   */
  public function getName() {
    return 'Download line items';
  }
}

