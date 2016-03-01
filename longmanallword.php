<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

$writer = WriterFactory::create(Type::XLSX);
$writer->openToFile(str_replace('.php', '.xlsx', __FILE__));
$headerRow = ['Entry', 'Part of Speech', 'alpha_key', 'key', 'entry_html'];

$writer->addRow($headerRow);

# Create a connection
$url = 'http://global.longmandictionaries.com/dict_search/get_initial_entries/ldoce6/';

$maxlength_other_words = 0;
$maxlength_entry_for_alpha_key = 0;
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
        $pos = substr($entry, strrpos($entry, ' ') + strlen(' '));
        if (!$pos) {
            $pos = '';
        }

        $alphaKey = $element->getAttribute('data-alphakey');
        $key = $element->getAttribute('data-key');

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
        $entry_for_alpha_key = curl_exec($ch);
        if($maxlength_entry_for_alpha_key < strlen($entry_for_alpha_key)){
            $maxlength_entry_for_alpha_key = strlen($entry_for_alpha_key);
        }
        curl_close($ch);

        # new data
        $data = array(
            'entry_key' => $key,
        );
        # Create a connection
        $url = 'http://global.longmandictionaries.com/dict_search/other_words/ldoce6/';
        $ch = curl_init($url);
        # Form data string
        $postString = http_build_query($data, '', '&');
        # Setting options
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        # Get the response
        $other_words = curl_exec($ch);
        if($maxlength_other_words < strlen($other_words)){
            $maxlength_other_words = strlen($other_words);
        }
        curl_close($ch);

        $singleRow = [$entryName, $pos, $alphaKey, $key, $element->html()];
        $writer->addRow($singleRow);

        echo $entryName . PHP_EOL;
        echo $maxlength_entry_for_alpha_key . PHP_EOL;
        echo $maxlength_other_words . PHP_EOL;
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

$endTime = microtime(true);
$executionTime = ($endTime - $startTime) / 60;

echo 'Total Execution Time: ' . $executionTime . ' minutes' . PHP_EOL;

exit;
