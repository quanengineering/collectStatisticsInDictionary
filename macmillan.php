<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

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

$writer = WriterFactory::create(Type::XLSX);
$writer->openToFile(str_replace('.php', '.xlsx', __FILE__));
$headerRow = ['Entry', 'Part of Speech', 'Frequency', 'Thesaurus', 'Sidebox'];
$writer->addRow($headerRow);

$count = 0;
foreach ($wordsUrl as $wordUrl) {

    $wordDocument = new Document($wordUrl, true);

    $entryName = $wordDocument->find('h1 .BASE')[0]->text();

    $pos = trim($wordDocument->find('.PART-OF-SPEECH')[0]->text());

    if (count($elements = $wordDocument->find('.redword')) != 0) {
        $frequency = $wordDocument->find('.icon_star');
        $frequency = count($frequency);
    } else {
        $frequency = '';
    }

    if (count($elements = $wordDocument->find('.moreButton')) != 0) {
        foreach ($wordDocument->find('.moreButton') as $element) {
            $singleRow = [$entryName, $pos, $frequency, $element->getAttribute('href')];
            $writer->addRow($singleRow);
        }
    } else {
        $singleRow = [$entryName, $pos, $frequency];
        $writer->addRow($singleRow);
    }

//    if (count($elements = $wordDocument->find('.ONEBOX-HEAD')) != 0) {
//        foreach ($wordDocument->find('.ONEBOX-HEAD') as $element) {
//            $element = $element->text();
//            $singleRow = [$entryName, $pos, $frequency, '', substr($element, 0, strrpos($element, ':'))];
//            $writer->addRow($singleRow);
//        }
//    } else {
//        $singleRow = [$entryName, $pos, $frequency];
//        $writer->addRow($singleRow);
//    }

    $count++;
    echo 'Number of words: ' . $count . PHP_EOL;
    echo $entryName . PHP_EOL;
}

$writer->close();

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
