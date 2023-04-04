# Bid Manager REST API v2 PHP Samples

This is a collection of samples written in PHP which provide a starting place
for your experimentation into [Bid Manager API
v2](https://developers.google.com/bid-manager/reference/rest).

## Technical Requirements

  - PHP 7.2+
  - [Composer](https://getcomposer.org/)

From the example directory, run `composer install` to install all dependencies.

## Setup Authentication

This API uses OAuth 2.0. Learn more about Google APIs and OAuth 2.0 here:
https://developers.google.com/accounts/docs/OAuth2

If you've already set up [Application Default
Credentials](https://cloud.google.com/docs/authentication/production#providing_credentials_to_your_application)
at the environmental variable `GOOGLE_APPLICATION_CREDENTIALS`, the sample
suite will automatically use those credentials for authentication.*

Follow these steps to enable the API for your Google Cloud Platform project and
generate the necessary credentials:
1. Visit [Google Developers Console](https://console.developers.google.com) to
select or create your project.
2. From the API Manager &rarr; Google APIs screen, activate access to
"DoubleClick Bid Manager API".
3. Click on "Credentials" in the left navigation menu
4. Click the button labeled "Create credentials" and select "OAuth Client ID"
5. Select "Web Application" as the "Application type", set the following
values, then "Create":
   * **Authorized Javascript Origins:** `http://localhost:8000`
   * **Authorized Redirect URIs:** `http://localhost:8000/index.php`
6. From the Credentials page, click the "Download OAuth Client" icon under
"Actions" next to the client ID you just created and click "Download JSON".
7. Save the downloaded file as `client_secrets.json` in your home
directory.

### Authenticate using a service account

These samples support authentication using a [service account
key](https://cloud.google.com/iam/docs/service-account-overview). To run
samples using a service account, follow these steps after completing steps 1
and 2 from the section above:
1. Click on "Credentials" in the left navigation menu.
2. Click the button labeled "Create credentials" and select "Service Account".
3. Provide a "Service account name", "Service account ID", and "Service account
description", then "Create and Continue".
4. From the Credentials page, click the new service account email.
5. Navigate to the "Keys" tab, click "Add Key", and create a new JSON key,
which will download automatically.
6. Save the downloaded file as `service_account_key.json` in your home
directory.
7. When opening the sample webpage, include the query parameter
`service_account=true` to authenticate using the service account key.

If you are using a service account for authentication, [create and download a
JSON service account
key](https://cloud.google.com/iam/docs/creating-managing-service-account-keys#creating),
save the file as `service_account_key.json` in your examples, and include the
query parameter `service_account=true` when opening the sample.

> #### Security alert!

> Always ensure that your `client_secrets.json` and `service_account_key.json`
> files are not publicly accessible. These files have credential information
> which could allow unauthorized access to your Display & Video 360 data.

## Running the Examples

Once you've checked out the code and installed dependencies using Composer, you
can run the examples locally:
1. Via the command line, execute the following command in this directory:
```
php -S localhost:8000 -t ./
```
2. Open the sample (http://localhost:8000/index.php) in your browser.
3. Click ```Connect Me``` to start an authentication flow, redirect back to
your server, and then run the samples against your Display & Video 360 account.

**Note**: To use a service account for authentication, open the sample using
the following url: `http://localhost:8000/index.php?service_account=true`