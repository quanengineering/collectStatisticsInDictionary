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

$homePageUrl = 'http://learnersdictionary.com/3000-words';
$homePage = new Document($homePageUrl, true);

$entriesSelectorUrl = array();

foreach ($homePage->find('.unselected a') as $entrySelectorUrl) {
    $entriesSelectorUrl[] = 'http://learnersdictionary.com' . $entrySelectorUrl->getAttribute('href');
}

$writer = WriterFactory::create(Type::XLSX);
$writer->openToFile(str_replace('.php', '.xlsx', __FILE__));
$headerRow = ['Entry', 'Part of Speech'];
$writer->addRow($headerRow);

foreach ($entriesSelectorUrl as $entrySelectorUrl) {

    $nextPageUrl = $entrySelectorUrl;
    while (true) {
        if ($nextPageUrl != '') {
            $currentPage = new Document($nextPageUrl, true);
        } else {
            break;
        }

        //get data on current page
        foreach ($currentPage->find('.a_words li') as $element) {
            $entry = trim($element->text());
            $entryName = substr($entry, 0, strrpos($entry, '                                            ('));
            $pos = substr($entry, strrpos($entry, '                                            (') + strlen('                                            ('));
            $pos = substr($pos, 0, -1);

            $singleRow = [$entryName, $pos];
            $writer->addRow($singleRow);
        }

        //end get data on current page

        if (count($elements = $currentPage->find('.next')) != 0) {
            $nextPageUrl = 'http://learnersdictionary.com' . $currentPage->find('.next')[0]->getAttribute('href');
        } else {
            $nextPageUrl = '';
        }
    }
}

$writer->close();

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;