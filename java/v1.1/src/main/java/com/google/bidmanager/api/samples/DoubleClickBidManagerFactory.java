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

import static java.nio.charset.StandardCharsets.UTF_8;

import com.google.api.client.auth.oauth2.Credential;
import com.google.api.client.extensions.java6.auth.oauth2.AuthorizationCodeInstalledApp;
import com.google.api.client.extensions.jetty.auth.oauth2.LocalServerReceiver;
import com.google.api.client.googleapis.auth.oauth2.GoogleAuthorizationCodeFlow;
import com.google.api.client.googleapis.auth.oauth2.GoogleClientSecrets;
import com.google.api.client.googleapis.util.Utils;
import com.google.api.client.http.HttpRequest;
import com.google.api.client.http.HttpRequestInitializer;
import com.google.api.client.http.HttpTransport;
import com.google.api.client.json.JsonFactory;
import com.google.api.client.util.store.DataStoreFactory;
import com.google.api.client.util.store.FileDataStoreFactory;
import com.google.api.services.doubleclickbidmanager.DoubleClickBidManager;
import com.google.api.services.doubleclickbidmanager.DoubleClickBidManagerScopes;
import java.io.IOException;
import java.io.InputStreamReader;

/** Utility class used to handle OAuth and service creation for all DBM API samples. */
public class DoubleClickBidManagerFactory {
  /** Directory to store user credentials. */
  private static final java.io.File DATA_STORE_DIR =
      new java.io.File(System.getProperty("user.home"), ".store/dbm_sample");

  /** HTTP read timeout for DBM API requests (in ms). Defaults to 3 minutes. * */
  private static final int HTTP_READ_TIMEOUT_IN_MILLIS = 3 * 60_000;

  private static final HttpTransport HTTP_TRANSPORT = Utils.getDefaultTransport();
  private static final JsonFactory JSON_FACTORY = Utils.getDefaultJsonFactory();

  /**
   * Be sure to specify the name of your application. If the application name is {@code null} or
   * blank, the application will log a warning. Suggested format is "MyCompany-ProductName/1.0".
   */
  private static final String APPLICATION_NAME = "APPLICATION_NAME_HERE";

  /**
   * Authorizes the installed application to access user's protected data.
   *
   * @param dataStoreFactory The data store to use for caching credential information.
   * @return A {@link Credential} object initialized with the current user's credentials.
   */
  private static Credential authorize(DataStoreFactory dataStoreFactory) throws Exception {
    // Load the client secrets JSON file.
    GoogleClientSecrets clientSecrets =
        GoogleClientSecrets.load(
            JSON_FACTORY,
            new InputStreamReader(
                DoubleClickBidManagerFactory.class.getResourceAsStream("/client_secrets.json"),
                UTF_8));

    if (clientSecrets.getDetails().getClientId().startsWith("Enter")
        || clientSecrets.getDetails().getClientSecret().startsWith("Enter ")) {
      System.out.println(
          "Enter Client ID and Secret from "
              + "https://console.developers.google.com/project into "
              + "dbm-cmdline-sample/src/main/resources/client_secrets.json");
      System.exit(1);
    }

    // Set up the authorization code flow.
    GoogleAuthorizationCodeFlow flow =
        new GoogleAuthorizationCodeFlow.Builder(
                HTTP_TRANSPORT, JSON_FACTORY, clientSecrets, DoubleClickBidManagerScopes.all())
            .setDataStoreFactory(dataStoreFactory)
            .build();

    // Authorize and persist credential information to the data store.
    return new AuthorizationCodeInstalledApp(flow, new LocalServerReceiver()).authorize("user");
  }

  /**
   * Adjusts HTTP timeout values used by the provided request initializer.
   *
   * @param initializer The {@link HttpRequestInitializer} used to authorize requests.
   * @return An {@link HttpRequestInitializer} with modified HTTP timeout values.
   */
  private static HttpRequestInitializer setHttpTimeout(final HttpRequestInitializer initializer) {
    return new HttpRequestInitializer() {
      @Override
      public void initialize(HttpRequest httpRequest) throws IOException {
        initializer.initialize(httpRequest);
        httpRequest.setReadTimeout(HTTP_READ_TIMEOUT_IN_MILLIS);
      }
    };
  }

  /**
   * Performs all necessary setup steps for running requests against the API.
   *
   * @return An initialized {@link DoubleClickBidManager} service object.
   */
  public static DoubleClickBidManager getInstance() throws Exception {
    FileDataStoreFactory dataStoreFactory = new FileDataStoreFactory(DATA_STORE_DIR);

    Credential credential = authorize(dataStoreFactory);

    // Create DoubleClickBidManager service object.
    DoubleClickBidManager bidmanager =
        new DoubleClickBidManager.Builder(HTTP_TRANSPORT, JSON_FACTORY, setHttpTimeout(credential))
            .setApplicationName(APPLICATION_NAME + "_JavaSamples")
            .build();

    return bidmanager;
  }
}
