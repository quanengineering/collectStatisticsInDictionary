<?php
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

foreach ($homePage->find('#topic-list dd dl dd ul li a') as $element) {
    $subcategoryUrls[] = $element->getAttribute('href');
}
//
////get word's url
////$first_subcategory_url = 'http://www.oxfordlearnersdictionaries.com/topic/animal_homes';
////$document = new Document($first_subcategory_url, true);
////
////$word_urls = array();
////
////foreach ($document->find('.wordpool li a') as $element) {
////    $word_urls[] = $element->getAttribute('href');
////}
//
////get word's definition
////$word_url = 'http://www.oxfordlearnersdictionaries.com/topic/animal_homes/aviary';
////$document = new Document($word_url, true);
////
////$word_definitions = array();
////
////foreach ($document->find('.sn-gs span .def') as $element) {
////    $word_definitions[] = $element->text();
////}

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Create first sheet
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setCellValue('A1', "Entry");
$objPHPExcel->getActiveSheet()->setCellValue('B1', "Subcategory");
$objPHPExcel->getActiveSheet()->setCellValue('C1', "Category");
$objPHPExcel->getActiveSheet()->setCellValue('D1', "Subject");

// Add data to first sheet
$objPHPExcel->setActiveSheetIndex(0);
$numberOfEntry = 2;
foreach ($subcategoryUrls as $subcategoryUrl) {
    $document = new Document($subcategoryUrl, true);

    $subcategoryName = $document->find('.selected')[0]->text();
    $categoryName = $categorySubcategoryNames[$subcategoryName];
    $subjectName = $document->find('.selected')[1]->text();

//    $word_urls = array();
//
//    foreach ($document->find('.wordpool li a') as $element) {
//        $word_urls[] = $element->getAttribute('href');
//    }

    foreach ($document->find('.wordpool li') as $entry) {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $numberOfEntry, $entry->text())
                                    ->setCellValue('B' . $numberOfEntry, $subcategoryName)
                                    ->setCellValue('C' . $numberOfEntry, $categoryName)
                                    ->setCellValue('D' . $numberOfEntry, $subjectName);
        $numberOfEntry++;
    }

//    foreach($word_urls as $word_url){
//        $document = new Document($word_url, true);
//
//        foreach ($document->find('.sn-gs span .def') as $element) {
//        }
//    }
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
$objWriter->setPreCalculateFormulas(false);
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));

?>