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

$topicUrl = 'http://www.ldoceonline.com/browse/topics.html';
$topics = new Document($topicUrl, true);

$subCategoriesUrl = array();

foreach ($topics->find('.topic_name') as $topic) {
    $subCategoriesUrl[] = substr(trim($topic->getAttribute('href')), 0, -1) . '-full/';
}

$totalMostFrequentlyWords = 0;
$totalMoreFrequentlyWords = 0;
$totalFrequentlyWords = 0;
$totalLessFrequentlyWords = 0;
$totalLessAndLessFrequentlyWords = 0;
$wordsNotDividedByFrequency = 0;
$totalWords = 0;
foreach ($subCategoriesUrl as $subCategoryUrl) {
    $document = new Document('http://www.ldoceonline.com' . $subCategoryUrl, true);

    echo $subCategoryUrl . PHP_EOL;

    $totalWordsNotDividedByFrequency += count($document->find('.size-'));
    $totalMostFrequentlyWords += count($document->find('.size-1'));
    $totalMoreFrequentlyWords += count($document->find('.size-2'));
    $totalFrequentlyWords += count($document->find('.size-3'));
    $totalLessFrequentlyWords += count($document->find('.size-4'));
    $totalLessAndLessFrequentlyWords += count($document->find('.size-5'));
    $totalLessAndLessFrequentlyWords += count($document->find('.size-6'));
    $totalWords += count($document->find('.clouditem'));
}

echo 'Total most frequently words: ' . $totalMostFrequentlyWords . PHP_EOL;
echo 'Total more frequently words: ' . $totalMoreFrequentlyWords . PHP_EOL;
echo 'Total frequently words: ' . $totalFrequentlyWords . PHP_EOL;
echo 'Total less frequently words: ' . $totalLessFrequentlyWords . PHP_EOL;
echo 'Total less and less frequently words: ' . $totalLessAndLessFrequentlyWords . PHP_EOL;
echo 'Total words not divided by frequency: ' . $totalWordsNotDividedByFrequency . PHP_EOL;
echo 'Total thesauruses subcategories/Total words in thesauruses subcategories: ' . count($subCategoriesUrl) . '/' . $totalWords . PHP_EOL;
echo 'Statistics from Longman Dictionary of Contemporary English 5th Edition' . PHP_EOL;

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
