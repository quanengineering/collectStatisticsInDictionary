<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;
use DiDom\Query;

//error handler function
function customError($errno, $errstr)
{
    echo PHP_EOL . "<b>Error:</b> [$errno] $errstr<br>";
    echo PHP_EOL . "Ending Script" . PHP_EOL;
    die();
}

//set error handler
set_error_handler("customError");

$wordsUrl = array();

# Create a connection
$url = 'http://global.longmandictionaries.com/dict_search/get_initial_entries/ldoce6/';

do {
    $ch = curl_init($url);
    # Setting options
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    # Get the response
    $response = curl_exec($ch);
    curl_close($ch);

    $document = new Document($response);
    foreach ($document->find('a') as $element) {
        $alphaKey = $element->getAttribute('data-alphakey');
        $key = $element->getAttribute('data-key');
        $wordsUrl[$alphaKey] = $key;
    }

    if ($alphaKey == 'insipidness,_insipidity_d1') { //this link is NOT FOUND
        $alphaKey = 'insipidly_d1'; // replace with previous link
    }

    if ($alphaKey == 'leninist,_leninite_d1') { //this link is NOT FOUND
        $alphaKey = 'leninism'; // replace with previous link
    }

    $url = 'http://global.longmandictionaries.com/dict_search/get_entry_chunk_for_alpha_key/ldoce6/' . $alphaKey . '/1/';

} while ($alphaKey != 'zzz');

$entriesId = array();
$totalEntries = 0;
$totalDefinitions = 0;
foreach ($wordsUrl as $alphaKey => $key) {

    if (!in_array($key, $entriesId)) { //check if entry existed = check if entry is crawled before
        $entriesId[] = $key;

        # new data
        $data = array(
            'alpha_key' => $alphaKey,
            'name' => ''
        );
        # Create a connection
        $url = 'http://global.longmandictionaries.com/dict_search/entry_for_alpha_key/ldoce6/';
        $ch = curl_init($url);
        # Form data string
        $postString = http_build_query($data, '', '&');
        # Setting options
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        # Get the response
        $wordDocument = curl_exec($ch);
        curl_close($ch);

        $wordDocument = new Document($wordDocument);

        if (count($elements = $wordDocument->find('.hwd')) != 0) { //check if word has name

            $entryName = $wordDocument->find('.hwd')[0]->text();

            $totalEntries++;

            if (count($elements = $wordDocument->find('.sense .def')) != 0) { //check if word has definitions
                $totalDefinitions += count($wordDocument->find('.sense .def'));

                echo 'Current entry has definitions: ' . $entryName . PHP_EOL;
            }
        }
    }
}

echo 'Total entries/Total definitions: ' . $totalEntries . '/' . $totalDefinitions . PHP_EOL;
echo 'Statistics from Longman Dictionary of Contemporary English' . PHP_EOL;

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
