<?php
/*
 * Copyright 2022 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// Require the base class.
require_once __DIR__ . "/../BaseExample.php";

// Require the necessary utility class.
require_once __DIR__ . "/Utils/DownloadUtils.php";

/**
 * This example downloads the most recent finished report under a query.
 */
class GetLatestReportForQuery extends BaseExample
{
    /**
     * (non-PHPdoc)
     * @see BaseExample::getInputParameters()
     */
    protected function getInputParameters(): array
    {
        return array(
            array(
                'name' => 'query_id',
                'display' => 'Query ID',
                'required' => true
            ),
            array(
                'name' => 'output_file',
                'display' => 'Output File',
                'required' => true
            )
        );
    }

    /**
     * (non-PHPdoc)
     * @see BaseExample::run()
     */
    public function run()
    {
        $values = $this->formValues;

        $queryId = $values['query_id'];
        $outputFile = $values['output_file'];

        $mostRecentReport = null;
        $response = null;
        $nextPageToken = null;

        // Retrieve consecutive pages of reports under the query until you find
        // one that is done.
        do {
            // Set order of reports by descending report ID to retrieve newest
            // reports first.
            // Set next page token for when retrieving subsequent pages.
            $optParams = array(
                'orderBy' => 'key.reportId desc',
                'pageToken' => $nextPageToken
            );

            // Call the API, retrieving the current page of reports.
            try {
                $response = $this
                    ->service
                    ->queries_reports
                    ->listQueriesReports(
                        $queryId,
                        $optParams
                    );
            } catch (\Exception $e) {
                $this->renderError($e);
                return;
            }

            if (!empty($response->getReports())) {
                foreach($response->getReports() as $report) {
                    if (
                        $report
                            ->getMetadata()
                            ->getStatus()
                            ->getState() === "DONE"
                    ) {
                        $mostRecentReport = $report;
                        break;
                    }
                }
            }

            // Update the next page token.
            $nextPageToken = $response->getNextPageToken();
        } while (
            $mostRecentReport === null
            && !empty($nextPageToken)
        );

        if ($mostRecentReport === null) {
            printf(
                'No reports have been successfully generated for query '
                    . '%s.<br>',
                $queryId);
            return;
        }

        printf(
            'Downloading report %s<br>',
            $mostRecentReport->getKey()->getReportId()
        );

        // Download report file.
        try {
            DownloadUtils::downloadFileFromCloudStorage(
                $mostRecentReport->getMetadata()->getGoogleCloudStoragePath(),
                $outputFile
            );
        } catch (\Exception $e) {
            $this->renderError($e);
            return;
        }

        printf(
            'Report %s successfully downloaded at %s.<br>',
            $mostRecentReport->getKey()->getReportId(),
            $outputFile
        );
    }

    /**
     * (non-PHPdoc)
     * @see BaseExample::getName()
     */
    public static function getName(): string
    {
        return 'Get Latest Report For Query';
    }
}