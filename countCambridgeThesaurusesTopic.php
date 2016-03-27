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

$subCategoriesUrl = array();

foreach ($wordsUrl as $wordUrl) {

    $wordDocument = new Document($wordUrl, true);

    if (count($elements = $wordDocument->find('#dataset-british .headword')) != 0) { //check if word has name

        $entryName = $wordDocument->find('#dataset-british .headword')[0]->text();

        if (count($elements = $wordDocument->find('#dataset-british .cdo-topic')) != 0) { //check if word has synonym
            foreach ($wordDocument->find('#dataset-british .cdo-topic') as $subCategoryUrl) {
                if (!in_array($subCategoryUrl->getAttribute('href'), $subCategoriesUrl)) { //check if thesaurus is not existed
                    $subCategoriesUrl[] = $subCategoryUrl->getAttribute('href');
                }
            }
            echo 'Current entry has thesauruses: ' . $entryName . PHP_EOL;
        }
    }
}

$totalMostFrequentlyWords = 0;
$totalMoreFrequentlyWords = 0;
$totalFrequentlyWords = 0;
$totalLessFrequentlyWords = 0;
$totalWords = 0;
foreach ($subCategoriesUrl as $subCategoryUrl) {
    $document = new Document($subCategoryUrl, true);

    $totalMostFrequentlyWords += count($document->find('.topic_3'));
    $totalMoreFrequentlyWords += count($document->find('.topic_2'));
    $totalFrequentlyWords += count($document->find('.topic_1'));
    $totalLessFrequentlyWords += count($document->find('.topic_0'));

    $totalWords += count($document->find('.hwd'));
}

echo 'Total most frequently words: ' . $totalMostFrequentlyWords . PHP_EOL;
echo 'Total more frequently words: ' . $totalMoreFrequentlyWords . PHP_EOL;
echo 'Total frequently words: ' . $totalFrequentlyWords . PHP_EOL;
echo 'Total less frequently words: ' . $totalLessFrequentlyWords . PHP_EOL;
echo 'Total thesauruses subcategories/Total words in thesauruses subcategories: ' . count($subCategoriesUrl) . '/' . $totalWords . PHP_EOL;
echo 'Statistics from Cambridge Advanced Learner’s Dictionary & Thesaurus' . PHP_EOL;

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
