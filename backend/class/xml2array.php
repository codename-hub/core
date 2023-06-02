<?php

namespace codename\core;

use DOMDocument;

/**
 * A class to convert XML to array in PHP
 * It returns the array which can be converted back to XML using the Array2XML script
 * It takes an XML string or a DOMDocument object as an input.
 *
 * See Array2XML: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 *
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-xml-to-array-in-php-xml2array
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.1 (07 Dec 2011)
 * Version: 0.2 (04 Mar 2012)
 *             Fixed typo 'DomDocument' to 'DOMDocument'
 *
 * Usage:
 *       $array = xml2array::createArray($xml);
 */
class xml2array
{
    /**
     * @var null|DOMDocument
     */
    private static ?DOMDocument $xml = null;
    /**
     * @var string
     */
    private static string $encoding = 'UTF-8';

    /**
     * Convert an XML to Array
     * @param $input_xml
     * @return DOMDocument|array
     * @throws \Exception
     */
    public static function &createArray($input_xml): DOMDocument|array
    {
        $xml = self::getXMLRoot();
        if (is_string($input_xml)) {
            $parsed = $xml->loadXML($input_xml);
            if (!$parsed) {
                throw new \Exception('[xml2array] Error parsing the XML string.');
            }
        } else {
            if (get_class($input_xml) != 'DOMDocument') {
                throw new \Exception('[xml2array] The input XML object should be of type: DOMDocument.');
            }
            $xml = self::$xml = $input_xml;
        }
        $array[$xml->documentElement->tagName] = self::convert($xml->documentElement);
        self::$xml = null;    // clear the xml node in the class for 2nd time use.
        return $array;
    }

    /**
     * @return DOMDocument|null
     */
    private static function getXMLRoot(): ?DOMDocument
    {
        if (empty(self::$xml)) {
            self::init();
        }
        return self::$xml;
    }

    /**
     * Initialize the root XML node [optional]
     * @param string $version
     * @param string $encoding
     * @param bool $format_output
     */
    public static function init(string $version = '1.0', string $encoding = 'UTF-8', bool $format_output = true): void
    {
        self::$encoding = $encoding;
        self::$xml = new DOMDocument($version, self::$encoding);
        self::$xml->formatOutput = $format_output;
    }

    /*
     * Get the root XML node, if there isn't one, create it.
     */

    /**
     * Convert an Array to XML
     * @param mixed $node - XML as a string or as an object of DOMDocument
     * @return mixed
     */
    private static function &convert(mixed $node): mixed
    {
        $output = [];

        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
                $output['@cdata'] = trim($node->textContent);
                break;

            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;

            case XML_ELEMENT_NODE:

                // for each child node, call the covert function recursively
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = self::convert($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;

                        // assume more nodes of same kind are coming
                        if (!isset($output[$t])) {
                            $output[$t] = [];
                        }
                        $output[$t][] = $v;
                    } elseif ($v !== '') {
                        $output = $v;
                    }
                }

                if (is_array($output)) {
                    // if only one node of its kind, assign it directly instead if array($value);
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1) {
                            $output[$t] = $v[0];
                        }
                    }
                    if (empty($output)) {
                        //for empty nodes
                        $output = '';
                    }
                }

                // loop through the attributes and collect them
                if ($node->attributes->length) {
                    $a = [];
                    foreach ($node->attributes as $attrName => $attrNode) {
                        $a[$attrName] = (string)$attrNode->value;
                    }
                    // if it's a leaf node, store the value in @value instead of directly storing it.
                    if (!is_array($output)) {
                        $output = ['@value' => $output];
                    }
                    $output['@attributes'] = $a;
                }
                break;
        }
        return $output;
    }
}
