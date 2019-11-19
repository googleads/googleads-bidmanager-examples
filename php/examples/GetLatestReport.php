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
 * DBM users typically create scheduled reports, where the advertiser uses
 * either the UI or API to create a query that will be used to generate reports
 * based on a daily, weekly, or monthly schedule.
 *
 * In this example for a daily query we check to see if a report has run in
 * the last 12 hours and, if it has, download the data to a local csv file.
 */
class GetLatestReport extends BaseExample {
  // Twelve hours in milliseconds: in this example, reports created after this
  // date are considered fresh.  Note: 1000 * 60 * 60 * 12 = 43200000
  const CURRENT_REPORT_WINDOW_MS = 43200000;

  protected function getInputParameters() {
    return array(array(
      'name' => 'query_id',
      'display' => 'Query id',
      'required' => false
    ));
  }

  protected function printQueries($queries) {
    foreach ($queries as $query) {
      printf('<p>%s %s</p>', $query->queryId, $query->metadata->title);
    }
  }

  /**
   * (non-PHPdoc)
   * @see BaseExample::run()
   */
  public function run() {
    $values = $this->formValues;

    $queryId = $values['query_id'];

    if ($queryId === '' || $queryId === 0) {
      // Call the API, getting a list of queries.
      $result = $this->service->queries->listqueries();
      if (isset($result['queries']) && count($result['queries']) > 0) {
        print '<pre>';
        $this->printQueries($result['queries']);
        while (!is_null($result->nextPageToken)
            && !empty($result->nextPageToken)) {
          $result = $this->service->queries->listqueries(
              array('pageToken' => $result->nextPageToken));
          $this->printQueries($result['queries']);
        }
        print '</pre>';
      } else {
        print '<p>No reports found</p>';
      }
    } else {
      // Call the API, getting the latest status for the passed queryId.
      $query = $this->service->queries->getquery($queryId);

      if ($query->metadata->latestReportRunTimeMs > microtime(true) * 1000 -
          self::CURRENT_REPORT_WINDOW_MS) {
        // Grab the report.
        file_put_contents($query->queryId . '.csv',
            fopen($query->metadata->googleCloudStoragePathForLatestReport,
                'r'));
        print '<p>Download complete.</p>';
      } else {
        printf('<p>No reports for query Id %s in the last 12 hours.</p>',
            $queryId);
      }
    }
  }

  /**
   * (non-PHPdoc)
   * @see BaseExample::getName()
   */
  public function getName() {
    return 'Get latest report';
  }
}
