<?php

namespace PhrelatorioTest;

class PhrelatorioTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function on_unopened_blockes_raises_exception(): void
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
                           <text:p><text:a xlink:href="phrelatorio:///condition%20item%20items">for "item items"</text:a></text:p>
</table:table-cell>
</table:table-row>
</table:table>
</document>
XML;

        $this->expectException(\Exception::class);
        $this->renderTemplate($input, ['items' => ['A', 'B', 'C']]);
    }

    /**
     * @test
     */
    public function on_unbalanced_blocks_raises_exception(): void
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
</table:table>
</document>
XML;

        $this->expectException(\Exception::class);
        $this->renderTemplate($input, ['items' => ['A', 'B', 'C']]);
    }

    /**
     * @test
     */
    public function repeat_column(): void
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

        $this->assertEquals(
            $wants,
            $this->renderTemplate($input, ['items' => ['A', 'B', 'C']])
        );
    }

    /**
     * @test
     */
    public function repeat_row(): void
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

        $this->assertEquals(
            $wants,
            $this->renderTemplate($input, ['items' => ['A', 'B', 'C']])
        );
    }

    /**
     * @test
     */
    public function repeat_row_with_repeat_nested_column(): void
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

        $this->assertEquals(
            $wants,
            $this->renderTemplate($input, ['items' => ['A', 'B', 'C']])
        );
    }

    /**
     * @test
     */
    public function conditional_row(): void
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

        $this->assertEquals(
            $wants,
            $this->renderTemplate($input, ['show' => false])
        );
    }

    /**
     * @test
     */
    public function execute_PHP_Expression(): void
    {
            $input = <<<XML
<?xml version="1.0"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
<table:table>
<table:table-row>
<table:table-cell>
    <text:p><text:a xlink:href="phrelatorio://content%20php:%20a%20+%20b">a + b</text:a></text:p>
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
        <text:p>5</text:p>
      </table:table-cell>
    </table:table-row>
  </table:table>
</document>

XML;

        $this->assertEquals(
            $wants,
            $this->renderTemplate($input, ['a' => 2, 'b' => 3])
        );
    }

    /**
     * @test
     */
    public function do_nothing_if_not_have_template_elements(): void
    {
        $input = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<document xmlns="http://test" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:xlink="http://www.w3.org/1999/xlink">
  <table:table>
    <table:table-row>
      <table:table-cell>
      </table:table-cell>
    </table:table-row>
  </table:table>
</document>

XML;

        $this->assertEquals(
            $input,
            $this->renderTemplate($input, [])
        );
    }

    private function renderTemplate(string $input, array $context): string
    {
        $tml = \Phrelatorio\OpenDocument\Template::fromString($input);
        return $tml->execute($context);
    }
}
