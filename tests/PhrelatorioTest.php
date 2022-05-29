<?php

namespace PhrelatorioTest;

use PHPUnit\Framework\TestCase;
use Phrelatorio;

class PhrelatorioTest extends TestCase
{
    public function testBob(): void
    {
        $input = <<<XML
<?xml version="1.0"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
<table:table>
<table:table-row>
<table:cell>
                           <text:p><text:a xlink:href="phrelatorio://for%20%22item%20items%22">for "item items"</text:a></text:p>
</table:cell>
<table:cell>
                       <text:p><text:a xlink:href="phrelatorio://content%20item">value</text:a></text:p>
</table:cell>
<table:cell>
                           <text:p><text:a xlink:href="phrelatorio:///for">end</text:a></text:p>
</table:cell>

</table:table-row>
</table:table>
</document>
XML;

       $wants = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
  <table:table>
    <table:table-row>
      <table:cell>A</table:cell>
      <table:cell>B</table:cell>
      <table:cell>C</table:cell>
    </table:table-row>
  </table:table>
</document>
XML;

$tml = Phrelatorio\Template::fromString($input);
    $this->assertEquals($wants, $tml->execute(['items' => ['A', 'B', 'C']]));
    }
}
