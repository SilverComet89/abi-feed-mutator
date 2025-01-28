<?php

namespace SilverComet;

require_once __DIR__ . '/lib/Import.php';

use SilverComet\lib\Import;
use DOMDocument;
use DomXPath;

$import = new Import();

$data = $import->getFeed('https://www.propertyportalmarketing.com/xml/abire-apits.xml');
$import->dumpToFile(__DIR__ . '/source.xml', $data);

$dom = new DOMDocument();
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($data);
$xpath = new DOMXpath($dom);
$properties = $dom->getElementsByTagName('property');

$redundantNodes = [];

// Check for redundant properties
foreach ($xpath->query('//property') as $node) {
    if (strpos(strtolower($node->getElementsByTagName('PropertyName')->item(0)->nodeValue), 'reunion') === false) {
        $redundantNodes[] = $node;
    }
}

// Remove redundant properties
foreach ($redundantNodes as $redundantNode) {
    $redundantNode->parentNode->removeChild($redundantNode);
}

// Now we sort out property type
foreach ($xpath->query('//property') as $node) {
    $notMansion = false;
    if (strpos(strtolower($node->getElementsByTagName('en')->item(0)->nodeValue), ' condo ') !== false || strpos(strtolower($node->getElementsByTagName('en')->item(0)->nodeValue), ' condominium ') !== false) {
        $node->getElementsByTagName('type')->item(0)->nodeValue = 'Condo';
        $notMansion = true;
    }

    if (strpos(strtolower($node->getElementsByTagName('en')->item(0)->nodeValue), ' villa ') !== false) {
        $node->getElementsByTagName('type')->item(0)->nodeValue = 'Villa';
        $notMansion = true;
    }
    
    // Strip MLS
    $mlsToCut = strpos(strtolower($node->getElementsByTagName('PropertyName')->item(0)->nodeValue), ' (mls');
    if ($mlsToCut !== false) {
        $node->getElementsByTagName('PropertyName')->item(0)->nodeValue = substr(
            $node->getElementsByTagName('PropertyName')->item(0)->nodeValue,
            0,
            $mlsToCut
        );
    }

    // Now we create the zip and address field
    $zipNode = $dom->createElement("zip");
    $node->appendChild($zipNode);
    $addressNode = $dom->createElement("street");
    $node->appendChild($addressNode);
    
    // Now we add the value to our new node.
    $addressStrLen = strlen($node->getElementsByTagName('PropertyName')->item(0)->nodeValue);
    $posOfZip = strpos($node->getElementsByTagName('PropertyName')->item(0)->nodeValue, 'FL ');
    $zip = substr($node->getElementsByTagName('PropertyName')->item(0)->nodeValue, $posOfZip, $addressStrLen);

    $address = rtrim(substr($node->getElementsByTagName('PropertyName')->item(0)->nodeValue, 0, $posOfZip), ', ');
    $node->getElementsByTagName('zip')->item(0)->nodeValue = $zip;
    $node->getElementsByTagName('street')->item(0)->nodeValue = $address;
    
    
    
    if (
        $notMansion != true 
        && (int) $node->getElementsByTagName('plot')->item(0)->nodeValue >= 1000 
        && (int) $node->getElementsByTagName('price')->item(0)->nodeValue >= 1000000
        && (int) $node->getElementsByTagName('beds')->item(0)->nodeValue > 6
        ) {
        $node->getElementsByTagName('type')->item(0)->nodeValue = 'Mansion';
    }
    
}

$dom->save(__DIR__ . '/stream.xml');

header('Content-Type: application/xml');
print file_get_contents(__DIR__ . '/stream.xml');
