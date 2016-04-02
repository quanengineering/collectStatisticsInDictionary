<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;
use DiDom\Query;

//error handler function
function customError($errno, $errstr)
{
    echo PHP_EOL . "<b>Error:</b> [$errno] $errstr<br>";
    echo PHP_EOL . "Ending Script" . PHP_EOL;
    die();
}

//set error handler
set_error_handler("customError");

$subCategoriesUrl = array();
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
    $subCategoriesUrl[] = 'http://global.longmandictionaries.com/popup/supp/ldoce6/thesaurus/ws' . $wordsetUrl;
}

$totalWords = 0;
foreach ($subCategoriesUrl as $subCategoryUrl) {
    $document = new Document($subCategoryUrl, true);

    $totalWords += count($document->find('.ws-head'));
}

echo 'Total thesauruses subcategories/Total words in thesauruses subcategories: ' . count($subCategoriesUrl) . '/' . $totalWords . PHP_EOL;
echo 'Statistics from Longman Dictionary of Contemporary English' . PHP_EOL;

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
