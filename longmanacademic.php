<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;

$document = new Document("data/longmanacademic.html", true);

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Create first sheet
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setCellValue('A1', "Entry");
$objPHPExcel->getActiveSheet()->setCellValue('B1', "Part of Speech");

// Add data to first sheet
$objPHPExcel->setActiveSheetIndex(0);
$count = 2;
foreach ($document->find('a') as $element) {

    $entryName = $element->find('span')[0]->text();
    $pos = $element->find('i')[0]->text();

    if ($document->has('sup')) {
        $entryName = substr($entryName, 0, -1);
    }

    $objPHPExcel->getActiveSheet()->setCellValue('A' . $count, $entryName)
        ->setCellValue('B' . $count, $pos);

    $count++;

    echo $entryName . PHP_EOL;
}

// Rename first worksheet
$objPHPExcel->getActiveSheet()->setTitle('Longman');

// Save Excel 2007 file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
