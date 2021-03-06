<?php

/*
 * This file is part of the ePub Reader package
 *
 * (c) Justin Rainbow <justin.rainbow@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vibby\EPub\Resource;

use SimpleXMLElement;
use Vibby\EPub\NamespaceRegistry;
use Vibby\EPub\Definition\Package;
use Vibby\EPub\Definition\Metadata;
use Vibby\EPub\Definition\MetadataItem;
use Vibby\EPub\Definition\Manifest;
use Vibby\EPub\Definition\ManifestItem;
use Vibby\EPub\Definition\Spine;
use Vibby\EPub\Definition\SpineItem;
use Vibby\EPub\Definition\Guide;
use Vibby\EPub\Definition\GuideItem;
use Vibby\EPub\Definition\Navigation;
use Vibby\EPub\Exception\InvalidArgumentException;

class OpfResource
{
    /**
     * @var \SimpleXMLElement
     */
    private $xml;

    /**
     * Array of XML namespaces found in document
     *
     * @var array
     */
    private $namespaces;

    /**
     * Constructor
     *
     * @param \SimpleXMLElement|string $data
     * @param ZipFileResource $resource
     * @throws InvalidArgumentException
     */
    public function __construct($data, ZipFileResource $resource = null)
    {
        if ($data instanceof SimpleXMLElement) {
            $this->xml = $data;
        } elseif (is_string($data)) {
            $this->xml = new SimpleXMLElement($data);
        } else {
            throw new InvalidArgumentException(sprintf('Invalid data type for OpfResource'));
        }

        $this->resource = $resource;

        $this->namespaces = $this->xml->getNamespaces(true);
    }

    /**
     * Processes the XML data and puts the data into a Package object
     *
     * @param Package $package
     *
     * @return Package
     */
    public function bind(Package $package = null): Package
    {
        $package = $package ?: new Package();
        $xml     = $this->xml;

        // Epub version:
        $package->version = (string) $xml['version'];
        
        $this->processMetadataElement($xml->metadata, $package->metadata);
        $this->processManifestElement($xml->manifest, $package->manifest);
        $this->processSpineElement($xml->spine, $package->spine, $package->manifest, $package->navigation);

        if ($xml->guide) {
            $this->processGuideElement($xml->guide, $package->guide);
        }

        return $package;
    }

    private function processMetadataElement(SimpleXMLElement $xml, Metadata $metadata)
    {
        foreach ($xml->children(NamespaceRegistry::NAMESPACE_DC) as $child) {
            $item = new MetadataItem();

            $item->name = $child->getName();
            $item->value = trim((string) $child);
            $item->attributes = $this->getXmlAttributes($child);

            $metadata->add($item);
        }
    }

    private function processManifestElement(SimpleXmlElement $xml, Manifest $manifest)
    {
        foreach ($xml->item as $child) {
            $item = new ManifestItem();

            $item->id       = (string) $child['id'];
            $item->href     = (string) $child['href'];
            $item->type     = (string) $child['media-type'];
            $item->fallback = (string) $child['fallback'];

            $this->addContentGetter($item);

            $manifest->add($item);
        }
    }

    private function processSpineElement(SimpleXMLElement $xml, Spine $spine, Manifest $manifest, Navigation $navigation)
    {
        $position = 1;
        foreach ($xml->itemref as $child) {
            $id = (string) $child['idref'];
            $manifestItem = $manifest->get($id);
            if (!$linear = $child['linear']) {
                $linear = 'yes';
            }

            $item = new SpineItem();


            $item->id     = $id;
            $item->type   = $manifestItem->type;
            $item->href   = $manifestItem->href;
            $item->order  = $position;
            $item->linear = $linear;

            $this->addContentGetter($item);

            $spine->add($item);

            $position++;
        }
        
        $ncxId = ($xml['toc']) ? (string) $xml['toc'] : 'ncx';
        
        if ($manifest->has($ncxId)) {
            $navigation->src = $manifest->get($ncxId);
        }
    }

    private function processGuideElement(SimpleXMLElement $xml, Guide $guide)
    {
        foreach ($xml->reference as $child) {
            $item = new GuideItem();

            $item->title = (string) $child['title'];
            $item->type  = (string) $child['type'];
            $item->href  = (string) $child['href'];

            $this->addContentGetter($item);

            $guide->add($item);
        }
    }

    /**
     * Builds an array from XML attributes
     *
     * For instance:
     *
     *   <tag
     *       xmlns:opf="http://www.idpf.org/2007/opf"
     *       opf:file-as="Some Guy"
     *       id="name"/>
     *
     * Will become:
     *
     *   array('opf:file-as' => 'Some Guy', 'id' => 'name')
     *
     * **NOTE**: Namespaced attributes will have the namespace prefix
     *           prepended to the attribute name
     *
     * @param \SimpleXMLElement $xml The XML tag to grab attributes from
     *
     * @return array
     */
    private function getXmlAttributes($xml)
    {
        $attributes = array();
        foreach ($this->namespaces as $prefix => $namespace) {
            foreach ($xml->attributes($namespace) as $attr => $value) {
                if ($prefix !== "") {
                    $attr = "{$prefix}:{$attr}";
                }

                $attributes[$attr] = $value;
            }
        }

        return $attributes;
    }

    private function addContentGetter($item)
    {
        if (null !== $this->resource) {
            $resource = $this->resource;

            $item->setContent(function () use ($item, $resource) {
                return $resource->get($item->href);
            });
        }
    }
}
