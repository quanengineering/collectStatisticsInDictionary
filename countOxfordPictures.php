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

$totalEntriesHavePictures = 0;
$totalEntries = 0;
foreach ($wordsUrl as $wordUrl) {
    if ($wordUrl != 'http://www.oxfordlearnersdictionaries.com/definition/english/nancy-drew') {

        $wordDocument = new Document($wordUrl, true);

        $entryName = $wordDocument->find('.h')[0]->text();

        $totalEntries++;

        if (count($elements = $wordDocument->find('#ox-enlarge')) != 0) { //check if word has pictures

            $totalEntriesHavePictures++;

            echo 'Current entry has pictures: ' . $entryName . PHP_EOL;
            echo 'Total entries have pictures: ' . $totalEntriesHavePictures . PHP_EOL;
            echo 'Total entries: ' . $totalEntries . PHP_EOL;
        }
    }
}

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
