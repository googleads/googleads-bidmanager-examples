# DoubleClick Bid Manager REST API Python Samples

These samples demonstrate basic usage of the
[DoubleClick Bid Manager REST API](https://developers.google.com/bid-manager/).

## Prerequisites

Please make sure that you're running Python 2.7+ or 3.4+ and have pip installed. Use the following command from the samples directory to install all dependencies:

```Batchfile
$ pip install -r dependencies.txt
```

## Setup Authentication

This API uses OAuth 2.0. Learn more about Google APIs and OAuth 2.0 here:
https://developers.google.com/accounts/docs/OAuth2

Or, if you'd like to dive right in, follow these steps.
 - Visit https://console.developers.google.com to register your application.
 - From the API Manager -> Google APIs screen, activate access to "DoubleClick Bid Manager API".
 - Click on "Credentials" in the left navigation menu
 - Click the button labeled "Create credentials" and select "OAuth Client ID"
 - Select "Other" as the "Application type", then "Create"
 - From the Credentials page, click "Download JSON" next to the client ID you just created and save the file as `client_secrets.json` in the samples project directory.

## Running the Examples

I'm assuming you've checked out the code and are reading this from a local
directory. If not check out the code to a local directory.

1. Start up a sample, e.g.

        $ python get_latest_report.py

2. Complete the authorization steps on your browser.

3. Examine your shell output, be inspired and start hacking an amazing new app!
