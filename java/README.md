# DoubleClick Bid Manager API Java Samples
These samples demonstrate basic usage of the DoubleClick Bid Manager REST API.

The complete documentation for the DoubleClick Bid Manager API is
available from <https://developers.google.com/bid-manager/>.

## Prerequisites
- [`Java 7+`](http://java.com)
- [`Maven`](http://maven.apache.org)

## Announcements and updates

For API and client library updates and news, please follow our Google Ads
Developers blog: <http://googleadsdeveloper.blogspot.com/>.

## Running the examples

### Download the repository contents

To download the contents of the repository, you can use the command

```
git clone https://github.com/googleads/googleads-bidmanager-examples
```

or browse to <https://github.com/googleads/googleads-bidmanager-examples> and
 download a zip.

### Authorization Setup
The API uses OAuth2 for security.

 * Launch the Google Developers Console <https://console.developers.google.com>
 * select a project
 * click **APIs & auth**
 * click the **Credentials** tab
 * if you need to create a ```Client ID for native application```
  * click **Create a new client ID**
  * select **Installed Application**
 * Click **Download JSON** for a ```Client ID for native application```
 * copy this file to src/main/resources/client_secrets.json.

## Setup the environment

### Set the Application name
Edit `DoubleClickBidManagerFactory.java` and change **APPLICATION_NAME**

### Via the command line

Execute the following command:

```
$ mvn compile
```

### Via Eclipse

1. Setup Eclipse preferences:
    1. Window > Preferences .. (or on Mac, Eclipse > Preferences)
    2. Select Maven
    3. Select "Download Artifact Sources"
    4. Select "Download Artifact JavaDoc"
2. Import the sample project
    1. "File > Import..."
    2. Select "Maven > Existing Maven Project" and click "Next"
    3. Click "Browse" next to "Select root directory", find the sample directory
    and click "Next"
    4. Click "Finish"

## Running the Examples

Once you've checked out the code:

1. Run GetLatestReport.java
    1. Via the command line, execute the following command:

        ```
        $ mvn exec:java -Dexec.mainClass="com.google.bidmanager.api.samples.GetLatestReport"
        ```
    2. Via eclipse, right-click on the project and select Run As > Java
    Application

