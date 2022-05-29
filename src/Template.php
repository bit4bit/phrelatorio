<?php

namespace Phrelatorio;

use PHPTAL;

class Template
{
    private $doc;

    public function execute($context): string
    {
        $tal = PHPTAL::create();
        $tal->setSource($this->doc->asXML());

        foreach($context as $k => $v) {
            $tal->{$k} = $v;
        }
        
        return (string) $tal->execute();
    }

    private function transform(): void
    {
        $elements = $this->doc->getAll('//text:a[starts-with(@*["href"=local-name()], "phrelatorio:")]');

        $this->talTransform();
    }

    private function extractTal($elem) {
        $href = urldecode($elem->element()->getAttribute('xlink:href'));
        if (preg_match('|phrelatorio://(?<talAction>[^ ]+) *(?<talArgument>.*)?$|', $href, $matchs) != 1) {
            throw new \Exception("invalid href ${href}: {$elem}");
        }
        $talAction = $matchs['talAction'];
        $talArgument = @$matchs['talArgument'];

        return [$talAction, $talArgument];
    }
    
    private function talTransform()
    {
        $elements = $this->doc->getAll('//text:a[starts-with(@*["href"=local-name()], "phrelatorio://")]');

        // argumentos para uso posterior
        $talArguments = [];
        $beginsFor = [];
        $endsFor = [];
        foreach ($elements as $elem) {
            [$talAction, $talArgument] = $this->extractTal($elem);
            
            $parent = $elem->parent()->parent();
            $talArguments[spl_object_hash($parent)] = $talArgument;
            
            switch($talAction) {
                
            case 'content':
                // a/p/container
                $elem->parent()->parent()->element()->setAttribute('tal:content', $talArgument);
                break;
            case 'for':
                $beginsFor[] = $parent;
                break;
            case '/for':
                $endsFor[] = $parent;
                break;
            }

            $elem->parent()->remove();
        }

        // </a>..<a>
        foreach($endsFor as $endFor) {
            $beginFor = array_pop($beginsFor);

            // algoritmo extraido de relatorio
            $parentBeginFor = $beginFor->parent();
            $parentEndFor = $endFor->parent();
            $parentCommon = null;
            
            $levelsBeforeException = 5;
            while($levelsBeforeException -= 1 > 0) {
                // ubicamos el pariente en comun
                $idParentBegin = spl_object_hash($parentBeginFor);
                $idParentEnd = spl_object_hash($parentEndFor);
                if ($idParentBegin == $idParentEnd) {
                    $parentCommon = $parentBeginFor;
                    break;
                }

                $parentBeginFor = $beginFor->parent();
                $parentEndFor = $endFor->parent();
            }
            if ($parentCommon === null) {
                throw new \Exception("fails to found a common ancestor for ${beginFor} and ${endFor}");
            }

            // TODO adicionar tag para repetir elementos
            $parentCommonTag = $parentCommon->element()->localName;
            $talArgument = $talArguments[spl_object_hash($beginFor)];
            if (!in_array($parentCommonTag, ['table-row', 'table', 'spreadsheet'])) {
                throw new \Exception("not known how to repeat for {$parentCommon}");
            }
            $parentCommon->element()->setAttribute('tal:repeat', trim($talArgument,'"'));
            $beginFor->remove();
            $endFor->remove();
        }
    }
    
    public function __construct(string $xmlstr) {
        $this->doc = \UXML\UXML::fromString($xmlstr);
        $this->doc->element()->ownerDocument->createElementNs('http://tal', 'tal');
        $this->transform();
    }
    
    public static function fromString(string $xmlstr)
    {
        return new self($xmlstr);
    }

    
}
