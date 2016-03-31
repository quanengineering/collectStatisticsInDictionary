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

$totalDefinitionsHaveSynonyms = 0;
$totalDefinitions = 0;
foreach ($wordsUrl as $wordUrl) {

    $wordDocument = new Document($wordUrl, true);

    foreach ($wordDocument->find('#dataset-british .sense-block') as $element) {
        $totalDefinitions += count($element->find('.def-head'));

        $totalDefinitionsHaveSynonyms += count($element->find('.cdo-smartt'));

        if (count($elements = $element->find('.def')) != 0) {
            echo 'Current definition has thesauruses: ' . $element->find('.def')[0]->text() . PHP_EOL;
        }
    }

}

echo 'Total definitions have thesauruses/Total definitions: ' . $totalDefinitionsHaveSynonyms . '/' . $totalDefinitions . PHP_EOL;
echo 'Statistics from Cambridge Advanced Learner’s Dictionary & Thesaurus' . PHP_EOL;

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
