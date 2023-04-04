# Bid Manager REST API v2 Python Samples

These samples demonstrate basic usage of
[Bid Manager API v2](https://developers.google.com/bid-manager/reference/rest).

## Prerequisites

Please make sure that you're running Python 3.8 and have pip installed. Use
the following command from the samples directory to install all dependencies:

```Batchfile
$ python -m pip install -r requirements.txt
```

## Setup Authentication

This API uses [OAuth
2.0](https://developers.google.com/accounts/docs/OAuth2).

Follow these steps to enable the API for your Google Cloud Platform project and
generate the necessary credentials:
1. Visit [Google Developers Console](https://console.developers.google.com) to
select or create your project.
2. From the API Manager &rarr; Google APIs screen, activate access to
"DoubleClick Bid Manager API".
3. Click on "Credentials" in the left navigation menu
4. Click the button labeled "Create credentials" and select "OAuth Client ID"
5. Select "Desktop App" as the "Application type", then "Create"
6. From the Credentials page, click the "Download OAuth Client" icon under
"Actions" next to the client ID you just created and click "Download JSON".
7. Save the downloaded file as `client_secrets.json` in your home directory.

### Authenticating with a Service Account

These samples support authentication using a [service account
key](https://cloud.google.com/iam/docs/service-account-overview).
To run samples using a service account, follow these steps after completing
steps 1 and 2 from the section above:
1. Click on "Credentials" in the left navigation menu
2. Click the button labeled "Create credentials" and select "Service Account"
3. Provide a "Service account name", "Service account ID", and "Service account
description", then "Create and Continue".
4. From the Credentials page, click the new service account email.
5. Navigate to the "Keys" tab, click "Add Key", and create a new JSON key,
which will download automatically.
6. Save the file as `service_account_key.json` in your home directory.
7. When starting a sample, include the `--use_service_account` flag to
designate that you are authenticating with a service account.

## Running the Examples

I'm assuming you've checked out the code and are reading this from a local
directory. If not check out the code to a local directory.

1. Start up a sample, e.g.

   ```
   $ python get_latest_report_for_query.py INSERT_QUERY_ID_HERE INSERT_OUTPUT_FILE_HERE
   ```
2. Complete the authorization steps on your browser.

3. Examine your shell output, be inspired and start hacking an amazing new app!
