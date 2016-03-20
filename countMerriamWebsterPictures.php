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

$alphalinksUrl = 'http://learnersdictionary.com/browse/learners/';
$alphalinks = new Document($alphalinksUrl, true);

$wordsUrl = array();

foreach ($alphalinks->find('.alphalinks a') as $alphalink) {

    $groupEntries = new Document('http://learnersdictionary.com' . $alphalink->getAttribute('href'), true);

    foreach ($groupEntries->find('.entries a') as $groupEntry) {
        $entry = new Document('http://learnersdictionary.com' . $groupEntry->getAttribute('href'), true);

        foreach ($entry->find('.entries a') as $wordUrl) {
            $wordsUrl[] = 'http://learnersdictionary.com' . $wordUrl->getAttribute('href');
        }
    }
}

$totalEntriesHavePictures = 0;
$totalEntries = 0;
foreach ($wordsUrl as $wordUrl) {

    $wordDocument = new Document($wordUrl, true);
    if (count($elements = $wordDocument->find('#ld_entries_v2_mainh')) != 0) { //check if entry has name

        $entryName = $wordDocument->find('#ld_entries_v2_mainh')[0]->text();

        $totalEntries++;

        if (count($elements = $wordDocument->find('.arts')) != 0) { //check if word has pictures

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
