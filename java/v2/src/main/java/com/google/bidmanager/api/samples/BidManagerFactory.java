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

package com.google.bidmanager.api.samples;

import static java.nio.charset.StandardCharsets.UTF_8;

import com.google.api.client.auth.oauth2.Credential;
import com.google.api.client.extensions.java6.auth.oauth2.AuthorizationCodeInstalledApp;
import com.google.api.client.extensions.jetty.auth.oauth2.LocalServerReceiver;
import com.google.api.client.googleapis.auth.oauth2.GoogleAuthorizationCodeFlow;
import com.google.api.client.googleapis.auth.oauth2.GoogleClientSecrets;
import com.google.api.client.googleapis.auth.oauth2.GoogleCredential;
import com.google.api.client.googleapis.util.Utils;
import com.google.api.client.http.HttpRequest;
import com.google.api.client.http.HttpRequestInitializer;
import com.google.api.client.http.HttpTransport;
import com.google.api.client.json.JsonFactory;
import com.google.api.client.util.Strings;
import com.google.api.client.util.store.DataStoreFactory;
import com.google.api.client.util.store.FileDataStoreFactory;
import com.google.api.services.doubleclickbidmanager.DoubleClickBidManager;
import com.google.api.services.doubleclickbidmanager.DoubleClickBidManagerScopes;
import java.io.Console;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.Reader;
import java.nio.file.Files;
import java.nio.file.Paths;
import java.util.ArrayList;
import java.util.List;

/**
 * Factory for BidManager clients that handles OAuth and service creation for all Bid Manager API
 * samples.
 */
public final class BidManagerFactory {
  /** Directory to store user credentials. */
  private static final java.io.File DATA_STORE_DIR =
      new java.io.File(System.getProperty("user.home"), ".store/dbm_sample");

  /** Default location for client secrets file. */
  private static final String CLIENT_SECRETS_FILE =
      System.getProperty("user.home") + "/client_secrets.json";

  /** Default location for service account key file. */
  private static final String SERVICE_ACCOUNT_KEY_FILE =
      System.getProperty("user.home") + "/service_account_key.json";

  /**
   * An optional Google account email to impersonate. Only applicable to service accounts which have
   * enabled domain-wide delegation and wish to make API requests on behalf of an account within
   * their domain. Setting this field will not allow you to impersonate a user from a domain you
   * don't own (e.g., gmail.com).
   */
  private static final String EMAIL_TO_IMPERSONATE = "";

  /**
   * Be sure to specify the name of your application. If the application name is {@code null} or
   * blank, the application will log a warning. Suggested format is "MyCompany-ProductName/1.0".
   */
  private static final String APPLICATION_NAME = "";

  private static final HttpTransport HTTP_TRANSPORT = Utils.getDefaultTransport();
  private static final JsonFactory JSON_FACTORY = Utils.getDefaultJsonFactory();

  /**
   * Authorizes the installed application to access user's protected data.
   *
   * @param clientSecretsFile The path to the file containing client secrets.
   * @param additionalScopes Scopes to authenticate in addition to default scope.
   * @return A {@link Credential} object initialized with the current user's credentials.
   */
  private static Credential authorize(String clientSecretsFile, List<String> additionalScopes)
      throws Exception {
    // Load application default credentials if they're available.
    Credential credential = loadApplicationDefaultCredentials(additionalScopes);

    // Otherwise, load credentials from the set client secrets file or a provided client secrets
    // file.
    if (credential == null) {
      String verifiedClientSecretsFile = null;
      // Assign given path to client secrets file, if provided.
      // Otherwise, assign default path.
      String tmpClientSecretsFilePath = clientSecretsFile;
      if (tmpClientSecretsFilePath == null) {
        tmpClientSecretsFilePath = CLIENT_SECRETS_FILE;
      }

      // Verify that given file exists. If not, ask user for path to existing file.
      do {
        if ((new File(tmpClientSecretsFilePath)).isFile()) {
          verifiedClientSecretsFile = tmpClientSecretsFilePath;
        } else {
          Console console = System.console();
          console.printf("%nA file was not found at %s.%n", tmpClientSecretsFilePath);
          console.printf("Please provide the path to a client secrets JSON file.%n");
          console.printf("Enter path to client secrets file:%n");
          tmpClientSecretsFilePath = console.readLine();
        }
      } while (verifiedClientSecretsFile == null);

      credential =
          loadUserCredentials(
              verifiedClientSecretsFile,
              new FileDataStoreFactory(DATA_STORE_DIR),
              additionalScopes);
    }

    return credential;
  }

  /**
   * Attempts to load application default credentials.
   *
   * @param additionalScopes Scopes to authenticate in addition to default scope.
   * @return A {@link Credential} object initialized with application default credentials, or {@code
   *     null} if none were found.
   */
  private static Credential loadApplicationDefaultCredentials(List<String> additionalScopes) {
    try {
      GoogleCredential credential = GoogleCredential.getApplicationDefault();
      return credential.createScoped(buildScopesList(additionalScopes));
    } catch (IOException ignored) {
      // No application default credentials, continue to try other options.
    }

    return null;
  }

