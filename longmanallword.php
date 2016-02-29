<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Create first sheet
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setCellValue('A1', "Entry");
$objPHPExcel->getActiveSheet()->setCellValue('B1', "alpha_key");

# Create a connection
$url = 'http://global.longmandictionaries.com/dict_search/get_initial_entries/ldoce6/';

// Add data to first sheet
$objPHPExcel->setActiveSheetIndex(0);
$count = 2;

do {
    $ch = curl_init($url);
    # Setting options
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    # Get the response
    $response = curl_exec($ch);
    curl_close($ch);

    $document = new Document($response);
    foreach ($document->find('a') as $element) {
        $entryName = $element->text();
        $entryName = substr($entryName, 0, strrpos($entryName, ' '));
        $alphaKey = $element->getAttribute('data-alphakey');

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $count, $entryName)
            ->setCellValue('B' . $count, $alphaKey);
        $count++;

        echo $entryName . PHP_EOL;
    }

    $url = 'http://global.longmandictionaries.com/dict_search/get_entry_chunk_for_alpha_key/ldoce6/' . $alphaKey . '/1/';

    sleep(5);

} while ($alphaKey != 'zzz');

// Rename first worksheet
$objPHPExcel->getActiveSheet()->setTitle('Longman');

// Save Excel 2007 file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
