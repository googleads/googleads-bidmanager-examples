# DoubleClick Bid Manager API PHP Samples

This is a collection of samples written in PHP which provide a starting place
for your experimentation into the DoubleClick Bid Manager API.

## Prerequisites

  - PHP 5.4+
  - JSON PHP extension
  - Composer

From the example directory, run `composer install` to install all dependencies.

## Setup Authentication

This API uses OAuth 2.0. Learn more about Google APIs and OAuth 2.0 here:
https://developers.google.com/accounts/docs/OAuth2

Or, if you'd like to dive right in, follow these steps.
 - Visit https://console.developers.google.com to register your application.
 - From the API Manager -> Overview screen, activate access to "DoubleClick Bid Manager API".
 - Click on "Credentials" in the left navigation menu
 - Click the button labeled "Create credentials" ->  "OAuth2 client ID"
 - Select "Web Application" as the "Application type"
 - Configure javascript origins and redirect URIs
   - Authorized Javascript Origins: http://localhost
   - Authorized Redirect URIs: http://localhost/path/to/index.php
 - Click "Create client ID"
 - Click "Download JSON" and save the file as `client_secrets.json` in your
   examples directory

> #### Security alert!

> Always ensure that your client_secrets.json file is not publicly accessible.
> This file contains credential information which could allow unauthorized access
> to your DBM data.

## Running the Examples

Open the sample (http://your/path/index.php) in your browser

Click ```Connect Me``` to start an authentication flow, redirect back to your 
server, and then run the samples against your your Bid Manager account.
