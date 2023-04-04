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
 * Helper functions for HTML generation.
 */

/**
 * Opens the HTML.
 * @param string $title the title of the page
 */
function printHtmlHeader(string $title)
{
    $htmlTitle = filter_var($title, FILTER_SANITIZE_SPECIAL_CHARS);
    print '<!DOCTYPE html>';
    print '<html>';
    print '<head><title>' . $htmlTitle . '</title></head>';
    print '<link rel="stylesheet" href="style.css" type="text/css" />';
    print '<body>';
}

/**
 * Closes the HTML.
 */
function printHtmlFooter()
{
    print '</body>';
    print '</html>';
}

/**
 * Closes the HTML for samples.
 */
function printSampleHtmlFooter()
{
    print '<a href="index.php">Go back to samples list</a>';
    printHtmlFooter();
}

/**
 * Prints the index with links to the supported examples.
 * @param array $actions supported actions
 * @param array $unsupportedActions unsupportedActions
 */
function printExamplesIndex(array $actions, array $unsupportedActions)
{
    print '<h2>Select a sample from the list</h2>';
    print '<ul class="nav">';
    foreach ($actions as $action) {
        require_once 'examples/' . $action . '.php';
        $class = ucfirst($action);
        printf(
            '<li><a class="highlight" href="?action=%s">%s</a></li>',
            $action,
            $class::getName()
        );
    }
    foreach ($unsupportedActions as $unsupported) {
            require_once 'examples/' . $unsupported . '.php';
            $class = ucfirst($unsupported);
            printf(
                '<li><a class="highlight">%s</a></li>',
                $class::getName()
            );
        }
    print '</ul>';
}