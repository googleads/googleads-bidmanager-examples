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

/**
 * Include the DoubleClick Bid Manager service class and the HTML generation
 * functions.
 */
require_once 'htmlHelper.php';

/**
 * Base class for all examples, contains helper methods to support examples
 * input and rendering results.
 */
abstract class BaseExample {
  protected $service;

  /**
   * Inject the dependency.
   * @param Google_Service_DoubleClickBidManager $service an authenticated
   *     instance of Google_Service_DoubleClickBidManager.
   */
  public function __construct(Google_Service_DoubleClickBidManager $service) {
    $this->service = $service;
  }

  /**
   * Contains the logic of the example.
   */
  abstract protected function run();

  /**
   * Executes the example, checks if the example requires parameters and
   * request them before invoking run.
   */
  public function execute() {
    if (count($this->getInputParameters()) > 0) {
      if ($this->isSubmitComplete()) {
        $this->formValues = $this->getFormValues();
        $this->run();
      } else {
        $this->renderInputForm();
      }
    } else {
      $this->run();
    }
  }

  /**
   * Gives a display name of the example.
   * To be implemented in the specific example class.
   * @return string that is displayed on the main page as a link
   */
  abstract public function getName();

  /**
   * Returns the list of input parameters of the example.
   * To be overriden by examples that require parameters.
   * @return array of arrays of name, display and required
   */
  protected function getInputParameters() {
    return array();
  }

  /**
   * Renders an input form to capture the example parameters.
   */
  protected function renderInputForm() {
    $parameters = $this->getInputParameters();
    if (count($parameters) > 0) {
      printf('<h2>Enter %s parameters</h2>', $this->getName());
      print '<form method="POST"><fieldset>';
      foreach ($parameters as $parameter) {
        $name = $parameter['name'];
        $display = $parameter['display'];
        $currentValue = isset($_POST[$name]) ? $_POST[$name] : '';
        printf('%s: <input name="%s" value="%s">', $display, $name,
            $currentValue);
        if ($parameter['required']) {
          print '*';
        }
        print '</br>';
      }
      print '</fieldset>*required<br/>';
      print '<input type="submit" name="submit" value="Submit"/>';
      print '</form>';
    }
  }

  /**
   * Checks if the form has been submitted and all required parameters are
   * set.
   */
  protected function isSubmitComplete() {
    if (isset($_POST['submit'])) {
      foreach ($this->getInputParameters() as $parameter) {
        if ($parameter['required'] &&
            empty($_POST[$parameter['name']])) {
          return false;
        }
      }
      return true;
    }
    return false;
  }

  /**
   * Retrieves the submitted form values.
   */
  protected function getFormValues() {
    $input = array();
    foreach ($this->getInputParameters() as $parameter) {
      if (isset($_POST[$parameter['name']])) {
        $input[$parameter['name']] = $_POST[$parameter['name']];
      }
    }
    return $input;
  }

  /**
   * Prints out the given result object.
   * @param array $result result from a DBM API call.
   */
  protected function printResult($result) {
    printf('<pre>');
    print_r($result);
    printf('</pre>');
  }
}

