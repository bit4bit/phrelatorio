<?php

require_once __DIR__ . "/../vendor/autoload.php";

$doc = Phrelatorio\OpenDocument::loadXML('cuenta-cobro.fodt');
$data = [
    'date' => '2022-06-04',
    'subject' => 'ACA PROVEEDOR',
    'company' => 'LA COMPANY',
    'supplier' => 'PHRELATORIO',
    'taxid' => 'C.C colombiano',
    'city' => 'Medellin',
    'debt' => '12345689',
    'concept' => 'DESARROLLO SOFTWARE LIBRE',
    'items' => [
        'PHP',
        'C',
        'C++'
    ]
];
$doc->saveXML('./out_cuenta-cobro.fodt', $data);
