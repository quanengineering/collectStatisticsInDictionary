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
$url = 'http://global.longmandictionaries.com/dict_search/get_initial_entries/lcdt/';

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

    $url = 'http://global.longmandictionaries.com/dict_search/get_entry_chunk_for_alpha_key/lcdt/' . $alphaKey . '/1/';

} while ($alphaKey != 'zone');

$entriesId = array();
$totalEntriesHaveCollocations = 0;
$totalEntriesHaveSynonyms = 0;
$totalEntries = 0;
foreach ($wordsUrl as $alphaKey => $key) {

    if (!in_array($key, $entriesId)) { //check if entry existed = check if entry is crawled before
        $entriesId[] = $key;

        # new data
        $data = array(
            'alpha_key' => $alphaKey,
            'name' => ''
        );
        # Create a connection
        $url = 'http://global.longmandictionaries.com/dict_search/entry_for_alpha_key/lcdt/';
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

            if (count($elements = $wordDocument->find('.collobox')) != 0) { //check if word has collocation
                $totalEntriesHaveCollocations++;

                echo 'Current entry has collocations: ' . $entryName . PHP_EOL;
            }

            if (count($elements = $wordDocument->find('.thesbox')) != 0) { //check if word has synonym
                $totalEntriesHaveSynonyms++;

                echo 'Current entry has thesauruses: ' . $entryName . PHP_EOL;
            }
        }
    }
}

echo 'Total entries have collocations/Total entries: ' . $totalEntriesHaveCollocations . '/' . $totalEntries . PHP_EOL;
echo 'Total entries have thesauruses/Total entries: ' . $totalEntriesHaveSynonyms . '/' . $totalEntries . PHP_EOL;
echo 'Statistics from Longman Collocations Dictionary and Thesaurus' . PHP_EOL;

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
