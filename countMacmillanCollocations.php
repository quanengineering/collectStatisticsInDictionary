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

$lettersUrl = 'http://www.macmillandictionary.com/browse/british/';
$letters = new Document($lettersUrl, true);

$wordsUrl = array();

foreach ($letters->find('#letters a') as $letterUrl) {

    $groupResults = new Document($letterUrl->getAttribute('href'), true);

    foreach ($groupResults->find('#groupResult a') as $groupResult) {
        $result = new Document($groupResult->getAttribute('href'), true);

        foreach ($result->find('#result a') as $wordUrl) {
            $wordsUrl[] = $wordUrl->getAttribute('href');
        }
    }
}

$totalDefinitionsHaveCollocations = 0;
$totalDefinitions = 0;
foreach ($wordsUrl as $wordUrl) {

    if ($wordUrl != 'http://www.macmillandictionary.com/dictionary/british/huntington-s-disease' && $wordUrl != 'http://www.macmillandictionary.com/dictionary/british/za-atar') { //this link is NOT FOUND

        $wordDocument = new Document($wordUrl, true);

        if (count($elements = $wordDocument->find('.DEFINITION')) != 0) { //check if word has definitions

            foreach ($wordDocument->find('.SENSE') as $element) {
                $totalDefinitions++;

                if (count($elements = $element->find('.ONEBOX-HEAD')) != 0) { //check if word has additional boxes
                    $item = $element->find('.ONEBOX-HEAD')[0]->text();
                    if (substr($item, 0, strrpos($item, ':')) == 'Collocates') { //check if definition has collocation
                        $totalDefinitionsHaveCollocations++;

                        if (count($elements = $element->find('.DEFINITION')) != 0) {
                            echo 'Current definition has collocations: ' . $element->find('.DEFINITION')[0]->text() . PHP_EOL;
                        }
                    }
                }
            }
        }
    }
}

echo 'Total definitions have collocations/Total definitions: ' . $totalDefinitionsHaveCollocations . '/' . $totalDefinitions . PHP_EOL;
echo 'Statistics from Macmillan English Dictionary' . PHP_EOL;

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
