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

/**
 * Helper class to download files.
 */
class DownloadUtils
{
    /**
     * Downloads a file on Google Cloud Storage to the given file location.
     * @param string $cloudStoragePath the ID of the advertiser to upload the
     *     file and create the asset beneath.
     * @param string $outputFile the file to upload.
     */
    public static function downloadFileFromCloudStorage(
        string $cloudStoragePath,
        string $outputFile
    ) {
        // Download the file.
        file_put_contents($outputFile, fopen($cloudStoragePath, 'r'));
        return;
    }
}