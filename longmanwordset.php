<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Create first sheet
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setCellValue('A1', "Entry");
$objPHPExcel->getActiveSheet()->setCellValue('B1', "Part of Speech");
$objPHPExcel->getActiveSheet()->setCellValue('C1', "Word set");

// Add data to first sheet
$objPHPExcel->setActiveSheetIndex(0);
$count = 2;
for ($i = 1; $i <= 215; $i++) {
    if ($i < 10) {
        $wordsetUrl = str_pad($i, 4, '000', STR_PAD_LEFT);
    }
    if ($i < 100 && $i >= 10) {
        $wordsetUrl = str_pad($i, 4, '00', STR_PAD_LEFT);
    }
    if ($i < 1000 && $i >= 100) {
        $wordsetUrl = str_pad($i, 4, '0', STR_PAD_LEFT);
    }

    $wordsetUrl = 'http://global.longmandictionaries.com/popup/supp/ldoce6/thesaurus/ws' . $wordsetUrl;
    $document = new Document($wordsetUrl, true);

    $wordsetName = $document->find('.ws-head')[0]->text();

    foreach ($document->find('.wswd') as $entry) {
        $entry = $entry->text();
        $entryName = strstr($entry, ',', true);
        $pos = substr($entry, strpos($entry, ', ') + strlen(', '));

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $count, $entryName)
            ->setCellValue('B' . $count, $pos)
            ->setCellValue('C' . $count, $wordsetName);

        $count++;

        echo $entryName . PHP_EOL;
    }
}

// Rename first worksheet
$objPHPExcel->getActiveSheet()->setTitle('Longman');

// Create second sheet
$objPHPExcel->createSheet();

$objPHPExcel->setActiveSheetIndex(1);
$objPHPExcel->getActiveSheet()->setCellValue('A1', "Word set");
$objPHPExcel->getActiveSheet()->setCellValue('B1', "No.of entries");

//Add data to second sheet
$objPHPExcel->setActiveSheetIndex(1);
$count = 2;
for ($i = 1; $i <= 215; $i++) {
    if ($i < 10) {
        $wordsetUrl = str_pad($i, 4, '000', STR_PAD_LEFT);
    }
    if ($i < 100 && $i >= 10) {
        $wordsetUrl = str_pad($i, 4, '00', STR_PAD_LEFT);
    }
    if ($i < 1000 && $i >= 100) {
        $wordsetUrl = str_pad($i, 4, '0', STR_PAD_LEFT);
    }

    $wordsetUrl = 'http://global.longmandictionaries.com/popup/supp/ldoce6/thesaurus/ws' . $wordsetUrl;
    $document = new Document($wordsetUrl, true);

    $wordsetName = $document->find('.ws-head')[0]->text();

    $objPHPExcel->getActiveSheet()->setCellValue('A' . $count, $wordsetName);
}

// Rename second worksheet
$objPHPExcel->getActiveSheet()->setTitle('Summary');

// Save Excel 2007 file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
