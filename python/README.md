DoubleClick Bid Manager REST API Python Samples
=======================================================
These samples demonstrate basic usage of the
[DoubleClick Bid Manager REST API](https://developers.google.com/bid-manager/).

Setup
=======================================================
To run these samples, you'll need to do the following:

1. Download and install the **Google API Python Client** with either
   easy_install or pip:

  * Using easy_install:

      ```
      $ easy_install --upgrade google-api-python-client
      ```

  * Using pip:

      ```
      $ pip install --upgrade google-api-python-client
      ```

2. If you haven't done so already, contact your Google Technical Account
  Manager to ensure you are configured to use the DoubleClick Bid Manager REST
  API.
3. Go to the [Google Developers Console](https://console.developers.google.com/)
  and click the `APIs & auth` and then the `Credentials` tab.
4. If you don't see any rows under `OAuth 2.0 client IDs`, you should create
  one now by clicking the `Add credentials` button and selecting
  `OAuth 2.0 client ID` from the drop-down menu. Select `Other` as your
  Application type and click the `Create` button.
5. On the `Credentials` page, click one of your clients under
  `OAuth 2.0 client IDs` and view the corresponding `Client Id` and
  `Client secret`.
6. You will only need to authorize once to run these samples. You can either
  open **util.py** and manually enter the `CLIENT_ID` and `CLIENT_SECRET`
  fields or you may provide them as command line arguments when running one
  of the samples. By default, your credentials will be saved to
  **dbm_sample.yaml** in your home directory.
9. Before attempting to run any of the samples, you should update any fields
  containing a template value.

You should now be able to start any of the samples by running them from the
command line. You will need to provide your client id and client secret the
first time you run one of the samples either by setting it in the util.py or
providing them as command line arguments to the example you're trying to run.
For example:

```
$ python download_line_items.py --client_id="$CLIENT_ID" --client_secret="$CLIENT_SECRET"
```

For a listing of available command line arguments for a given example, provide
the `--help` argument. For example:

```
$ python download_line_items.py --help
```
