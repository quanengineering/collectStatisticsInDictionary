<?php
require "vendor/autoload.php";

use DiDom\Document;

$homepage_url = 'http://www.oxfordlearnersdictionaries.com/topic/';
$homepage = new Document($homepage_url, true);

//create associative array to store name of subcategory - category
$category_subcategory_name = array();
$i = 0;
$j = 0;
foreach($homepage->find('#topic-list dd dl dt') as $element){
    $category_name = $homepage->find('#topic-list dd dl dt')[$i]->text();
    if($homepage->find('#topic-list dd dl')[$j]->text() == ''){
        $j++;
    }
    foreach($homepage->find('#topic-list dd dl')[$j]->find('dd ul li') as $subcategory){
        $category_subcategory_name[$subcategory->text()] = $category_name;
    }
    $i++;
    $j++;
}

// get subcategory's url
$subcategory_urls = array();

foreach ($homepage->find('#topic-list dd dl dd ul li a') as $element) {
    $subcategory_urls[] = $element->getAttribute('href');
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
$count = 2;
foreach ($subcategory_urls as $subcategory_url) {
    $document = new Document($subcategory_url, true);

    $subcategory_name = $document->find('.selected')[0]->text();
    $category_name = $category_subcategory_name[$subcategory_name];
    $subject_name = $document->find('.selected')[1]->text();

//    $word_urls = array();
//
//    foreach ($document->find('.wordpool li a') as $element) {
//        $word_urls[] = $element->getAttribute('href');
//    }

    foreach ($document->find('.wordpool li') as $word) {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $count, $word->text())
                                    ->setCellValue('B' . $count, $subcategory_name)
                                    ->setCellValue('C' . $count, $category_name)
                                    ->setCellValue('D' . $count, $subject_name);
        $count++;
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
$objPHPExcel->getActiveSheet()->setCellValue('B1', "No.of words");
$objPHPExcel->getActiveSheet()->setCellValue('C1', "Category");
$objPHPExcel->getActiveSheet()->setCellValue('D1', "No.of words");
$objPHPExcel->getActiveSheet()->setCellValue('E1', "Subject");
$objPHPExcel->getActiveSheet()->setCellValue('F1', "No.of words");

//Add data to second sheet
$objPHPExcel->setActiveSheetIndex(1);
$count = 2;
foreach($homepage->find('#topic-list dd dl dd ul li') as $subcategory){
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $count, $subcategory->text());
    $count++;
}
$count = 2;
foreach($homepage->find('#topic-list dd dl dt') as $category){
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $count, $category->text());
    $count++;
}
$count = 2;
$subcategory_url = 'http://www.oxfordlearnersdictionaries.com/topic/animal_homes';
$subcategory = new Document($subcategory_url, true);
foreach($subcategory->find('#rightcolumn div div ul li') as $subject){
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $count, trim($subject->text()));
    $count++;
}

// Rename second worksheet
$objPHPExcel->getActiveSheet()->setTitle('Summary');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Save Excel 2007 file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save(str_replace('.php', '.xls', __FILE__));

?>