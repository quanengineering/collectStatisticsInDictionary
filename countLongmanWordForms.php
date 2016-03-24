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
        $wordsUrl[] = $element->getAttribute('data-alphakey');
    }

    if ($alphaKey == 'insipidness,_insipidity_d1') { //this link is NOT FOUND
        $alphaKey = 'insipidly_d1'; // replace with previous link
    }

    if ($alphaKey == 'leninist,_leninite_d1') { //this link is NOT FOUND
        $alphaKey = 'leninism'; // replace with previous link
    }

    $url = 'http://global.longmandictionaries.com/dict_search/get_entry_chunk_for_alpha_key/ldoce6/' . $alphaKey . '/1/';

} while ($alphaKey != 'zzz');

$totalEntriesHaveNounForms = 0;
$totalNounEntries = 0;
$totalEntriesHaveVerbForms = 0;
$totalVerbEntries = 0;
$totalEntriesHaveAdjectiveForms = 0;
$totalAdjectiveEntries = 0;
foreach ($wordsUrl as $alphaKey) {

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

        if (count($elements = $wordDocument->find('.entryhead')[0]->find('.pos')) != 0) { //check if word has part of speech
            $pos = trim($wordDocument->find('.entryhead')[0]->find('.pos')[0]->text()); //find part of speech of word
            if ($pos == 'noun') {
                $totalNounEntries++;
                if (count($elements = $wordDocument->find('.entryhead')[0]->find('.inflections')) != 0) { //check if word has noun forms

                    $totalEntriesHaveNounForms++;

                    echo 'Current entry has noun forms: ' . $entryName . PHP_EOL;
                }
            } elseif ($pos == 'adjective') {
                $totalAdjectiveEntries++;
                if (count($elements = $wordDocument->find('.entryhead')[0]->find('.inflections')) != 0) { //check if word has adjective forms

                    $totalEntriesHaveAdjectiveForms++;

                    echo 'Current entry has adjective forms: ' . $entryName . PHP_EOL;
                }
            } elseif ($pos == 'verb') {
                $totalVerbEntries++;
                if (count($elements = $wordDocument->find('.entryhead')[0]->find('.inflections')) != 0) { //check if word has verb forms

                    $totalEntriesHaveVerbForms++;

                    echo 'Current entry has verb forms: ' . $entryName . PHP_EOL;
                }
            }

        }
    }

}

echo 'Total entries have noun forms/Total noun entries: ' . $totalEntriesHaveNounForms . '/' . $totalNounEntries . PHP_EOL;
echo 'Total entries have  adjective forms/Total adjective entries: ' . $totalEntriesHaveAdjectiveForms . '/' . $totalAdjectiveEntries . PHP_EOL;
echo 'Total entries have verb forms/Total verb entries: ' . $totalEntriesHaveVerbForms . '/' . $totalVerbEntries . PHP_EOL;
echo 'Statistics from Longman Dictionary of Contemporary English' . PHP_EOL;

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