  /**
   * Attempts to load user credentials from the provided client secrets file and persists data to
   * the provided data store.
   *
   * @param clientSecretsFile The path to the file containing client secrets.
   * @param dataStoreFactory The data store to use for caching credential information.
   * @param additionalScopes Scopes to authenticate in addition to default scope.
   * @return A {@link Credential} object initialized with user account credentials.
   */
  private static Credential loadUserCredentials(
      String clientSecretsFile, DataStoreFactory dataStoreFactory, List<String> additionalScopes)
      throws Exception {

    // Load client secrets JSON file.
    GoogleClientSecrets clientSecrets;
    try (Reader reader = Files.newBufferedReader(Paths.get(clientSecretsFile), UTF_8)) {
      clientSecrets = GoogleClientSecrets.load(JSON_FACTORY, reader);
    }

    // Set up the authorization code flow.
    GoogleAuthorizationCodeFlow flow =
        new GoogleAuthorizationCodeFlow.Builder(
                HTTP_TRANSPORT, JSON_FACTORY, clientSecrets, buildScopesList(additionalScopes))
            .setDataStoreFactory(dataStoreFactory)
            .build();

    // Authorize and persist credential information to the data store.
    return new AuthorizationCodeInstalledApp(flow, new LocalServerReceiver()).authorize("user");
  }

  /**
   * Authorizes the installed application to access the API using a service account.
   *
   * @param serviceAccountKeyFile The path to the file containing the service account key.
   * @param additionalScopes Scopes to authenticate in addition to default scope.
   * @return A {@link Credential} object initialized with service account credentials.
   */
  private static Credential authorizeWithServiceAccount(
      String serviceAccountKeyFile, List<String> additionalScopes) throws Exception {

    // Use the default or provided service account key file to generate credentails
    String verifiedServiceAccountKeyFile = null;
    // Assign given path to client secrets file, if provided.
    // Otherwise, assign default path.
    String tmpServiceAccountKeyPath = serviceAccountKeyFile;
    if (tmpServiceAccountKeyPath == null) {
      tmpServiceAccountKeyPath = SERVICE_ACCOUNT_KEY_FILE;
    }

    // Verify that given file exists. If not, ask user for path to existing file.
    do {
      if ((new File(tmpServiceAccountKeyPath)).isFile()) {
        verifiedServiceAccountKeyFile = tmpServiceAccountKeyPath;
      } else {
        Console console = System.console();
        console.printf("%nA file was not found at %s.%n", tmpServiceAccountKeyPath);
        console.printf("Please provide the path to a service account key JSON file.%n");
        console.printf("Enter path to service account key file:%n");
        tmpServiceAccountKeyPath = console.readLine();
      }
    } while (verifiedServiceAccountKeyFile == null);

    // Generate a credential object from the specified JSON file.
    GoogleCredential credential =
        GoogleCredential.fromStream(new FileInputStream(verifiedServiceAccountKeyFile));

    // Update the credential object with appropriate scopes.
    if (Strings.isNullOrEmpty(EMAIL_TO_IMPERSONATE)) {
      credential = credential.createScoped(buildScopesList(additionalScopes));
    } else {
      credential =
          new GoogleCredential.Builder()
              .setTransport(credential.getTransport())
              .setJsonFactory(credential.getJsonFactory())
              .setServiceAccountId(credential.getServiceAccountId())
              .setServiceAccountPrivateKey(credential.getServiceAccountPrivateKey())
              .setServiceAccountScopes(buildScopesList(additionalScopes))
              // Set the email of the user you are impersonating (this can be yourself).
              .setServiceAccountUser(EMAIL_TO_IMPERSONATE)
              .build();
    }

    return credential;
  }

  /**
   * Adjusts HTTP timeout values used by the provided request initializer.
   *
   * @param requestInitializer The {@link HttpRequestInitializer} used to authorize requests.
   * @return An {@link HttpRequestInitializer} with modified HTTP timeout values.
   */
  private static HttpRequestInitializer setHttpTimeout(
      final HttpRequestInitializer requestInitializer) {
    return new HttpRequestInitializer() {
      @Override
      public void initialize(HttpRequest httpRequest) throws IOException {
        requestInitializer.initialize(httpRequest);
      }
    };
  }

  /**
   * Builds list of OAuth scopes to use in authentication.
   *
   * @param additionalScopes The scopes provided to authenticate in addition to the default.
   * @return An List of OAuth scopes strings.
   */
  private static List<String> buildScopesList(List<String> additionalScopes) {
    List<String> scopes = new ArrayList<String>();
    scopes.add(DoubleClickBidManagerScopes.DOUBLECLICKBIDMANAGER);
    if (additionalScopes != null && !additionalScopes.isEmpty()) {
      scopes.addAll(additionalScopes);
    }
    return scopes;
  }

  /**
   * Performs all necessary setup steps for running requests against the API.
   *
   * @param clientSecretsFile The path to the file containing client secrets.
   * @param useServiceAccount Whether or not to authenticate with a service account.
   * @param serviceAccountKeyFile The path to the file containing the service account key.
   * @param additionalScopes Scopes to authenticate in addition to default scope.
   * @return An initialized {@link DoubleClickBidManager} service object.
   */
  public static DoubleClickBidManager getInstance(
      String clientSecretsFile,
      boolean useServiceAccount,
      String serviceAccountKeyFile,
      List<String> additionalScopes)
      throws Exception {

    // Authorize with either a user or service account.
    Credential credential;
    if (!useServiceAccount) {
      credential = authorize(clientSecretsFile, additionalScopes);
    } else {
      credential = authorizeWithServiceAccount(serviceAccountKeyFile, additionalScopes);
    }

    String modifiedApplicationName = APPLICATION_NAME;
    if (APPLICATION_NAME != null && !APPLICATION_NAME.trim().isEmpty()) {
      modifiedApplicationName += "_JavaSamples";
    }

    // Create DoubleClickBidManager service object.
    DoubleClickBidManager bidManager =
        new DoubleClickBidManager.Builder(HTTP_TRANSPORT, JSON_FACTORY, setHttpTimeout(credential))
            .setApplicationName(modifiedApplicationName)
            .build();

    return bidManager;
  }
}
