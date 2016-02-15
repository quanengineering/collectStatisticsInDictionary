<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;

$homePageUrl = 'http://www.oxfordlearnersdictionaries.com/topic/';
$homePage = new Document($homePageUrl, true);

//create associative array to store name of subcategory - category
$categorySubcategoryNames = array();
$i = 0;
$j = 0;
foreach($homePage->find('#topic-list dd dl dt') as $element){
    $categoryName = $homePage->find('#topic-list dd dl dt')[$i]->text();
    if($homePage->find('#topic-list dd dl')[$j]->text() == ''){
        $j++;
    }
    foreach($homePage->find('#topic-list dd dl')[$j]->find('dd ul li') as $subcategory){
        $categorySubcategoryNames[$subcategory->text()] = $categoryName;
    }
    $i++;
    $j++;
}

// get subcategory's url
$subcategoryUrls = array();

foreach ($homePage->find('#topic-list dd dl dd ul li a') as $subcategoryUrl) {
    $subcategoryUrls[] = $subcategoryUrl->getAttribute('href');
}

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Create first sheet
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setCellValue('A1', "Entry");
$objPHPExcel->getActiveSheet()->setCellValue('B1', "Subcategory");
$objPHPExcel->getActiveSheet()->setCellValue('C1', "Category");
$objPHPExcel->getActiveSheet()->setCellValue('D1', "Subject");
$objPHPExcel->getActiveSheet()->setCellValue('E1', "Part of Speech");
$objPHPExcel->getActiveSheet()->setCellValue('F1', "Definition");

// Add data to first sheet
$objPHPExcel->setActiveSheetIndex(0);
$count = 2;
foreach ($subcategoryUrls as $subcategoryUrl) {
    $document = new Document($subcategoryUrl, true);

    $subcategoryName = $document->find('.selected')[0]->text();
    $categoryName = $categorySubcategoryNames[$subcategoryName];
    $subjectName = $document->find('.selected')[1]->text();

    $entryUrls = array();
    foreach ($document->find('.wordpool li a') as $entryUrl) {
        $entryUrls[] = $entryUrl->getAttribute('href');
    }

    foreach($entryUrls as $entryUrl){
        $document = new Document($entryUrl, true);

        $entryName = $document->find('.h')[0]->text();
        if ($document->has('.pos')) {
            $partOfSpeech = $document->find('.pos')[0]->text();
        } else {
            $partOfSpeech = '';
        }

        foreach ($document->find('.sn-gs span .def') as $entryDefinition) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $count, trim($entryName))
                ->setCellValue('B' . $count, $subcategoryName)
                ->setCellValue('C' . $count, $categoryName)
                ->setCellValue('D' . $count, $subjectName)
                ->setCellValue('E' . $count, $partOfSpeech)
                ->setCellValue('F' . $count, trim($entryDefinition->text()));
            $count++;
        }
        echo $entryName. PHP_EOL;
    }
}

// Rename first worksheet
$objPHPExcel->getActiveSheet()->setTitle('Oxford');

// Create second sheet
$objPHPExcel->createSheet();

$objPHPExcel->setActiveSheetIndex(1);
$objPHPExcel->getActiveSheet()->setCellValue('A1', "Subcategory");
$objPHPExcel->getActiveSheet()->setCellValue('B1', "No.of entries");
$objPHPExcel->getActiveSheet()->setCellValue('D1', "Category");
$objPHPExcel->getActiveSheet()->setCellValue('E1', "No.of entries");
$objPHPExcel->getActiveSheet()->setCellValue('G1', "Subject");
$objPHPExcel->getActiveSheet()->setCellValue('H1', "No.of entries");

//Add data to second sheet
$objPHPExcel->setActiveSheetIndex(1);
$count = 2;
foreach($homePage->find('#topic-list dd dl dd ul li') as $subcategory){
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $count, $subcategory->text());
    $count++;
}
$count = 2;
foreach($homePage->find('#topic-list dd dl dt') as $category){
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $count, $category->text());
    $count++;
}
$count = 2;
$subcategoryUrl = 'http://www.oxfordlearnersdictionaries.com/topic/animal_homes';
$subcategory = new Document($subcategoryUrl, true);
foreach($subcategory->find('#rightcolumn div div ul li') as $subject){
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $count, trim($subject->text()));
    $count++;
}

// Rename second worksheet
$objPHPExcel->getActiveSheet()->setTitle('Summary');

// Set active sheet index to the second sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(1);

// Save Excel 2007 file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

$endTime = microtime(true);
$executionTime = ($endTime - $startTime)/60;

echo 'Total Execution Time: '.$executionTime.' minutes' . PHP_EOL;

exit;