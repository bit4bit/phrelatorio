<?php

$doc = Phrelatorio\OpenDocument::loadXML('example1.fods');
$doc->saveXML('./out_example1.fods', ['title' => 'HOLA', 'items' => ['GUIX', 'PARABOLA', 'TRISQUEL']]);
