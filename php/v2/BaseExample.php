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

/**
 * Include the Bid Manager API service class and the HTML generation
 * functions.
 */
require_once 'htmlHelper.php';

/**
 * Base class for all examples, contains helper methods to support examples
 * input and rendering results.
 */
abstract class BaseExample
{
    protected $service;

    /**
     * Inject the dependency.
     * @param Google_Service_DoubleClickBidManager $service an authenticated
     *     instance of Google_Service_DoubleClickBidManager.
     */
    public function __construct(Google_Service_DoubleClickBidManager $service)
    {
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
    public function execute()
    {
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
    abstract public static function getName();

    /**
     * Returns the list of input parameters of the example.
     * To be overriden by examples that require parameters.
     * @return array of arrays of name, display and required
     */
    protected function getInputParameters(): array
    {
        return array();
    }

    /**
     * Renders an input form to capture the example parameters.
     */
    protected function renderInputForm()
    {
        $parameters = $this->getInputParameters();
        if (count($parameters) > 0) {
            printf('<h2>Enter %s parameters</h2>', $this->getName());
            print '<form method="POST" enctype="multipart/form-data">'
                . '<fieldset>';
            foreach ($parameters as $parameter) {
                $name = $parameter['name'];
                $display = $parameter['display'];
                $currentValue = isset($_POST[$name]) ? $_POST[$name] : '';

                $required = '';
                if ($parameter['required']) {
                    $required = '*';
                }

                printf('%s%s: ', $display, $required);

                // Print the input depending on whether set values were given.
                if (array_key_exists('values', $parameter)
                    && !empty($parameter['values'])
                ) {
                    $values = $parameter['values'];

                    // Add an empty value if not included.
                    if (!in_array('', $values)) {
                        array_unshift($values, '');
                    }

                    // Build dropdown.
                    printf('<select name="%s">', $name);
                    foreach ($values as $value) {
                        printf('<option value="%s"', $value);
                        if ($value == $currentValue) {
                            print ' selected';
                        }
                        printf('>%s</option>', $value);
                    }
                    print '</select>';

                } else {
                    printf(
                        '<input name="%s" value="%s">',
                        $name,
                        $currentValue
                    );
                }

                print '</br>';
            }
            print '</fieldset>*required<br/>';
            print '<input type="submit" name="submit" value="Submit"/>';
            print '</form>';
        }
    }

    /**
    * Renders an exception thrown while making a request to the API.
    * @param Exception $e the exception to render.
    */
    protected function renderError(\Exception $e)
    {
        print '<p class="error">Error Code: ' . $e->getCode() . ' </p>';
        print '<p class="error">Exception: ' . $e->getMessage() . ' </p>';
        print '<p><a class="highlight" href="?action=' . $_GET['action']
            . '"> Go back to ' . $this->getName() . ' sample</a></p>';
    }

    /**
     * Checks if the form has been submitted and all required parameters are
     * set.
     * @return bool whether the sample has been or can be submitted.
     */
    protected function isSubmitComplete(): bool
    {
        if (isset($_POST['submit'])) {
            foreach ($this->getInputParameters() as $parameter) {
                if ($parameter['required']) {
                    if (empty($_POST[$parameter['name']])){
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Retrieves the submitted form values.
     * @return array the input form values for the sample.
     */
    protected function getFormValues(): array
    {
        $input = array();
        foreach ($this->getInputParameters() as $parameter) {
            if ($this->isFile($parameter)
                && isset($_FILES[$parameter['name']])
            ) {
                $input[$parameter['name']] = $_FILES[$parameter['name']];
            } elseif (isset($_POST[$parameter['name']])) {
                $input[$parameter['name']] = $_POST[$parameter['name']];
            }
        }
        return $input;
    }

    /**
     * Determines if an input parameter is a file.
     * @param array $parameter the parameter in question.
     * @return bool whether the given input parameter has a set file field.
     */
    protected function isFile(array $parameter): bool
    {
        return (array_key_exists('file', $parameter) && $parameter['file']);
    }
}