<?php

/**
 * This file is part of the bit4bit/phrelatorio library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Jovany Leandro G.C <bit4bit@riseup.net>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Phrelatorio\OpenDocument;

use PHPTAL;

/**
 * @internal
 */
class Template
{
    const PHPTAL_ENABLE_BLOCKS = ['repeat', 'condition'];
    const PHPTAL_INLINE = ['content'];

    private $doc;

    public function execute(array $context): string
    {
        $tal = PHPTAL::create();
        $tal->setSource($this->doc->asXML());

        // TODO poblamos phtal, no tiene otra manera?
        foreach($context as $k => $v) {
            $tal->{$k} = $v;
        }

        $out = (string) $tal->execute();

        return $this->sanitazeXML($out);
    }

    public static function fromString(string $xmlstr): self
    {
        return new static($xmlstr);
    }

    private function __construct(string $xmlstr) {
        $this->doc = \UXML\UXML::fromString($xmlstr);

        // namespace para PHPTAL
        $this->doc->element()->ownerDocument->createElementNs('http://tal', 'tal');
        $this->talTransform();
    }

    private function sanitazeXML(string $xml): string
    {
        // MACHETE eliminar etiquetas temporales y reformatear xml
        $out = preg_replace('|</?.*phrelatorio.*>\n*|', '', $xml);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($out);
        return $dom->saveXML();
    }

    private function extractTal($elem) {
        $href = rawurldecode($elem->element()->getAttribute('xlink:href'));
        if (preg_match('|phrelatorio://(?<talOpen>/?)(?<talAction>[^ ]+) *(?<talArgument>.*)?$|', $href, $matchs) != 1) {
            throw new \Exception("invalid href ${href}: {$elem}");
        }

        $talAction = $matchs['talAction'];
        $talArgument = @$matchs['talArgument'];
        $talOpen = @$matchs['talOpen'] != '/';
        return [$talOpen, $talAction, $talArgument];
    }
    
