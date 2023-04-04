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

package com.google.bidmanager.api.samples.utils;

import com.google.api.client.googleapis.media.MediaHttpDownloader;
import com.google.api.client.googleapis.util.Utils;
import com.google.api.client.http.GenericUrl;
import com.google.api.client.http.HttpTransport;
import java.io.FileOutputStream;
import java.io.OutputStream;

/** This class implements file download utility methods used across samples. */
public class DownloadUtils {

  private static final HttpTransport HTTP_TRANSPORT = Utils.getDefaultTransport();

  /**
   * Download file from Google Cloud Storage.
   *
   * @throws Exception If an error occurs while downloading the file
   */
  public static void downloadFileFromCloudStorage(String cloudStoragePath, String outputFile)
      throws Exception {

    GenericUrl reportUrl = new GenericUrl(cloudStoragePath);

    // Download the report file.
    try (OutputStream output = new FileOutputStream(outputFile)) {
      MediaHttpDownloader downloader = new MediaHttpDownloader(HTTP_TRANSPORT, null);
      downloader.download(reportUrl, output);
    }

    System.out.println("Download complete.");
  }
}
