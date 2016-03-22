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

$lettersUrl = 'http://www.oxfordlearnersdictionaries.com/browse/english/';
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
$totalVerbFormsEntriesHaveAudio = 0;
$totalEntriesHaveVerbForms = 0;
$totalVerbEntries = 0;
$totalEntriesHaveAdjectiveForms = 0;
$totalAdjectiveEntries = 0;
foreach ($wordsUrl as $wordUrl) {

    if ($wordUrl != 'http://www.oxfordlearnersdictionaries.com/definition/english/nancy-drew') { //this link is NOT FOUND

        $wordDocument = new Document($wordUrl, true);

        if (count($elements = $wordDocument->find('.h')) != 0) { //check if word has name

            $entryName = $wordDocument->find('.h')[0]->text();

            if (count($elements = $wordDocument->find('.webtop-g .pos')) != 0) { //check if word has part of speech
                $pos = $wordDocument->find('.webtop-g .pos')[0]->text(); //find part of speech of word
                if ($pos == 'noun') {
                    $totalNounEntries++;
                    if (count($elements = $wordDocument->find('.if-gs')) != 0) { //check if word has noun forms

                        $totalEntriesHaveNounForms++;

                        echo 'Current entry has noun forms: ' . $entryName . PHP_EOL;
                        echo 'Total entries have noun forms: ' . $totalEntriesHaveNounForms . PHP_EOL;
                        echo 'Total noun entries: ' . $totalNounEntries . PHP_EOL;
                    }
                } elseif ($pos == 'adjective') {
                    $totalAdjectiveEntries++;
                    if (count($elements = $wordDocument->find('.if-gs')) != 0) { //check if word has adjective forms

                        $totalEntriesHaveAdjectiveForms++;

                        echo 'Current entry has adjective forms: ' . $entryName . PHP_EOL;
                        echo 'Total entries have adjective forms: ' . $totalEntriesHaveAdjectiveForms . PHP_EOL;
                        echo 'Total adjective entries: ' . $totalAdjectiveEntries . PHP_EOL;
                    }
                } elseif ($pos == 'verb') {
                    $totalVerbEntries++;
                    if (count($elements = $wordDocument->find("//span[contains(@unbox, 'verbforms')]", Query::TYPE_XPATH)) != 0) { //check if word has verb forms

                        $totalEntriesHaveVerbForms++;

                        if (count($elements = $wordDocument->find("//div[contains(@verbform, 'y')]", Query::TYPE_XPATH)) != 0) {
                            $totalVerbFormsEntriesHaveAudio++;
                        }

                        echo 'Current entry has verb forms: ' . $entryName . PHP_EOL;
                        echo 'Total verb forms entries have audio: ' . $totalVerbFormsEntriesHaveAudio . PHP_EOL;
                        echo 'Total entries have verb forms: ' . $totalEntriesHaveVerbForms . PHP_EOL;
                        echo 'Total verb entries: ' . $totalVerbEntries . PHP_EOL;
                    }
                }

            }
        }
    }
}

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
