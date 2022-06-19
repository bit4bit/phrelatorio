<?php

require_once __DIR__ . "/../vendor/autoload.php";

$doc = Phrelatorio\OpenDocument::loadFlatODT('example5.fods');
$doc->save('./out_example5.fods', ['show' => false, 'title' => 'HOLA', 'items' => [['GUIX'], ['PARABOLA'], ['TRISQUEL']]]);
