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

$lettersUrl = 'http://dictionary.cambridge.org/browse/english/';
$letters = new Document($lettersUrl, true);

$wordsUrl = array();

foreach ($letters->find('.cdo-browse-letters a') as $letterUrl) {

    $groupResults = new Document($letterUrl->getAttribute('href'), true);

    foreach ($groupResults->find('.cdo-browse-groups a') as $groupResult) {
        $result = new Document($groupResult->getAttribute('href'), true);

        foreach ($result->find('.cdo-browse-entries a') as $wordUrl) {
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

    $wordDocument = new Document($wordUrl, true);

    if (count($elements = $wordDocument->find('#dataset-british .pos-header')) != 0) { //check if word has name

        foreach ($wordDocument->find('#dataset-british .pos-header') as $element){

            $entryName = $element->find('.headword')[0]->text();

            if (count($elements = $element->find('.pos')) != 0) { //check if word has part of speech
                $pos = $element->find('.pos')[0]->text(); //find part of speech of word
                if ($pos == 'noun') {
                    $totalNounEntries++;
                    if (count($elements = $element->find("//span[contains(@type, 'plural')]", Query::TYPE_XPATH)) != 0) { //check if word has noun forms

                        $totalEntriesHaveNounForms++;

                        echo 'Current entry has noun forms: ' . $entryName . PHP_EOL;
                    }
                } elseif ($pos == 'adjective') {
                    $totalAdjectiveEntries++;
                    if (count($elements = $element->find('.irreg-infls')) != 0) { //check if word has adjective forms

                        $totalEntriesHaveAdjectiveForms++;

                        echo 'Current entry has adjective forms: ' . $entryName . PHP_EOL;
                    }
                } elseif ($pos == 'verb') {
                    $totalVerbEntries++;
                    if (count($elements = $element->find('.irreg-infls')) != 0) { //check if word has verb forms

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
echo 'Statistics from Cambridge Advanced Learner’s Dictionary & Thesaurus' . PHP_EOL;

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
