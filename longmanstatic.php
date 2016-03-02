<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

$filePath = "data/longmanallword.csv";
$reader = ReaderFactory::create(Type::CSV);
$reader->open($filePath);

$count = 0;
foreach ($reader->getSheetIterator() as $sheet) {
    foreach ($sheet->getRowIterator() as $row) {

        $document = new Document($row[5]);
        if($document->has('.hyphenation')){
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

        echo $count . PHP_EOL;
    }
}

$reader->close();

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;