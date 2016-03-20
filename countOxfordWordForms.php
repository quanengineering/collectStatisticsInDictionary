<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;

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

$numberOfEntriesHaveNounForms = 0;
$totalNounEntries = 0;
$numberOfEntriesHaveVerbForms = 0;
$totalVerbEntries = 0;
foreach ($wordsUrl as $wordUrl) {
    if ($wordUrl != 'http://www.oxfordlearnersdictionaries.com/definition/english/nancy-drew') {

        $wordDocument = new Document($wordUrl, true);

        $entryName = $wordDocument->find('.h')[0]->text();

        if (count($elements = $wordDocument->find('.webtop-g .pos')) != 0) {
            $pos = $wordDocument->find('.webtop-g .pos')[0]->text();
            if ($pos == 'noun') {
                $totalNounEntries++;
                if (count($elements = $wordDocument->find('.if-g')) != 0) {

                    $numberOfEntriesHaveNounForms++;

                    echo 'Current entry have noun forms: ' . $entryName . PHP_EOL;
                    echo 'Total noun entries: ' . $totalNounEntries . PHP_EOL;
                    echo 'Total entries have noun forms: ' . $numberOfEntriesHaveNounForms . PHP_EOL;

                }
            } elseif ($pos == 'verb') {
                $totalVerbEntries++;
                if (count($elements = $wordDocument->find('.vp-g')) != 0) {

                    $numberOfEntriesHaveVerbForms++;

                    echo 'Current entry have verb forms: ' . $entryName . PHP_EOL;
                    echo 'Total verb entries: ' . $totalVerbEntries . PHP_EOL;
                    echo 'Total entries have verb forms: ' . $numberOfEntriesHaveVerbForms . PHP_EOL;
                }
            }

        }
    }
}

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
