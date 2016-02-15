<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;

//English Dictionary
$homePageUrl = 'http://www.oxfordlearnersdictionaries.com/wordlist/english/academic/AcademicWordList_sublist_1/';
$homePage = new Document($homePageUrl, true);

$sublistsSelectorUrl = array($homePageUrl);

foreach ($homePage->find('.hide_phone a') as $sublistSelectorUrl) {
    $sublistsSelectorUrl[] = $sublistSelectorUrl->getAttribute('href');
}

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Create first sheet
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setCellValue('A1', "Entry");
$objPHPExcel->getActiveSheet()->setCellValue('B1', "Part of Speech");
$objPHPExcel->getActiveSheet()->setCellValue('C1', "Sublist");

// Add data to first sheet
$objPHPExcel->setActiveSheetIndex(0);
$count = 2;
foreach ($sublistsSelectorUrl as $sublistSelectorUrl) {

    $nextPageUrl = $sublistSelectorUrl;
    while (true) {
        if ($nextPageUrl != '') {
            $currentPage = new Document($nextPageUrl, true);
        } else {
            break;
        }

        //get data on current page

        $document = new Document($nextPageUrl, true);
        $sublistName = $document->find('.currentpage')[0]->text();

        $entryUrls = array();
        foreach ($document->find('.wordlist-oxford3000 li a') as $entryUrl) {
            $entryUrls[] = $entryUrl->getAttribute('href');
        }

        foreach ($entryUrls as $entryUrl) {
            $document = new Document($entryUrl, true);

            $entryName = $document->find('.h')[0]->text();
            if ($document->has('.pos')) {
                $partOfSpeech = $document->find('.pos')[0]->text();
            } else {
                $partOfSpeech = '';
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $count, trim($entryName))
                ->setCellValue('B' . $count, $partOfSpeech)
                ->setCellValue('C' . $count, $sublistName);
            $count++;

            echo $entryName . PHP_EOL;
        }

        //end get data on current page

        if ($currentPage->has('.activepage')) {
            if (array_pop($currentPage->find('.paging_links')[0]->find('a'))->text() == '>') {
                $nextPageUrl = array_pop($currentPage->find('.paging_links')[0]->find('a'))->getAttribute('href');
            } else {
                $nextPageUrl = '';
            }
        } else {
            $nextPageUrl = '';
        }
    }
}

// Rename first worksheet
$objPHPExcel->getActiveSheet()->setTitle('English Dictionary');

//American English Dictionary
$homePageUrl = 'http://www.oxfordlearnersdictionaries.com/wordlist/american_english/academic/sublist01/';
$homePage = new Document($homePageUrl, true);

$sublistsSelectorUrl = array($homePageUrl);

foreach ($homePage->find('.hide_phone a') as $sublistSelectorUrl) {
    $sublistsSelectorUrl[] = $sublistSelectorUrl->getAttribute('href');
}

// Create second sheet
$objPHPExcel->createSheet();

$objPHPExcel->setActiveSheetIndex(1);
$objPHPExcel->getActiveSheet()->setCellValue('A1', "Entry");
$objPHPExcel->getActiveSheet()->setCellValue('B1', "Part of Speech");
$objPHPExcel->getActiveSheet()->setCellValue('C1', "Sublist");

// Add data to second sheet
$objPHPExcel->setActiveSheetIndex(1);
$count = 2;
foreach ($sublistsSelectorUrl as $sublistSelectorUrl) {

    $nextPageUrl = $sublistSelectorUrl;
    while (true) {
        if ($nextPageUrl != '') {
            $currentPage = new Document($nextPageUrl, true);
        } else {
            break;
        }

        //get data on current page

        $document = new Document($nextPageUrl, true);
        $sublistName = $document->find('.currentpage')[0]->text();

        $entryUrls = array();
        foreach ($document->find('.wordlist-oxford3000 li a') as $entryUrl) {
            $entryUrls[] = $entryUrl->getAttribute('href');
        }

        foreach ($entryUrls as $entryUrl) {
            $document = new Document($entryUrl, true);

            $entryName = $document->find('.h')[0]->text();
            if ($document->has('.pos')) {
                $partOfSpeech = $document->find('.pos')[0]->text();
            } else {
                $partOfSpeech = '';
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $count, trim($entryName))
                ->setCellValue('B' . $count, $partOfSpeech)
                ->setCellValue('C' . $count, $sublistName);
            $count++;

            echo $entryName . PHP_EOL;
        }

        //end get data on current page

        if ($currentPage->has('.activepage')) {
            if (array_pop($currentPage->find('.paging_links')[0]->find('a'))->text() == '>') {
                $nextPageUrl = array_pop($currentPage->find('.paging_links')[0]->find('a'))->getAttribute('href');
            } else {
                $nextPageUrl = '';
            }
        } else {
            $nextPageUrl = '';
        }
    }
}

// Rename second worksheet
$objPHPExcel->getActiveSheet()->setTitle('American English Dictionary');

// Save Excel 2007 file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;