    private function talTransform()
    {
        $phrelatorioElements = $this->doc->getAll('//text:a[starts-with(@*["href"=local-name()], "phrelatorio://")]');

        // argumentos para uso posterior
        $beginsBlock = [];
        $blocks = []; # begin => end

        // construimos la relacion begin => end,
        // usando beginsBlock como una pila
        foreach ($phrelatorioElements as $elem) {
            [$talOpen, $talAction, $talArgument] = $this->extractTal($elem);

            // /table:table-cell/text:p/text:a
            $cell = $elem->parent()->parent();

            if (in_array($talAction, self::PHPTAL_INLINE)) {
                $inTable = $cell->element()->localName == 'table-cell';

                if ($inTable) {
                    $elem->parent()->element()->setAttribute("tal:{$talAction}", $talArgument);
                } else {
                    // invertimos xlink:a/text:a 
                    $span = $elem->get('./text:span');
                    $span->element()->setAttribute("tal:{$talAction}", $talArgument);
                    $elem->parent()->element()->insertBefore($span->element(), $elem->element());
                }
            } else if (in_array($talAction, self::PHPTAL_ENABLE_BLOCKS)) {
                if ($talOpen) {
                    \array_push($beginsBlock, [$cell, $talAction, $talArgument]);
                } else {
                    [$beginBlock, $beginTalAction, $beginTalArgument] = \array_pop($beginsBlock);
                    $endBlock = $cell;

                    if ($beginTalAction != $talAction) {
                        throw new \Exception("closed unopened ${talAction}");
                    }

                    $commonAncestor = $this->lowestCommonAncestor($beginBlock, $endBlock);
                    if ($commonAncestor === null) {
                        throw new \Exception("can't detect common parent");
                    }
                    $blocks[] = [$beginTalAction, $beginTalArgument, $commonAncestor, $beginBlock, $endBlock];
                }
            }

            // xlink:href
            $elem->remove();
        }

        if (count($beginsBlock)) {
            throw new \Exception("unbalanced blocks");
        }

        foreach($blocks as $match) {
            [$talAction, $talArgument, $commonAncestor, $beginBlock, $endBlock] = $match;
            $commonAncestorTag = $commonAncestor->element()->localName;
            $sectionToRepeat = ['table-row' => 'column', 'table' => 'row', 'spreadsheet' => 'table'][$commonAncestorTag];

            switch($sectionToRepeat) {
            case 'column':
                $phrelatorio = $this->newPhrelatorioTag($talAction, $talArgument);
                $beginBlock->parent()->element()->insertBefore($phrelatorio->element(), $beginBlock->element());
                
                $this->moveBetweenToNewParent($beginBlock, $endBlock, $phrelatorio);
                $beginBlock->remove();
                $endBlock->remove();
                break;
            case 'row':
                $phrelatorio = $this->newPhrelatorioTag($talAction, $talArgument);
                $beginBlock->get('./ancestor::table:table')->element()->insertBefore($phrelatorio->element(), $beginBlock->get('./ancestor::table:table-row')->element());

                $beginTableRow = $beginBlock->get('./ancestor::table:table-row');
                $endTableRow = $endBlock->get('./ancestor::table:table-row');
                $this->moveBetweenToNewParent($beginTableRow, $endTableRow, $phrelatorio);

                $beginTableRow->remove();
                $endTableRow->remove();
                break;
            case 'table':
                $phrelatorio = $this->newPhrelatorioTag($talAction, $talArgument);
                // self/table-row/table/spreadsheet
                $beginBlock->get('./ancestor::office:spreadsheet')->element()->insertBefore($phrelatorio->element(), $beginBlock->get('./ancestor::table:table')->element());

                $beginWrapBlock = $beginBlock->get('./ancestor::table:table');
                $endWrapBlock = $endBlock->get('./ancestor::table:table');
                $this->moveBetweenToNewParent($beginWrapBlock, $endWrapBlock, $phrelatorio);

                $beginWrapBlock->remove();
                $endWrapBlock->remove();
                break;
            default:
                throw new \Exception("not known how to repeat for {$commonAncestor}");
            }
        }
    }

    private function newPhrelatorioTag($talAction, $talArgument): \UXML\UXML
    {
        $phrelatorio = \UXML\UXML::newInstance('tal:phrelatorio', null, [], $this->doc->element()->ownerDocument);
        $phrelatorio->element()->setAttribute("tal:{$talAction}", trim($talArgument,'"'));
        return $phrelatorio;
    }
    
    private function moveBetweenToNewParent($begin, $end, $parent): void
    {
        $beginDom = $begin->element();
        $endDom = $end->element();
        $parentDom = $parent->element();

        $fromBeginNextSibling = $beginDom->nextSibling;
        while( $fromBeginNextSibling !== null && spl_object_hash($fromBeginNextSibling) != spl_object_hash($endDom)) {
            $parentDom->appendChild($fromBeginNextSibling);
            $fromBeginNextSibling = $beginDom->nextSibling;
        }
    }

    private function lowestCommonAncestor($beginFor, $endFor) {
        // algoritmo extraido de relatorio
        $parentBeginFor = $beginFor->parent();
        $parentEndFor = $endFor->parent();
        $commonAncestor = null;
        
        $levelsBeforeGiveOut = 4;
        while($levelsBeforeGiveOut -= 1 > 0) {
            // ubicamos el pariente en comun
            $idParentBegin = spl_object_hash($parentBeginFor);
            $idParentEnd = spl_object_hash($parentEndFor);
            if ($idParentBegin == $idParentEnd) {
                $commonAncestor = $parentBeginFor;
                break;
            }
            
            $parentBeginFor = $parentBeginFor->parent();
            $parentEndFor = $parentEndFor->parent();
        }

        return $commonAncestor;
    }
}
