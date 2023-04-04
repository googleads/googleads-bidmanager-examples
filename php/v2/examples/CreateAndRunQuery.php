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
 * This example creates a new basic query, runs the query, and downloads the
 * report once finished.
 */
class CreateAndRunQuery extends BaseExample
{
    // The following values control retry behavior while the report is
    // generating.
    // Minimum amount of time between polling requests. Defaults to 5 seconds.
    private const MIN_RETRY_INTERVAL = 5;
    // Maximum amount of time between polling requests. Defaults to 5 minutes.
    private const MAX_RETRY_INTERVAL = 300;
    // Maximum amount of time to spend polling. Defaults to 5 hours.
    private const MAX_RETRY_ELAPSED_TIME = 18000;

    /**
     * (non-PHPdoc)
     * @see BaseExample::getInputParameters()
     */
    protected function getInputParameters(): array
    {
        return array(
            array(
                'name' => 'title',
                'display' => 'Title',
                'required' => true
            ),
            array(
                'name' => 'advertiser_id_filter',
                'display' => 'Advertiser ID Filter',
                'required' => true
            ),
            array(
                'name' => 'campaign_id_filters',
                'display' => 'Campaign ID Filters (comma-separated)',
                'required' => false
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

        $title = $values['title'];
        $advertiserIdFilter = $values['advertiser_id_filter'];
        $campaignIdFilters = array();
        $outputFile = $values['output_file'];

        if (!empty($values['campaign_id_filters'])) {
            $campaignIdFilters = explode(",", $values['campaign_id_filters']);
        }

        $query = new Google_Service_DoubleClickBidManager_Query();

        $metadata = new Google_Service_DoubleClickBidManager_QueryMetadata();
        $metadata->setTitle($title);
        $metadata->setFormat("CSV");

        $dataRange = new Google_Service_DoubleClickBidManager_DataRange();
        $dataRange->setRange("LAST_7_DAYS");
        $metadata->setDataRange($dataRange);

        $query->setMetadata($metadata);

        $parameters = new Google_Service_DoubleClickBidManager_Parameters();
        $parameters->setType("STANDARD");

        $filters = array();
        $advertiserFilterPair =
            new Google_Service_DoubleClickBidManager_FilterPair();
        $advertiserFilterPair->setType("FILTER_ADVERTISER");
        $advertiserFilterPair->setValue($advertiserIdFilter);
        array_push($filters, $advertiserFilterPair);

        foreach ($campaignIdFilters as $campaignId) {
            $filterPair =
                new Google_Service_DoubleClickBidManager_FilterPair();
            $filterPair->setType("FILTER_MEDIA_PLAN");
            $filterPair->setValue($campaignId);
            array_push($filters, $filterPair);
        }
        $parameters->setFilters($filters);

        $parameters->setGroupBys(
            array(
                "FILTER_ADVERTISER_NAME",
                "FILTER_ADVERTISER",
                "FILTER_ADVERTISER_CURRENCY",
                "FILTER_INSERTION_ORDER_NAME",
                "FILTER_INSERTION_ORDER",
                "FILTER_LINE_ITEM_NAME",
                "FILTER_LINE_ITEM"
            )
        );

        $parameters->setMetrics(
            array(
                "METRIC_IMPRESSIONS",
                "METRIC_BILLABLE_IMPRESSIONS",
                "METRIC_CLICKS",
                "METRIC_CTR",
                "METRIC_TOTAL_CONVERSIONS",
                "METRIC_LAST_CLICKS",
                "METRIC_LAST_IMPRESSIONS",
                "METRIC_REVENUE_ADVERTISER",
                "METRIC_MEDIA_COST_ADVERTISER"
            )
        );
        $query->setParams($parameters);

        $schedule = new Google_Service_DoubleClickBidManager_QuerySchedule();
        $schedule->setFrequency("ONE_TIME");
        $query->setSchedule($schedule);

        // Call the API, creating the query.
        try {
            $queryResult = $this->service->queries->create($query);
        } catch (\Exception $e) {
            $this->renderError($e);
            return;
        }

        printf('Query %s was created.<br>', $queryResult['queryId']);

        $optParams = array(
            'synchronous' => false
        );

        // Call the API, running the query.
        try {
            $reportResult = $this->service->queries->run(
                $queryResult['queryId'],
                new Google_Service_DoubleClickBidManager_RunQueryRequest(),
                $optParams);
        } catch (\Exception $e) {
            $this->renderError($e);
            return;
        }

        // Call the API, polling the report until it is finished.
        try {
            $finishedReport = $this->pollReportUntilFinished($reportResult);
        } catch (\Exception $e) {
            $this->renderError($e);
            return;
        }

        if ($finishedReport === null) {
            print 'Unable to generate report.<br>';
            return;
        } elseif (
            $finishedReport
                ->getMetadata()
                ->getStatus()
                ->getState() === "FAILED"
        ) {
            print 'Report generation failed.<br>';
            return;
        }

        printf(
            'Report %s generated successfully.<br>',
            $finishedReport->getKey()->getReportId()
        );
        print('Downloading report file.<br>');

        // Download report file.
        try {
            DownloadUtils::downloadFileFromCloudStorage(
                $finishedReport->getMetadata()->getGoogleCloudStoragePath(),
                $outputFile
            );
        } catch (\Exception $e) {
            $this->renderError($e);
            return;
        }

        printf(
            'Report %s successfully downloaded at %s.<br>',
            $finishedReport->getKey()->getReportId(),
            $outputFile
        );
    }

    /**
     * Polls the running report to see if it is finished processing.
     * Uses an exponential backoff policy to limit retries and conserve quota.
     * @param Google_Service_DisplayVideo_Report $report the
     *     Report to be monitored for completion.
     * @return Google_Service_DisplayVideo_Report the finished report
     */
    private function pollReportUntilFinished(
        Google_Service_DoubleClickBidManager_Report $report
    ): ?Google_Service_DoubleClickBidManager_Report {
        $sleep = 0;
        $startTime = time();

        do {
            // Call the API, retrieving the report.
            $report = $this->service->queries_reports->get(
                $report->getKey()->getQueryId(),
                $report->getKey()->getReportId()
            );

            if (
                $report->getMetadata()->getStatus()->getState() === "DONE"
                || $report->getMetadata()->getStatus()->getState() === "FAILED"
            ) {
                return $report;
            } elseif (time() - $startTime > self::MAX_RETRY_ELAPSED_TIME) {
                print(
                    'The report is taking longer than the set maximum retry '
                    . 'time to generate. Exiting.<br>'
                );
                return null;
            }

            $sleep = $this->getNextSleepInterval($sleep);
            printf(
                'The operation is still running, sleeping for %d seconds<br>',
                $sleep
            );
            sleep($sleep);
        } while (true);
    }

    /**
     * Returns the next sleep interval to be used.
     * @param int $previousSleepInterval the previous sleep interval used.
     * @return int the next sleep interval to use.
     */
    private function getNextSleepInterval(int $previousSleepInterval): int
    {
        $minInterval = max(self::MIN_RETRY_INTERVAL, $previousSleepInterval);
        $maxInterval = max(
            self::MIN_RETRY_INTERVAL,
            $previousSleepInterval * 3
        );
        return min(self::MAX_RETRY_INTERVAL, rand($minInterval, $maxInterval));
    }

    /**
     * (non-PHPdoc)
     * @see BaseExample::getName()
     */
    public static function getName(): string
    {
        return 'Create And Run Query';
    }
}