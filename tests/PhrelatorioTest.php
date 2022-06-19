<?php

namespace PhrelatorioTest;

class PhrelatorioTest extends \PHPUnit\Framework\TestCase
{
    public function testForColumn(): void
    {
        $input = <<<XML
<?xml version="1.0"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
<table:table>
<table:table-row>
<table:table-cell>
                           <text:p><text:a xlink:href="phrelatorio://repeat%20item%20items">for "item items"</text:a></text:p>
</table:table-cell>
<table:table-cell>
                       <text:p><text:a xlink:href="phrelatorio://content%20item">value</text:a></text:p>
</table:table-cell>
<table:table-cell>
                           <text:p><text:a xlink:href="phrelatorio:///repeat">end</text:a></text:p>
</table:table-cell>

</table:table-row>
</table:table>
</document>
XML;

       $wants = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
  <table:table>
    <table:table-row>
      <table:table-cell>
        <text:p>A</text:p>
      </table:table-cell>
      <table:table-cell>
        <text:p>B</text:p>
      </table:table-cell>
      <table:table-cell>
        <text:p>C</text:p>
      </table:table-cell>
    </table:table-row>
  </table:table>
</document>

XML;

       $tml = OpenDocumentTester::fromFlatODT($input);
       $tml->assertContent(['items' => ['A', 'B', 'C']], $wants);
    }

    public function testForRow(): void
    {
        $input = <<<XML
<?xml version="1.0"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
<table:table>
<table:table-row>
<table:table-cell>
                           <text:p><text:a xlink:href="phrelatorio://repeat%20item%20items">for "item items"</text:a></text:p>
</table:table-cell>
</table:table-row>
<table:table-row>
<table:table-cell>
                       <text:p><text:a xlink:href="phrelatorio://content%20item">value</text:a></text:p>
</table:table-cell>
</table:table-row>
<table:table-row>
<table:table-cell>
                           <text:p><text:a xlink:href="phrelatorio:///repeat">end</text:a></text:p>
</table:table-cell>
</table:table-row>
</table:table>
</document>
XML;

       $wants = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
  <table:table>
    <table:table-row>
      <table:table-cell>
        <text:p>A</text:p>
      </table:table-cell>
    </table:table-row>
    <table:table-row>
      <table:table-cell>
        <text:p>B</text:p>
      </table:table-cell>
    </table:table-row>
    <table:table-row>
      <table:table-cell>
        <text:p>C</text:p>
      </table:table-cell>
    </table:table-row>
  </table:table>
</document>

XML;

       $tml = OpenDocumentTester::fromFlatODT($input);
       $tml->assertContent(['items' => ['A', 'B', 'C']], $wants);
    }

    public function testRepeatRowAndRepeatColumnConsecutive(): void
    {
        $input = <<<XML
<?xml version="1.0"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
<table:table>
<table:table-row>
<table:table-cell>
                           <text:p><text:a xlink:href="phrelatorio://repeat%20item%20items">for "item items"</text:a></text:p>
</table:table-cell>
</table:table-row>
<table:table-row>
<table:table-cell>
                       <text:p><text:a xlink:href="phrelatorio://content%20item">value</text:a></text:p>
</table:table-cell>
</table:table-row>
<table:table-row>
<table:table-cell>
                           <text:p><text:a xlink:href="phrelatorio:///repeat">end</text:a></text:p>
</table:table-cell>
</table:table-row>
<table:table-row>
<table:table-cell>
                           <text:p><text:a xlink:href="phrelatorio://repeat%20item%20items">for "item items"</text:a></text:p>
</table:table-cell>
<table:table-cell>
                       <text:p><text:a xlink:href="phrelatorio://content%20item">value</text:a></text:p>
</table:table-cell>
<table:table-cell>
                           <text:p><text:a xlink:href="phrelatorio:///repeat">end</text:a></text:p>
</table:table-cell>

</table:table-row>
</table:table>
</document>
XML;

       $wants = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
  <table:table>
    <table:table-row>
      <table:table-cell>
        <text:p>A</text:p>
      </table:table-cell>
    </table:table-row>
    <table:table-row>
      <table:table-cell>
        <text:p>B</text:p>
      </table:table-cell>
    </table:table-row>
    <table:table-row>
      <table:table-cell>
        <text:p>C</text:p>
      </table:table-cell>
    </table:table-row>
    <table:table-row>
      <table:table-cell>
        <text:p>A</text:p>
      </table:table-cell>
      <table:table-cell>
        <text:p>B</text:p>
      </table:table-cell>
      <table:table-cell>
        <text:p>C</text:p>
      </table:table-cell>
    </table:table-row>
  </table:table>
</document>

XML;

       $tml = OpenDocumentTester::fromFlatODT($input);
       $tml->assertContent(['items' => ['A', 'B', 'C']], $wants);
    }

    public function testConditionalRow(): void
    {
        $input = <<<XML
<?xml version="1.0"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
<table:table>
<table:table-row>
<table:table-cell>
    <text:p><text:a xlink:href="phrelatorio://condition%20show">for "item items"</text:a></text:p>
</table:table-cell>
</table:table-row>
<table:table-row>
<table:table-cell>
    <text:p>NOT SHOW THIS</text:p>
</table:table-cell>
</table:table-row>
<table:table-row>
<table:table-cell>
    <text:p><text:a xlink:href="phrelatorio:///condition">/condition</text:a></text:p>
</table:table-cell>
</table:table-row>
<table:table-row>
<table:table-cell>
    <text:p>SHOW THIS</text:p>
</table:table-cell>
</table:table-row>
</table:table>
</document>

XML;

        $wants = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
  <table:table>
    <table:table-row>
      <table:table-cell>
        <text:p>SHOW THIS</text:p>
      </table:table-cell>
    </table:table-row>
  </table:table>
</document>

XML;

        $tml = OpenDocumentTester::fromFlatODT($input);
        $tml->assertContent(['show' => false], $wants);
    }
}


class OpenDocumentTester extends \Phrelatorio\OpenDocument
{
    public function assertContent(array $context, string $wants): void
    {
        \PHPUnit\Framework\Assert::assertEquals($wants, $this->asXML($context));
    }
}
