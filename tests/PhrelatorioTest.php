<?php

namespace PhrelatorioTest;

use PHPUnit\Framework\TestCase;
use Phrelatorio;

class PhrelatorioTest extends TestCase
{
    public function atestForColumn(): void
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
      <table:cell>
        <text:p>A</text:p>
      </table:cell>
      <table:cell>
        <text:p>B</text:p>
      </table:cell>
      <table:cell>
        <text:p>C</text:p>
      </table:cell>
    </table:table-row>
  </table:table>
</document>

XML;

$tml = Phrelatorio\OpenDocument::fromString($input);
    $this->assertEquals($wants, $tml->asXML(['items' => ['A', 'B', 'C']]));
    }

    public function atestForRow(): void
    {
        $input = <<<XML
<?xml version="1.0"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
<table:table>
<table:table-row>
<table:cell>
                           <text:p><text:a xlink:href="phrelatorio://for%20%22item%20items%22">for "item items"</text:a></text:p>
</table:cell>
</table:table-row>
<table:table-row>
<table:cell>
                       <text:p><text:a xlink:href="phrelatorio://content%20item">value</text:a></text:p>
</table:cell>
</table:table-row>
<table:table-row>
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
      <table:cell>
        <text:p>A</text:p>
      </table:cell>
    </table:table-row>
    <table:table-row>
      <table:cell>
        <text:p>B</text:p>
      </table:cell>
    </table:table-row>
    <table:table-row>
      <table:cell>
        <text:p>C</text:p>
      </table:cell>
    </table:table-row>
  </table:table>
</document>

XML;

$tml = Phrelatorio\OpenDocument::fromString($input);
    $this->assertEquals($wants, $tml->asXML(['items' => ['A', 'B', 'C']]));
    }


    public function testRepeatRowAndRepeatColumnConsecutive(): void
    {
        $input = <<<XML
<?xml version="1.0"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
<table:table>
<table:table-row id="row-repeat">
<table:cell>
                           <text:p><text:a xlink:href="phrelatorio://for%20%22item%20items%22">for "item items"</text:a></text:p>
</table:cell>
</table:table-row>
<table:table-row>
<table:cell>
                       <text:p><text:a xlink:href="phrelatorio://content%20item">value</text:a></text:p>
</table:cell>
</table:table-row>
<table:table-row>
<table:cell>
                           <text:p><text:a xlink:href="phrelatorio:///for">end</text:a></text:p>
</table:cell>
</table:table-row>
<table:table-row id="column-repeat">
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
      <table:cell>
        <text:p>A</text:p>
      </table:cell>
    </table:table-row>
    <table:table-row>
      <table:cell>
        <text:p>B</text:p>
      </table:cell>
    </table:table-row>
    <table:table-row>
      <table:cell>
        <text:p>C</text:p>
      </table:cell>
    </table:table-row>
    <table:table-row id="column-repeat">
      <table:cell>
        <text:p>A</text:p>
      </table:cell>
      <table:cell>
        <text:p>B</text:p>
      </table:cell>
      <table:cell>
        <text:p>C</text:p>
      </table:cell>
    </table:table-row>
  </table:table>
</document>

XML;

$tml = Phrelatorio\OpenDocument::fromString($input);
    $this->assertEquals($wants, $tml->asXML(['items' => ['A', 'B', 'C']]));
    }
}
