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

/*
 * Authorizes with the ServiceAccount Authorization Flow and presents a menu of
 * DoubleClickBid Manager REST API samples to run.
 *
 */

/*
 * Provide path to src directory of google-api-php-client.
 *
 * For example:"google-api-php-client/src/"
 */
set_include_path('<PATH_TO_PHP_CLIENT>' . PATH_SEPARATOR . get_include_path());
require_once 'Google/autoload.php';
require_once "htmlHelper.php";

session_start();

// Configure token storage on disk.
// If you want to store refresh tokens in a local disk file, set this to true.
define('STORE_ON_DISK', false, true);
define('TOKEN_FILENAME', 'tokens.dat', true);

// Set up authentication.
$client = new Google_Client();
$client->setApplicationName('DBM API PHP Samples');
$client->addScope('https://www.googleapis.com/auth/doubleclickbidmanager');
$client->setAccessType('offline');

// Be sure to replace the contents of client_secrets.json with your developer
// credentials.
$client->setAuthConfigFile('client_secrets.json');

// Create service.
$service = new Google_Service_DoubleClickBidManager($client);

// If we're logging out we just need to clear our local access token
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
}

// If we have a code back from the OAuth 2.0 flow, we need to exchange that
// with the authenticate() function. We store the resultant access token
// bundle in the session, and redirect to this page.
if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  // Note that "getAccessToken" actually retrieves both the access and refresh
  // tokens, assuming both are available.
  $_SESSION['access_token'] = $client->getAccessToken();
  if (STORE_ON_DISK) {
    file_put_contents(TOKEN_FILENAME, $_SESSION['access_token']);
  }
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
  exit;
}

// If we have an access token, we can make requests, else we generate an
// authentication URL.
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
} else if (STORE_ON_DISK && file_exists(TOKEN_FILENAME) &&
    filesize(TOKEN_FILENAME) > 0) {
  // Note that "setAccessToken" actually sets both the access and refresh token,
  // assuming both were saved.
  $client->setAccessToken(file_get_contents(TOKEN_FILENAME));
  $_SESSION['access_token'] = $client->getAccessToken();
} else {
  // If we're doing disk storage, generate a URL that forces user approval.
  // This is the only way to guarantee we get back a refresh token.
  if (STORE_ON_DISK) {
    $client->setApprovalPrompt('force');
  }
  $authUrl = $client->createAuthUrl();
}

print '<h1>Double Click Bid Manager REST API sample</h1>';

echo '<div><div class="request">';
if (isset($authUrl)) {
  echo '<a class="login" href="' . $authUrl . '">Connect Me!</a>';
} else {
  echo '<a class="logout" href="?logout">Logout</a>';
};
echo '</div>';



if ($client->getAccessToken()) {
  // Build the list of supported actions.
  $actions = getSupportedActions();

  // If the action is set dispatch the action if supported
  if (isset($_GET["action"])) {
    $action = $_GET["action"];
    if (!in_array($action, $actions)) {
      die('Unsupported action:' . $action . "\n");
    }
    // Render the required action.
    require_once 'examples/' . $action . '.php';
    $class = ucfirst($action);
    $example = new $class($service);
    printHtmlHeader($example->getName());
    try {
      $example->execute();
    } catch (apiException $ex) {
      printf('An error as occurred while calling the example:<br/>');
      printf($ex->getMessage());
    }
    printSampleHtmlFooter();
  } else {
    // Show the list of links to supported actions.
    printHtmlHeader('Double Click Bid Manager API PHP usage examples.');
    printExamplesIndex($actions);
    printHtmlFooter();
  }

  // The access token may have been updated.
  $_SESSION['service_token'] = $client->getAccessToken();
}

/**
 * Builds an array containing the supported actions.
 */
function getSupportedActions() {
  return array('GetLatestReport', 'DownloadLineItems', 'UploadLineItems');
}
