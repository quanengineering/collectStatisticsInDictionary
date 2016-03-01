<?php

$startTime = microtime(true);

require "vendor/autoload.php";

use DiDom\Document;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

$writer = WriterFactory::create(Type::XLSX);
$writer->openToFile(str_replace('.php', '.xlsx', __FILE__));
$headerRow = ['Entry', 'alpha_key'];

$writer->addRow($headerRow);

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
        $entryName = $element->text();
        $entryName = substr($entryName, 0, strrpos($entryName, ' '));
        $alphaKey = $element->getAttribute('data-alphakey');

        $singleRow = [$entryName, $alphaKey];
        $writer->addRow($singleRow);

        echo $entryName . PHP_EOL;
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
