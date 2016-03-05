<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;
use DiDom\Query;
use Box\Spout\Reader\ReaderFactory;
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

$filePath = "data/longmanallword.csv";
$reader = ReaderFactory::create(Type::CSV);
$reader->open($filePath);

$count = 0;
foreach ($reader->getSheetIterator() as $sheet) {
    foreach ($sheet->getRowIterator() as $row) {

        $document = new Document($row[5]);
        //check if word is NOT in culture word list
        if (count($elements = $document->find("//span[contains(@type, 'encyc')]", Query::TYPE_XPATH)) == 0) {

            if ($document->has('.hyphenation')) {
                $word = $document->find('.hyphenation')[0];
                echo 'Word: ' . $word->text() . PHP_EOL;
            }

            foreach ($document->find('.sense') as $element) {
                if ($element->has('.subsense')) {
                    foreach ($element->find('.subsense .def') as $item) {
                        echo trim($item->text()) . PHP_EOL;
                        $count++;
                    }
                } else {
                    if ($element->has('.def')) {
                        $item = $element->find('.def')[0];
                        echo trim($item->text()) . PHP_EOL;
                        $count++;
                    }
                }
            }

        }

    }
}
echo 'Total definition: ' . $count . PHP_EOL;

$reader->close();

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;