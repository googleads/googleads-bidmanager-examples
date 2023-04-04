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

/*
 * Authorizes with the ServiceAccount Authorization Flow and presents a menu of
 * Bid Manager REST API samples to run.
 */
require_once __DIR__ . '/vendor/autoload.php';
require_once 'htmlHelper.php';

session_start();

// Configure token storage on disk.
// If you want to store refresh tokens in a local disk file, set this to true.
$STORE_ON_DISK = false;
$TOKEN_FILENAME = 'tokens.dat';

// Configure authentication file locations.
$CLIENT_SECRETS_FILE = getenv('HOME') . '/client_secrets.json';
$SERVICE_ACCOUNT_KEY_FILE = getenv('HOME') . '/service_account_key.json';

$DEFAULT_OAUTH_SCOPES =
    [Google_Service_DoubleClickBidManager::DOUBLECLICKBIDMANAGER];

// Set up authentication.
$client = new Google_Client();
$client->setApplicationName('Bid Manager API PHP Samples');
$client->setScopes($DEFAULT_OAUTH_SCOPES);
$client->setAccessType('offline');

// If we're logging out we just need to clear our token and disable use of the
// service account.
if (isset($_REQUEST['logout'])) {
    unset($_SESSION['access_token']);
    unset($_SESSION['service_account']);
}


// Set the session to use a service account if the query param is set to true.
if (isset($_GET['service_account']) && $_GET['service_account']) {
    $_SESSION['service_account'] = true;
} else if (!isset($_SESSION['service_account'])) {
    $_SESSION['service_account'] = false;
}

if (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
    $client->useApplicationDefaultCredentials();
} else {
    if ($_SESSION['service_account']) {
        // Use service account for authentication and use User scope if set for
        // session.
        $client->setAuthConfig($SERVICE_ACCOUNT_KEY_FILE);
    } else {
        // Be sure to save your developer credentials as client_secrets.json
        // in your home directory.
        $client->setAuthConfig($CLIENT_SECRETS_FILE);
    }
}

// Create service.
$service = new Google_Service_DoubleClickBidManager($client);

// If we have a code back from the OAuth 2.0 flow, we need to exchange that
// with the authenticate() function. We store the resultant access token
// bundle in the session, and redirect to this page.
if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);

    // Note that "getAccessToken" actually retrieves both the access and
    // refresh tokens, assuming both are available.
    $_SESSION['access_token'] = $client->getAccessToken();
    if ($STORE_ON_DISK) {
        file_put_contents(
            $TOKEN_FILENAME,
            json_encode($_SESSION['access_token'])
        );
    }
    $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
    exit;
}

if (!$_SESSION['service_account']) {
    // If we have an access token, we can make requests, else we generate an
    // authentication URL.
    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
        $client->setAccessToken($_SESSION['access_token']);
    } elseif (
        $STORE_ON_DISK
        && file_exists($TOKEN_FILENAME)
        && filesize($TOKEN_FILENAME) > 0
    ) {
        // Note that "setAccessToken" actually sets both the access and refresh
        // token, assuming both were saved.
        $client->setAccessToken(file_get_contents($TOKEN_FILENAME));
        $_SESSION['access_token'] = $client->getAccessToken();
    } else {
        // If we're doing disk storage, generate a URL that forces user
        // approval. This is the only way to guarantee we get back a refresh
        // token.
        if ($STORE_ON_DISK) {
            $client->setApprovalPrompt('force');
        }

        $authUrl = $client->createAuthUrl();
    }
}

print '<h1>Bid Manager REST API sample</h1>';

echo '<div><div class="request">';
if (isset($authUrl)) {
    echo '<a class="login" href="' . $authUrl . '">Connect Me!</a>';
} else {
    echo '<a class="logout" href="?logout">Logout</a>';
};
echo '</div>';

// If we now have an access token, render the list of sample links or, if
// already selected, the chosen sample.
if ($client->getAccessToken() || $_SESSION['service_account']){
    // Build the lists of supported and unsupported actions based on
    // the method of authentication.
    $actions = getSupportedActions();
    $unsupportedActions = getServiceAccountSupportedActions();
    // If using a service account, add service account actions to supported
    // list.
    if  ($_SESSION['service_account']) {
        $actions = array_merge($actions, $unsupportedActions);
        $unsupportedActions = array();
    }

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
        printHtmlHeader('Bid Manager API PHP usage examples.');
        printExamplesIndex($actions, $unsupportedActions);
        printHtmlFooter();
    }

    // The access token may have been updated.
    $_SESSION['service_token'] = $client->getAccessToken();
}

/**
 * Builds an array containing the basic supported actions.
 * @return array a list of the supported sample actions.
 */
function getSupportedActions(): array
{
    return array(
        'CreateAndRunQuery',
        'GetLatestReportForQuery'
    );
}

/**
 * Builds an array containing supported actions requiring a service account.
 * @return array a list of the supported sample actions requiring a service
 *     account.
 */
function getServiceAccountSupportedActions(): array
{
    return array();
}