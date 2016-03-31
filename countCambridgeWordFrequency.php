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

$A1 = 0;
$A2 = 0;
$B1 = 0;
$B2 = 0;
$C1 = 0;
$C2 = 0;
foreach ($wordsUrl as $wordUrl) {

    $wordDocument = new Document($wordUrl, true);

    if (count($elements = $wordDocument->find('#dataset-british .headword')) != 0) {
        $entryName = $wordDocument->find('#dataset-british .headword')[0]->text();
    }

    $A1 += count($wordDocument->find('#dataset-british .A1'));
    $A2 += count($wordDocument->find('#dataset-british .A2'));
    $B1 += count($wordDocument->find('#dataset-british .B1'));
    $B2 += count($wordDocument->find('#dataset-british .B2'));
    $C1 += count($wordDocument->find('#dataset-british .C1'));
    $C2 += count($wordDocument->find('#dataset-british .C2'));

    echo 'Current entry: ' . $entryName . PHP_EOL;
}

echo 'Total definitions in' . PHP_EOL;
echo 'A1: Beginner level: ' . $A1 . PHP_EOL;
echo 'A2: Elementary level:' . $A2 . PHP_EOL;
echo 'B1: Intermediate level:' . $B1 . PHP_EOL;
echo 'B2: Upper-Intermediate level:' . $B2 . PHP_EOL;
echo 'C1: Advanced level:' . $C1 . PHP_EOL;
echo 'C2: Proficiency level:' . $C2 . PHP_EOL;

echo 'Statistics from Cambridge Advanced Learner’s Dictionary & Thesaurus' . PHP_EOL;

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
