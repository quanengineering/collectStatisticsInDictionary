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

$writer = WriterFactory::create(Type::XLSX);
$writer->openToFile(str_replace('.php', '.xlsx', __FILE__));
$headerRow = ['Entry', 'Part of Speech'];
$writer->addRow($headerRow);

for ($i = 1; $i <= 20; $i++) {
    $documentUrl = 'http://www.oxforddictionaries.com/top1000/english?page=' . $i;
    $document = new Document($documentUrl, true);

    foreach ($document->find('.arl_hw') as $element) {
        echo $element->text();
        $singleRow = [$element->text()];
        $writer->addRow($singleRow);
    }
}

for ($i = 1; $i <= 20; $i++) {
    $documentUrl = 'http://www.oxforddictionaries.com/top1000/american_english?page=' . $i;
    $document = new Document($documentUrl, true);

    foreach ($document->find('.arl_hw') as $element) {
        echo $element->text();
        $singleRow = [$element->text()];
        $writer->addRow($singleRow);
    }
}

$writer->close();

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;