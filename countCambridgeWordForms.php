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

$lettersUrl = 'http://dictionary.cambridge.org/browse/english/';
$letters = new Document($lettersUrl, true);

$wordsUrl = array();

foreach ($letters->find('.cdo-browse-letters a') as $letterUrl) {

    $groupResults = new Document($letterUrl->getAttribute('href'), true);

    foreach ($groupResults->find('.cdo-browse-groups a') as $groupResult) {
        $result = new Document($groupResult->getAttribute('href'), true);

        foreach ($result->find('.cdo-browse-entries a') as $wordUrl) {
            $wordsUrl[] = $wordUrl->getAttribute('href');
        }
    }
}

$totalWordsHaveNounForms = 0;
$totalNounWords = 0;
$totalWordsHaveVerbForms = 0;
$totalVerbWords = 0;
$totalWordsHaveAdjectiveForms = 0;
$totalAdjectiveWords = 0;
foreach ($wordsUrl as $wordUrl) {
    $wordUrl = 'http://dictionary.cambridge.org/dictionary/english/beautiful';
    $wordDocument = new Document($wordUrl, true);

    if (count($elements = $wordDocument->find('#dataset-british .headword')) != 0) { //check if word has name

        $wordName = $wordDocument->find('#dataset-british .headword')[0]->text();

        if (count($elements = $wordDocument->find('#dataset-british .pos')) != 0) { //check if word has part of speech
            $pos = $wordDocument->find('#dataset-british .pos')[0]->text(); //find part of speech of word
            if ($pos == 'noun') {
                $totalNounWords++;
                if (count($elements = $wordDocument->find('#dataset-british')[0]->find("//span[contains(@type, 'plural')]", Query::TYPE_XPATH)) != 0) { //check if word has noun forms

                    $totalWordsHaveNounForms++;

                    echo 'Current word has noun forms: ' . $wordName . PHP_EOL;
                }
            } elseif ($pos == 'adjective') {
                $totalAdjectiveWords++;
                if (count($elements = $wordDocument->find('#dataset-british .irreg-infls')) != 0) { //check if word has adjective forms

                    $totalWordsHaveAdjectiveForms++;

                    echo 'Current word has adjective forms: ' . $wordName . PHP_EOL;
                }
            } elseif ($pos == 'verb') {
                $totalVerbWords++;
                if (count($elements = $wordDocument->find('#dataset-british .irreg-infls')) != 0) { //check if word has verb forms

                    $totalWordsHaveVerbForms++;

                    echo 'Current word has verb forms: ' . $wordName . PHP_EOL;
                }
            }

        }
    }

    if (count($elements = $wordDocument->find('#dataset-british .runon')) != 0) { //check if word has derived word

        foreach ($wordDocument->find('#dataset-british .runon') as $element) {

            $wordName = $element->find('.w')[0]->text();

            if (count($elements = $element->find('.pos')) != 0) { //check if word has part of speech
                $pos = $element->find('.pos')[0]->text(); //find part of speech of word
                if ($pos == 'noun') {
                    $totalNounWords++;
                    if (count($elements = $element->find("//span[contains(@type, 'plural')]", Query::TYPE_XPATH)) != 0) { //check if word has noun forms

                        $totalWordsHaveNounForms++;

                        echo 'Current word has noun forms: ' . $wordName . PHP_EOL;
                    }
                } elseif ($pos == 'adjective') {
                    $totalAdjectiveWords++;
                    if (count($elements = $wordDocument->find('.irreg-infls')) != 0) { //check if word has adjective forms

                        $totalWordsHaveAdjectiveForms++;

                        echo 'Current word has adjective forms: ' . $wordName . PHP_EOL;
                    }
                } elseif ($pos == 'verb') {
                    $totalVerbWords++;
                    if (count($elements = $wordDocument->find('.irreg-infls')) != 0) { //check if word has verb forms

                        $totalWordsHaveVerbForms++;

                        echo 'Current word has verb forms: ' . $wordName . PHP_EOL;
                    }
                }
            }

        }
    }

}

echo 'Total words have noun forms/Total noun words: ' . $totalWordsHaveNounForms . '/' . $totalNounWords . PHP_EOL;
echo 'Total words have  adjective forms/Total adjective words: ' . $totalWordsHaveAdjectiveForms . '/' . $totalAdjectiveWords . PHP_EOL;
echo 'Total words have verb forms/Total verb words: ' . $totalWordsHaveVerbForms . '/' . $totalVerbWords . PHP_EOL;
echo 'Statistics from Cambridge Advanced Learner’s Dictionary & Thesaurus' . PHP_EOL;

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
