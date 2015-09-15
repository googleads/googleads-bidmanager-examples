#!/usr/bin/python
#
# Copyright 2015 Google Inc. All Rights Reserved.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#      http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

"""Common utilities used by the DoubleClick Bid Manager REST API Samples."""

import datetime
import os
import sys

from googleapiclient.discovery import build
import httplib2
from oauth2client import client
import yaml


# Update these with the values for your client id and client secret found in
# the Google Developers Console.
_CLIENT_ID = 'INSERT_CLIENT_ID_HERE'
_CLIENT_SECRET = 'INSERT_CLIENT_SECRET'
# Default path to dbm_sample.yaml, containing authorization credentials.
_DEFAULT_AUTH_PATH = os.path.join(os.path.expanduser('~'), 'dbm_sample.yaml')
# The web address for generating OAuth 2.0 credentials at Google.
_GOOGLE_OAUTH2_ENDPOINT = 'https://accounts.google.com/o/oauth2/token'
# DoubleClick Bid Manager REST API authorization scope.
_SCOPE = 'https://www.googleapis.com/auth/doubleclickbidmanager'
_USER_AGENT = 'DBM Python Sample'
_VERSION = 'v1'  # Version of the DoubleClick Bid Manager REST API to use.


def _get_credentials(path, client_id=None, client_secret=None):
  """Retrieve the OAuth2Credentials used for making API requests.

  This will step through the authentication flow the first time it is called
  and save credentials to the dbm_sample.yaml file in your home directory. In
  subsequent runs, it will use the credentials saved to this file
  automatically unless a different path is specified.

  Args:
    path: str path to the file used for storing authentication data.
    client_id: str containing your client ID.
    client_secret: str containing your client secret.

  Returns:
    An OAuth2Credentials instance.
  """
  try:
    auth_data = _load_auth_yaml(path)
    credentials = client.OAuth2Credentials(
        None, auth_data['client_id'], auth_data['client_secret'],
        auth_data['refresh_token'], datetime.datetime(1983, 7, 14, 12),
        _GOOGLE_OAUTH2_ENDPOINT,
        _USER_AGENT)
  except IOError:
    print 'Failed to retrieve credentials, stepping through OAuth2 flow.'
    credentials = _handle_oauth2_flow(client_id, client_secret)
    _save_auth_yaml(path, credentials)
  return credentials


def _handle_oauth2_flow(client_id, client_secret):
  """Handles the OAuth2 Installed Application Flow to get credentials.

  Args:
    client_id: str containing your client ID.
    client_secret: str containing your client secret.

  Returns:
    An OAuth2Credentials object used to authorize requests.
  """
  flow = client.OAuth2WebServerFlow(
      client_id=client_id, client_secret=client_secret,
      scope=[_SCOPE], user_agent=_USER_AGENT,
      redirect_uri='urn:ietf:wg:oauth:2.0:oob')

  authorize_url = flow.step1_get_authorize_url()

  print ('Log into the Google Account you use to access your DBM account'
         'and go to the following URL: \n%s\n' % (authorize_url))
  print 'After approving the token enter the verification code (if specified).'
  code = raw_input('Code: ').strip()

  try:
    credentials = flow.step2_exchange(code)
  except client.FlowExchangeError, e:
    print 'Authentication has failed: %s' % e
    sys.exit(1)
  else:
    return credentials


def _load_auth_yaml(path):
  """Load credentials from file at the specified path.

  Args:
    path: A str path to the file containing OAuth2 credentials.

  Returns:
    A dictionary mapping of the OAuth2 credentials.
  """
  if not os.path.isabs(path):
    path = os.path.expanduser(path)
  with open(path, 'rb') as handle:
    auth_data = yaml.load(handle.read())
    print 'Loaded credentials from "%s".' % path
    return auth_data


def _save_auth_yaml(path, credentials):
  """Save credentials to the file at the specified path.

  Args:
    path: str path to the file being saved.
    credentials: OAuth2Credentials object received after authorizing.
  """
  if not os.path.isabs(path):
    path = os.path.expanduser(path)
  with open(path, 'wb') as handle:
    handle.write(yaml.dump({
        'client_id': credentials.client_id,
        'client_secret': credentials.client_secret,
        'refresh_token': credentials.refresh_token}))
  print 'Saved credentials to "%s".' % path


def get_service(path=_DEFAULT_AUTH_PATH, client_id=None, client_secret=None):
  """Utility function for retrieving the service used to call the DBM REST API.

  Args:
    path: str path to the file containing OAuth2 credentials.
    client_id: str containing your client ID.
    client_secret: str containing your client secret.

  Raises:
    ValueError: If no client id / secret are provided via command line or set
                in this file.

  Returns:
      A service for interacting with the DoubleClick Bid Manager REST API.
  """
  if path or (client_id and client_secret):
    credentials = _get_credentials(path, client_id, client_secret)
  else:
    if (_CLIENT_ID == 'INSERT_CLIENT_ID_HERE'
        or _CLIENT_SECRET == 'INSERT_CLIENT_SECRET'):
      raise ValueError('You must set the CLIENT_ID and CLIENT_SECRET to'
                       'complete the OAuth2 flow.')
    else:
      credentials = _get_credentials(_DEFAULT_AUTH_PATH, _CLIENT_ID,
                                     _CLIENT_SECRET)

  return build('doubleclickbidmanager', _VERSION,
               http=credentials.authorize(httplib2.Http()))

