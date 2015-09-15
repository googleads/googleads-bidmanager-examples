#DoubleClick Bid Manager REST API PHP Samples
These samples demonstrate basic usage of the DoubleClick Bid Manager REST API.

The complete documentation for the DoubleClick Bid Manager API is
available from <https://developers.google.com/bid-manager/>.

##Prerequisites
- [`PHP 5.2.1+`](http://php.net/)
- [`PHP Client library for Google APIs`](https://developers.google.com/api-client-library/php/start/installation)

##Announcements and updates

For API and client library updates and news, please follow our Google Ads 
Developers blog: <http://googleadsdeveloper.blogspot.com/>.

## Running the examples

###Download the repository contents

To download the contents of the repository, you can use the command

```
git clone https://github.com/googleads/googleads-dbm-examples
```

or browse to <https://github.com/googleads/googleads-dbm-examples> and
 download a zip.

###Authorization Setup
The API uses OAuth2 for security

 * Launch the [Google Developers Console](https://console.developers.google.com)
 * select a project
 * click **APIs & auth**
 * click the **Credentials** tab
 * if you need to create a  ```Web client``` under ```OAuth 2.0 client IDs```
  * click **Create a new client ID**
  * select **Web Application**
  * ensure the **Authorized redirect URIs** is set to the path containing ```index.php```
    e.g. http://your/path/
 * if you have an existing ```Web client```
  * ensure the **Authorized redirect URIs** is set to the path containing ```index.php```
    e.g. http://your/path/
 * Click **Download JSON** for a ```Web Client```
 * place the file, renamed as client_secrets.json file in the same directory as ```index.php```

## Running the Examples

Change the include path in ```index.php``` to your client library installation (Replace ```<PATH_TO_PHP_CLIENT>```)

Open the sample (http://your/path/index.php) in your browser

Click ```Connect Me``` to start an authentication flow, redirect back to your 
server, and then run the samples against your your Bid Manager account.