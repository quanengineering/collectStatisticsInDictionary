<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;
use DiDom\Query;
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

$writer = WriterFactory::create(Type::XLSX);
$writer->openToFile(str_replace('.php', '.xlsx', __FILE__));
$headerRow = ['Entry', 'Part of Speech', 'alpha_key', 'definition'];
$writer->addRow($headerRow);

$count_total_word = 0;
$count_word_is_not_encyclopaedic_entry = 0;
$count_definition_is_not_encyclopaedic_entry = 0;
# Create a connection
$url = 'http://global.longmandictionaries.com/dict_search/get_initial_entries/ldoce6/';

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

        $entry = $element->text();
        $entryName = substr($entry, 0, strrpos($entry, ' '));
        if (count($elements = $element->find('sup')) != 0) {
            $entryName = substr($entryName, 0, -1);
        }
        $pos = substr($entry, strrpos($entry, ' ') + strlen(' '));
        if (!$pos) {
            $pos = '';
        }

        $alphaKey = $element->getAttribute('data-alphakey');

        //check if word is an important word
        if (count($elements = $element->find('kw')) != 0) {

            # new data
            $data = array(
                'alpha_key' => $alphaKey,
                'name' => ''
            );
            # Create a connection
            $url = 'http://global.longmandictionaries.com/dict_search/entry_for_alpha_key/ldoce6/';
            $ch = curl_init($url);
            # Form data string
            $postString = http_build_query($data, '', '&');
            # Setting options
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            # Get the response
            $entry_detail = curl_exec($ch);

            curl_close($ch);

            $entry_detail_document = new Document($entry_detail);
            //check if word is NOT an encyclopaedic entry
            if (count($elements = $entry_detail_document->find("//span[contains(@type, 'encyc')]", Query::TYPE_XPATH)) == 0) {

                if (count($elements = $entry_detail_document->find('.hyphenation')) != 0) {
                    $word = $entry_detail_document->find('.hyphenation')[0];
                    echo 'Word: ' . $word->text() . PHP_EOL;
                    $count_word_is_not_encyclopaedic_entry++;
                }

                foreach ($entry_detail_document->find('.sense') as $element) {
                    if (count($elements = $element->find('.subsense')) != 0) {
                        foreach ($element->find('.subsense .def') as $item) {
                            echo trim($item->text()) . PHP_EOL;
                            $count_definition_is_not_encyclopaedic_entry++;
                            $singleRow = [$entryName, $pos, $alphaKey, trim($item->text())];
                            $writer->addRow($singleRow);
                        }
                    } else {
                        if (count($elements = $element->find('.def')) != 0) {
                            $item = $element->find('.def')[0];
                            echo trim($item->text()) . PHP_EOL;
                            $count_definition_is_not_encyclopaedic_entry++;
                            $singleRow = [$entryName, $pos, $alphaKey, trim($item->text())];
                            $writer->addRow($singleRow);
                        }
                    }
                }

            }
            $count_total_word++;

            echo $entryName . PHP_EOL;
        }
    }

    if ($alphaKey == 'insipidness,_insipidity_d1') {
        $alphaKey = 'insipidly_d1';
    }

    if ($alphaKey == 'leninist,_leninite_d1') {
        $alphaKey = 'leninism';
    }

    $url = 'http://global.longmandictionaries.com/dict_search/get_entry_chunk_for_alpha_key/ldoce6/' . $alphaKey . '/1/';

} while ($alphaKey != 'zzz');

$writer->close();

echo 'Total word: ' . $count_total_word . PHP_EOL;
echo 'Total word is not encyclopaedic entry: ' . $count_word_is_not_encyclopaedic_entry . PHP_EOL;
echo 'Total definition is not encyclopaedic entry: ' . $count_definition_is_not_encyclopaedic_entry . PHP_EOL;

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;