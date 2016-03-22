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

$lettersUrl = 'http://www.macmillandictionary.com/browse/british/';
$letters = new Document($lettersUrl, true);

$wordsUrl = array();

foreach ($letters->find('#letters a') as $letterUrl) {

    $groupResults = new Document($letterUrl->getAttribute('href'), true);

    foreach ($groupResults->find('#groupResult a') as $groupResult) {
        $result = new Document($groupResult->getAttribute('href'), true);

        foreach ($result->find('#result a') as $wordUrl) {
            $wordsUrl[] = $wordUrl->getAttribute('href');
        }
    }
}

$totalEntriesHaveNounForms = 0;
$totalNounEntries = 0;
$totalEntriesHaveVerbForms = 0;
$totalVerbEntries = 0;
$totalEntriesHaveAdjectiveForms = 0;
$totalAdjectiveEntries = 0;
foreach ($wordsUrl as $wordUrl) {

    if ($wordUrl != 'http://www.macmillandictionary.com/dictionary/british/huntington-s-disease') { //this link is NOT FOUND

        $wordDocument = new Document($wordUrl, true);

        if (count($elements = $wordDocument->find('h1 .BASE')) != 0) { //check if word has name

            $entryName = $wordDocument->find('h1 .BASE')[0]->text();

            if (count($elements = $wordDocument->find('#headbar .PART-OF-SPEECH')) != 0) { //check if word has part of speech
                $pos = trim($wordDocument->find('#headbar .PART-OF-SPEECH')[0]->text()); //find part of speech of word
                if ($pos == 'noun') {
                    $totalNounEntries++;
                    if (count($elements = $wordDocument->find('.wordforms')) != 0) { //check if word has noun forms

                        $totalEntriesHaveNounForms++;

                        echo 'Current entry has noun forms: ' . $entryName . PHP_EOL;
                    }
                } elseif ($pos == 'adjective') {
                    $totalAdjectiveEntries++;
                    if (count($elements = $wordDocument->find('.wordforms')) != 0) { //check if word has adjective forms

                        $totalEntriesHaveAdjectiveForms++;

                        echo 'Current entry has adjective forms: ' . $entryName . PHP_EOL;
                    }
                } elseif ($pos == 'verb') {
                    $totalVerbEntries++;
                    if (count($elements = $wordDocument->find('.wordforms')) != 0) { //check if word has verb forms

                        $totalEntriesHaveVerbForms++;

                        echo 'Current entry has verb forms: ' . $entryName . PHP_EOL;
                    }
                }

            }
        }
    }
}

echo 'Total entries have noun forms/Total noun entries: ' . $totalEntriesHaveNounForms . '/' . $totalNounEntries . PHP_EOL;
echo 'Total entries have  adjective forms/Total adjective entries: ' . $totalEntriesHaveAdjectiveForms . '/' . $totalAdjectiveEntries . PHP_EOL;
echo 'Total entries have verb forms/Total verb entries: ' . $totalEntriesHaveVerbForms . '/' . $totalVerbEntries . PHP_EOL;
echo 'Statistics from Macmillan English Dictionary' . PHP_EOL;

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
