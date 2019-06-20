<?php
namespace RobRichards\XMLSecLibs;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Exception;
use RobRichards\XMLSecLibs\Utils\XPath as XPath;

/**
 * xmlseclibs.php
 *
 * Copyright (c) 2007-2019, Robert Richards <rrichards@cdatazone.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Robert Richards nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author    Robert Richards <rrichards@cdatazone.org>
 * @copyright 2007-2019 Robert Richards <rrichards@cdatazone.org>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 */

class XMLSecurityDSig
{
    const XMLDSIGNS = 'http://www.w3.org/2000/09/xmldsig#';
    const XML_SCHEMA = 'http://www.w3.org/2001/XMLSchema';
    const XML_SCHEMA_INSTANCE = 'http://www.w3.org/2001/XMLSchema-instance';
    const XADES = 'http://uri.etsi.org/01903/v1.3.2#';

    const SHA1 = 'http://www.w3.org/2000/09/xmldsig#sha1';
    const SHA256 = 'http://www.w3.org/2001/04/xmlenc#sha256';
    const SHA384 = 'http://www.w3.org/2001/04/xmldsig-more#sha384';
    const SHA512 = 'http://www.w3.org/2001/04/xmlenc#sha512';
    const RIPEMD160 = 'http://www.w3.org/2001/04/xmlenc#ripemd160';

    const C14N = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
    const C14N_COMMENTS = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments';
    const EXC_C14N = 'http://www.w3.org/2001/10/xml-exc-c14n#';
    const EXC_C14N_COMMENTS = 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments';

    const template = '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Id="@"><ds:SignedInfo><ds:SignatureMethod /></ds:SignedInfo></ds:Signature>';
    const BASE_TEMPLATE = '<Signature xmlns="http://www.w3.org/2000/09/xmldsig#" Id="@"><SignedInfo><SignatureMethod /></SignedInfo></Signature>';

    private $signPolicy = [];

    /** @var string|null */
    public $signatureId;

    /** @var string|null */
    public $signatureValue;

    /** @var string|null */
    public $xadesObjectId;

    /** @var string|null */
    public $keyInfoId;

    /** @var string|null */
    public $reference0Id;

    /** @var string|null */
    public $reference1Id;

    /** @var string|null */
    public $signedProperties;

    /** @var string|null */
    public $qualifyingProperties;

    /** @var DOMElement|null */
    public $xmlFirstChild = null;

    /** @var DOMElement|null */
    public $sigNode = null;

    /** @var array */
    public $idKeys = array();

    /** @var array */
    public $idNS = array();

    /** @var string|null */
    private $signedInfo = null;

    /** @var DomXPath|null */
    private $xPathCtx = null;

    /** @var string|null */
    private $canonicalMethod = null;

    /** @var string */
    private $prefix = '';

    /** @var string */
    private $searchpfx = 'secdsig';

    /**
     * This variable contains an associative array of validated nodes.
     * @var array|null
     */
    private $validatedNodes = null;

    /**
     * @param string $prefix
     */
    public function __construct($prefix='ds')
    {
        $this->signatureId = $this->generateGUID('Signature-');
        $this->signatureValue = $this->generateGUID('SignatureValue-');
        $this->xadesObjectId = $this->generateGUID('XadesObjectId-');
        $this->keyInfoId = "KeyInfoId-" . $this->signatureId;
        $this->reference0Id = $this->generateGUID('Reference-');
        $this->reference1Id = "ReferenceKeyInfo";
        $this->signedProperties = "SignedProperties-" . $this->signatureId;
        $this->qualifyingProperties = $this->generateGUID('QualifyingProperties-');
        //$this->signPolicy['digest'] = base64_encode(hash_file('sha1',$this->signPolicy['url'],true));

        $template = self::BASE_TEMPLATE;
        if (! empty($prefix)) {
            $this->prefix = $prefix.':';
            $search = array("<S", "</S", "xmlns=", "@");
            $replace = array("<$prefix:S", "</$prefix:S", "xmlns:$prefix=", $this->signatureId);
            $template = str_replace($search, $replace, $template);
        }
        $sigdoc = new DOMDocument();
        $sigdoc->loadXML($template);
        $this->sigNode = $sigdoc->documentElement;
    }

    /**
     * Reset the XPathObj to null
     */
    private function resetXPathObj()
    {
        $this->xPathCtx = null;
    }

    /**
     * Returns the XPathObj or null if xPathCtx is set and sigNode is empty.
     *
     * @return DOMXPath|null
     */
    private function getXPathObj()
    {
        if (empty($this->xPathCtx) && ! empty($this->sigNode)) {
            $xpath = new DOMXPath($this->sigNode->ownerDocument);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
            $this->xPathCtx = $xpath;
        }
        return $this->xPathCtx;
    }

    /**
     * Generate guid
     *
     * @param string $prefix Prefix to use for guid. defaults to pfx
     *
     * @return string The generated guid
     */
    public static function generateGUID($prefix='pfx')
    {
        $uuid = md5(uniqid(mt_rand(), true));
        $guid = $prefix.substr($uuid, 0, 8)."-".
                substr($uuid, 8, 4)."-".
                substr($uuid, 12, 4)."-".
                substr($uuid, 16, 4)."-".
                substr($uuid, 20, 12);
        return $guid;
    }

    /**
     * Generate guid
     *
     * @param string $prefix Prefix to use for guid. defaults to pfx
     *
     * @return string The generated guid
     *
     * @deprecated Method deprecated in Release 1.4.1
     */
    public static function generate_GUID($prefix='pfx')
    {
        return self::generateGUID($prefix);
    }

    /**
     * @param DOMDocument $objDoc
     * @param int $pos
     * @return DOMNode|null
     */
    public function locateSignature($objDoc, $pos=0)
    {
        if ($objDoc instanceof DOMDocument) {
            $doc = $objDoc;
        } else {
            $doc = $objDoc->ownerDocument;
        }
        if ($doc) {
            $xpath = new DOMXPath($doc);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
            $query = ".//secdsig:Signature";
            $nodeset = $xpath->query($query, $objDoc);
            $this->sigNode = $nodeset->item($pos);
            return $this->sigNode;
        }
        return null;
    }

    /**
     * @param string $name
     * @param null|string $value
     * @return DOMElement
     */
    public function createNewSignNode($name, $value=null)
    {
        $doc = $this->sigNode->ownerDocument;
        if (! is_null($value)) {
            $node = $doc->createElementNS(self::XMLDSIGNS, $this->prefix.$name, $value);
        } else {
            $node = $doc->createElementNS(self::XMLDSIGNS, $this->prefix.$name);
        }
        return $node;
    }

    /**
     * @param string $method
     * @throws Exception
     */
    public function setCanonicalMethod($method)
    {
        switch ($method) {
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
            case 'http://www.w3.org/2001/10/xml-exc-c14n#':
            case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
                $this->canonicalMethod = $method;
                break;
            default:
                throw new Exception('Invalid Canonical Method');
        }
        if ($xpath = $this->getXPathObj()) {
            $query = './'.$this->searchpfx.':SignedInfo';
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($sinfo = $nodeset->item(0)) {
                $query = './'.$this->searchpfx.'CanonicalizationMethod';
                $nodeset = $xpath->query($query, $sinfo);
                if (! ($canonNode = $nodeset->item(0))) {
                    $canonNode = $this->createNewSignNode('CanonicalizationMethod');
                    $sinfo->insertBefore($canonNode, $sinfo->firstChild);
                }
                $canonNode->setAttribute('Algorithm', $this->canonicalMethod);
            }
        }
    }

    public function setSignPolicy(){
        $xmlns = $this->xmlFirstChild->getAttribute('xmlns');
        switch ($xmlns){
            case (strpos($xmlns, 'v4.2') !== false):
                $this->signPolicy = [
                    "name" 		=> "",
                    "url" 		=> "https://tribunet.hacienda.go.cr/docs/esquemas/2016/v4.2/ResolucionComprobantesElectronicosDGT-R-48-2016_4.2.pdf",
                    "digest" 	=> "3gQCr0HYSdoxi0ZaRaJ4qs3mHfI=" // Base64_Encode(Hash_File(SHA_1))
                ];
                break;
            case (strpos($xmlns, 'v4.3') !== false):
                $this->signPolicy = [
                    "name" 		=> "",
                    "url" 		=> "https://www.hacienda.go.cr/ATV/ComprobanteElectronico/docs/esquemas/2016/v4.3/ResolucionComprobantesElectronicosDGT-R-48-2016_4.3.pdf",
                    "digest" 	=> "3gQCr0HYSdoxi0ZaRaJ4qs3mHfI=" // Base64_Encode(Hash_File(SHA_1))
                ];
                break;
            default:
                throw new Exception("Cannot validate version: Unsupported Version");
        }
    }

    /**
     * @param DOMNode $node
     * @param string $canonicalmethod
     * @param null|array $arXPath
     * @param null|array $prefixList
     * @return string
     */
    private function canonicalizeData($node, $canonicalmethod, $arXPath=null, $prefixList=null)
    {
        $exclusive = false;
        $withComments = false;
        switch ($canonicalmethod) {
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
                $exclusive = false;
                $withComments = false;
                break;
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
                $withComments = true;
                break;
            case 'http://www.w3.org/2001/10/xml-exc-c14n#':
                $exclusive = true;
                break;
            case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
                $exclusive = true;
                $withComments = true;
                break;
        }

        if (is_null($arXPath) && ($node instanceof DOMNode) && ($node->ownerDocument !== null) && $node->isSameNode($node->ownerDocument->documentElement)) {
            /* Check for any PI or comments as they would have been excluded */
            $element = $node;
            while ($refnode = $element->previousSibling) {
                if ($refnode->nodeType == XML_PI_NODE || (($refnode->nodeType == XML_COMMENT_NODE) && $withComments)) {
                    break;
                }
                $element = $refnode;
            }
            if ($refnode == null) {
                $node = $node->ownerDocument;
            }
        }

        return $node->C14N($exclusive, $withComments, $arXPath, $prefixList);
    }

    /**
     * @return null|string
     */
    public function canonicalizeSignedInfo()
    {

        $doc = $this->sigNode->ownerDocument;
        $canonicalmethod = null;
        if ($doc) {
            $xpath = $this->getXPathObj();
            $query = "./secdsig:SignedInfo";
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($signInfoNode = $nodeset->item(0)) {
                $query = "./secdsig:CanonicalizationMethod";
                $nodeset = $xpath->query($query, $signInfoNode);
                if ($canonNode = $nodeset->item(0)) {
                    $canonicalmethod = $canonNode->getAttribute('Algorithm');
                }
                $this->signedInfo = $this->canonicalizeData($signInfoNode, $canonicalmethod);
                return $this->signedInfo;
            }
        }
        return null;
    }

    /**
     * @param string $digestAlgorithm
     * @param string $data
     * @param bool $encode
     * @return string
     * @throws Exception
     */
    public function calculateDigest($digestAlgorithm, $data, $encode = true)
    {
        switch ($digestAlgorithm) {
            case self::SHA1:
                $alg = 'sha1';
                break;
            case self::SHA256:
                $alg = 'sha256';
                break;
            case self::SHA384:
                $alg = 'sha384';
                break;
            case self::SHA512:
                $alg = 'sha512';
                break;
            case self::RIPEMD160:
                $alg = 'ripemd160';
                break;
            default:
                throw new Exception("Cannot validate digest: Unsupported Algorithm <$digestAlgorithm>");
        }

        $digest = hash($alg, $data, true);
        if ($encode) {
            $digest = base64_encode($digest);
        }
        return $digest;

    }

    /**
     * @param $refNode
     * @param string $data
     * @return bool
     */
    public function validateDigest($refNode, $data)
    {
        $xpath = new DOMXPath($refNode->ownerDocument);
        $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
        $query = 'string(./secdsig:DigestMethod/@Algorithm)';
        $digestAlgorithm = $xpath->evaluate($query, $refNode);
        $digValue = $this->calculateDigest($digestAlgorithm, $data, false);
        $query = 'string(./secdsig:DigestValue)';
        $digestValue = $xpath->evaluate($query, $refNode);
        return ($digValue === base64_decode($digestValue));
    }

    /**
     * @param $refNode
     * @param DOMNode $objData
     * @param bool $includeCommentNodes
     * @return string
     */
    public function processTransforms($refNode, $objData, $includeCommentNodes = true)
    {
        $data = $objData;
        $xpath = new DOMXPath($refNode->ownerDocument);
        $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
        $query = './secdsig:Transforms/secdsig:Transform';
        $nodelist = $xpath->query($query, $refNode);
        $canonicalMethod = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
        $arXPath = null;
        $prefixList = null;
        foreach ($nodelist AS $transform) {
            $algorithm = $transform->getAttribute("Algorithm");
            switch ($algorithm) {
                case 'http://www.w3.org/2001/10/xml-exc-c14n#':
                case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':

                    if (!$includeCommentNodes) {
                        /* We remove comment nodes by forcing it to use a canonicalization
                         * without comments.
                         */
                        $canonicalMethod = 'http://www.w3.org/2001/10/xml-exc-c14n#';
                    } else {
                        $canonicalMethod = $algorithm;
                    }

                    $node = $transform->firstChild;
                    while ($node) {
                        if ($node->localName == 'InclusiveNamespaces') {
                            if ($pfx = $node->getAttribute('PrefixList')) {
                                $arpfx = array();
                                $pfxlist = explode(" ", $pfx);
                                foreach ($pfxlist AS $pfx) {
                                    $val = trim($pfx);
                                    if (! empty($val)) {
                                        $arpfx[] = $val;
                                    }
                                }
                                if (count($arpfx) > 0) {
                                    $prefixList = $arpfx;
                                }
                            }
                            break;
                        }
                        $node = $node->nextSibling;
                    }
            break;
                case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
                case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
                    if (!$includeCommentNodes) {
                        /* We remove comment nodes by forcing it to use a canonicalization
                         * without comments.
                         */
                        $canonicalMethod = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
                    } else {
                        $canonicalMethod = $algorithm;
                    }

                    break;
                case 'http://www.w3.org/TR/1999/REC-xpath-19991116':
                    $node = $transform->firstChild;
                    while ($node) {
                        if ($node->localName == 'XPath') {
                            $arXPath = array();
                            $arXPath['query'] = '(.//. | .//@* | .//namespace::*)['.$node->nodeValue.']';
                            $arXpath['namespaces'] = array();
                            $nslist = $xpath->query('./namespace::*', $node);
                            foreach ($nslist AS $nsnode) {
                                if ($nsnode->localName != "xml") {
                                    $arXPath['namespaces'][$nsnode->localName] = $nsnode->nodeValue;
                                }
                            }
                            break;
                        }
                        $node = $node->nextSibling;
                    }
                    break;
            }
        }
        if ($data instanceof DOMNode) {
            $data = $this->canonicalizeData($objData, $canonicalMethod, $arXPath, $prefixList);
        }
        return $data;
    }

    /**
     * @param DOMNode $refNode
     * @return bool
     */
    public function processRefNode($refNode)
    {
        $dataObject = null;

        /*
         * Depending on the URI, we may not want to include comments in the result
         * See: http://www.w3.org/TR/xmldsig-core/#sec-ReferenceProcessingModel
         */
        $includeCommentNodes = true;

        if ($uri = $refNode->getAttribute("URI")) {
            $arUrl = parse_url($uri);
            if (empty($arUrl['path'])) {
                if ($identifier = $arUrl['fragment']) {

                    /* This reference identifies a node with the given id by using
                     * a URI on the form "#identifier". This should not include comments.
                     */
                    $includeCommentNodes = false;

                    $xPath = new DOMXPath($refNode->ownerDocument);
                    if ($this->idNS && is_array($this->idNS)) {
                        foreach ($this->idNS as $nspf => $ns) {
                            $xPath->registerNamespace($nspf, $ns);
                        }
                    }
                    $iDlist = '@Id="'.XPath::filterAttrValue($identifier, XPath::DOUBLE_QUOTE).'"';
                    if (is_array($this->idKeys)) {
                        foreach ($this->idKeys as $idKey) {
                            $iDlist .= " or @".XPath::filterAttrName($idKey).'="'.
                                XPath::filterAttrValue($identifier, XPath::DOUBLE_QUOTE).'"';
                        }
                    }
                    $query = '//*['.$iDlist.']';
                    $dataObject = $xPath->query($query)->item(0);
                } else {
                    $dataObject = $refNode->ownerDocument;
                }
            }
        } else {
            /* This reference identifies the root node with an empty URI. This should
             * not include comments.
             */
            $includeCommentNodes = false;

            $dataObject = $refNode->ownerDocument;
        }
        $data = $this->processTransforms($refNode, $dataObject, $includeCommentNodes);
        if (!$this->validateDigest($refNode, $data)) {
            return false;
        }

        if ($dataObject instanceof DOMNode) {
            /* Add this node to the list of validated nodes. */
            if (! empty($identifier)) {
                $this->validatedNodes[$identifier] = $dataObject;
            } else {
                $this->validatedNodes[] = $dataObject;
            }
        }

        return true;
    }

    /**
     * @param DOMNode $refNode
     * @return null
     */
    public function getRefNodeID($refNode)
    {
        if ($uri = $refNode->getAttribute("URI")) {
            $arUrl = parse_url($uri);
            if (empty($arUrl['path'])) {
                if ($identifier = $arUrl['fragment']) {
                    return $identifier;
                }
            }
        }
        return null;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRefIDs()
    {
        $refids = array();

        $xpath = $this->getXPathObj();
        $query = "./secdsig:SignedInfo/secdsig:Reference";
        $nodeset = $xpath->query($query, $this->sigNode);
        if ($nodeset->length == 0) {
            throw new Exception("Reference nodes not found");
        }
        foreach ($nodeset AS $refNode) {
            $refids[] = $this->getRefNodeID($refNode);
        }
        return $refids;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function validateReference()
    {
        $docElem = $this->sigNode->ownerDocument->documentElement;
        if (! $docElem->isSameNode($this->sigNode)) {
            if ($this->sigNode->parentNode != null) {
                $this->sigNode->parentNode->removeChild($this->sigNode);
            }
        }
        $xpath = $this->getXPathObj();
        $query = "./secdsig:SignedInfo/secdsig:Reference";
        $nodeset = $xpath->query($query, $this->sigNode);
        if ($nodeset->length == 0) {
            throw new Exception("Reference nodes not found");
        }

        /* Initialize/reset the list of validated nodes. */
        $this->validatedNodes = array();

        foreach ($nodeset AS $refNode) {
            if (! $this->processRefNode($refNode)) {
                /* Clear the list of validated nodes. */
                $this->validatedNodes = null;
                throw new Exception("Reference validation failed");
            }
        }
        return true;
    }

    /**
     * @param DOMNode $sinfoNode
     * @param DOMDocument $node
     * @param string $algorithm
     * @param null|array $arTransforms
     * @param null|array $options
     */
    private function addRefInternal($sinfoNode, $node, $algorithm, $arTransforms=null, $options=null, $namespaces=null)
    {
        $canonicalNode = $node;
        $prefix = null;
        $prefix_ns = null;
        $id_ref = null;
        $id_name = 'Id';
        $overwrite_id  = true;
        $force_uri = false;
        $type = null;

        if (is_array($options)) {
            $prefix = empty($options['prefix']) ? null : $options['prefix'];
            $prefix_ns = empty($options['prefix_ns']) ? null : $options['prefix_ns'];
            $id_ref = empty($options['id_ref']) ? null : $options['id_ref'];
            $id_name = empty($options['id_name']) ? 'Id' : $options['id_name'];
            $overwrite_id = !isset($options['overwrite']) ? true : (bool) $options['overwrite'];
            $force_uri = !isset($options['force_uri']) ? false : (bool) $options['force_uri'];
            $type = empty($options['type']) ? null : $options['type'];
        }

        $refNode = $this->createNewSignNode('Reference');
        $sinfoNode->appendChild($refNode);

        $attname = $id_name;
        if (! empty($prefix)) {
            $attname = $prefix.':'.$attname;
        }

        if (!empty($id_ref)){
            $refNode->setAttribute("Id", $id_ref);
        }

        if (! $node instanceof DOMDocument) {
            if (!empty($type)){
                $refNode->setAttribute("Type", $type);
            }
            $uri = null;
            if (! $overwrite_id) {
                $uri = $prefix_ns ? $node->getAttributeNS($prefix_ns, $id_name) : $node->getAttribute($id_name);
            }
            if (empty($uri)) {
                $uri = self::generateGUID();
                $node->setAttributeNS($prefix_ns, $attname, $uri);
            }
            $refNode->setAttribute("URI", '#'.$uri);

            $canonicalNode = new DOMDocument();
            $tempNode = $canonicalNode->importNode($node, true);

            $xmlns = $this->xmlFirstChild->getAttribute('xmlns');
            if (!empty($xmlns)){
                $tempNode->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns",$xmlns);
            }
            $tempNode->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns:ds",self::XMLDSIGNS);
            $xmlns_xsd = $this->xmlFirstChild->getAttribute('xmlns:xsd');
            if (!empty($xmlns_xsd)){
                $tempNode->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns:xsd",self::XML_SCHEMA);
            }
            $xmlns_xsi = $this->xmlFirstChild->getAttribute('xmlns:xsi');
            if (!empty($xmlns_xsi)){
                $tempNode->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns:xsi",self::XML_SCHEMA_INSTANCE);
            }
            if (is_array($namespaces)) {
                foreach($namespaces as $n){
                    $tempNode->setAttributeNS("http://www.w3.org/2000/xmlns/",$n['qualifiedName'],$n['value']);
                }
            }
            $canonicalNode->appendChild($tempNode);
        } elseif ($force_uri) {
            $refNode->setAttribute("URI", '');
        }

        if (is_array($arTransforms)) {
            $transNodes = $this->createNewSignNode('Transforms');
            $refNode->appendChild($transNodes);
            foreach ($arTransforms AS $transform) {
                $transNode = $this->createNewSignNode('Transform');
                $transNodes->appendChild($transNode);
                if (is_array($transform) &&
                    (! empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116'])) &&
                    (! empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['query']))) {
                    $transNode->setAttribute('Algorithm', 'http://www.w3.org/TR/1999/REC-xpath-19991116');
                    $XPathNode = $this->createNewSignNode('XPath', $transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['query']);
                    $transNode->appendChild($XPathNode);
                    if (! empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['namespaces'])) {
                        foreach ($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['namespaces'] AS $prefix => $namespace) {
                            $XPathNode->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:$prefix", $namespace);
                        }
                    }
                } else {
                    $transNode->setAttribute('Algorithm', $transform);
                }
            }
        }

        $canonicalData = $this->processTransforms($refNode, $canonicalNode);
        $digValue = $this->calculateDigest($algorithm, $canonicalData);
        $digestMethod = $this->createNewSignNode('DigestMethod');
        $refNode->appendChild($digestMethod);
        $digestMethod->setAttribute('Algorithm', $algorithm);

        $digestValue = $this->createNewSignNode('DigestValue', $digValue);
        $refNode->appendChild($digestValue);
    }

    /**
     * @param DOMDocument $node
     * @param string $algorithm
     * @param null|array $arTransforms
     * @param null|array $options
     */
    public function addReference($node, $algorithm, $arTransforms=null, $options=null, $namespaces=null)
    {
        if ($xpath = $this->getXPathObj()) {
            $query = "./secdsig:SignedInfo";
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($sInfo = $nodeset->item(0)) {
                $this->addRefInternal($sInfo, $node, $algorithm, $arTransforms, $options, $namespaces);
            }
        }
    }

    /**
     * @param array $arNodes
     * @param string $algorithm
     * @param null|array $arTransforms
     * @param null|array $options
     */
    public function addReferenceList($arNodes, $algorithm, $arTransforms=null, $options=null)
    {
        if ($xpath = $this->getXPathObj()) {
            $query = "./secdsig:SignedInfo";
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($sInfo = $nodeset->item(0)) {
                foreach ($arNodes AS $node) {
                    $this->addRefInternal($sInfo, $node, $algorithm, $arTransforms, $options);
                }
            }
        }
    }

    /**
     * @param DOMElement|string $data
     * @param null|string $mimetype
     * @param null|string $encoding
     * @return DOMElement
     */
    public function addObject($data, $mimetype=null, $encoding=null)
    {
        $objNode = $this->createNewSignNode('Object');
        $this->sigNode->appendChild($objNode);
        if (! empty($mimetype)) {
            $objNode->setAttribute('MimeType', $mimetype);
        }
        if (! empty($encoding)) {
            $objNode->setAttribute('Encoding', $encoding);
        }

        if ($data instanceof DOMElement) {
            $newData = $this->sigNode->ownerDocument->importNode($data, true);
        } else {
            $newData = $this->sigNode->ownerDocument->createTextNode($data);
        }
        $objNode->appendChild($newData);

        return $objNode;
    }

    /**
     * @param null|DOMNode $node
     * @return null|XMLSecurityKey
     */
    public function locateKey($node=null)
    {
        if (empty($node)) {
            $node = $this->sigNode;
        }
        if (! $node instanceof DOMNode) {
            return null;
        }
        if ($doc = $node->ownerDocument) {
            $xpath = new DOMXPath($doc);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
            $query = "string(./secdsig:SignedInfo/secdsig:SignatureMethod/@Algorithm)";
            $algorithm = $xpath->evaluate($query, $node);
            if ($algorithm) {
                try {
                    $objKey = new XMLSecurityKey($algorithm, array('type' => 'public'));
                } catch (Exception $e) {
                    return null;
                }
                return $objKey;
            }
        }
        return null;
    }

    /**
     * Returns:
     *  Bool when verifying HMAC_SHA1;
     *  Int otherwise, with following meanings:
     *    1 on succesful signature verification,
     *    0 when signature verification failed,
     *   -1 if an error occurred during processing.
     *
     * NOTE: be very careful when checking the int return value, because in
     * PHP, -1 will be cast to True when in boolean context. Always check the
     * return value in a strictly typed way, e.g. "$obj->verify(...) === 1".
     *
     * @param XMLSecurityKey $objKey
     * @return bool|int
     * @throws Exception
     */
    public function verify($objKey)
    {
        $doc = $this->sigNode->ownerDocument;
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
        $query = "string(./secdsig:SignatureValue)";
        $sigValue = $xpath->evaluate($query, $this->sigNode);
        if (empty($sigValue)) {
            throw new Exception("Unable to locate SignatureValue");
        }
        return $objKey->verifySignature($this->signedInfo, base64_decode($sigValue));
    }

    /**
     * @param XMLSecurityKey $objKey
     * @param string $data
     * @return mixed|string
     */
    public function signData($objKey, $data)
    {
        return $objKey->signData($data);
    }

    /**
     * @param XMLSecurityKey $objKey
     * @param null|DOMNode $appendToNode
     */
    public function sign($objKey, $namespaces = null, $appendToNode = null)
    {
        // If we have a parent node append it now so C14N properly works
        if ($appendToNode != null) {
            $this->resetXPathObj();
            $this->appendSignature($appendToNode);
            $this->sigNode = $appendToNode->lastChild;
        }
        if ($xpath = $this->getXPathObj()) {
            $query = "./secdsig:SignedInfo";
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($sInfo = $nodeset->item(0)) {
                $query = "./secdsig:SignatureMethod";
                $nodeset = $xpath->query($query, $sInfo);
                $sMethod = $nodeset->item(0);
                $sMethod->setAttribute('Algorithm', $objKey->type);
                $canonicalNode = new DOMDocument();
                $tempNode = $canonicalNode->importNode($sInfo, true);
                $xmlns = $this->xmlFirstChild->getAttribute('xmlns');
                if (!empty($xmlns)){
                    $tempNode->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns",$xmlns);
                }
                $tempNode->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns:ds",self::XMLDSIGNS);
                $xmlns_xsd = $this->xmlFirstChild->getAttribute('xmlns:xsd');
                if (!empty($xmlns_xsd)){
                    $tempNode->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns:xsd",self::XML_SCHEMA);
                }
                $xmlns_xsi = $this->xmlFirstChild->getAttribute('xmlns:xsi');
                if (!empty($xmlns_xsi)){
                    $tempNode->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns:xsi",self::XML_SCHEMA_INSTANCE);
                }
                if (is_array($namespaces)) {
                    foreach($namespaces as $n){
                        $tempNode->setAttributeNS("http://www.w3.org/2000/xmlns/",$n['qualifiedName'],$n['value']);
                    }
                }
                $canonicalNode->appendChild($tempNode);
                $data = $this->canonicalizeData($canonicalNode, $this->canonicalMethod);
                $sigValue = base64_encode($this->signData($objKey, $data));
                $sigValueNode = $this->createNewSignNode('SignatureValue', $sigValue);
                $sigValueNode->setAttribute('Id',$this->signatureValue);
                if ($infoSibling = $sInfo->nextSibling) {
                    $infoSibling->parentNode->insertBefore($sigValueNode, $infoSibling);
                } else {
                    $this->sigNode->appendChild($sigValueNode);
                }
            }
        }
    }

    public function appendCert()
    {

    }

    /**
     * @param XMLSecurityKey $objKey
     * @param null|DOMNode $parent
     */
    public function appendKey($objKey, $parent=null)
    {
        $objKey->serializeKey($parent);
    }


    /**
     * This function inserts the signature element.
     *
     * The signature element will be appended to the element, unless $beforeNode is specified. If $beforeNode
     * is specified, the signature element will be inserted as the last element before $beforeNode.
     *
     * @param DOMNode $node       The node the signature element should be inserted into.
     * @param DOMNode $beforeNode The node the signature element should be located before.
     *
     * @return DOMNode The signature element node
     */
    public function insertSignature($node, $beforeNode = null)
    {

        $document = $node->ownerDocument;
        $signatureElement = $document->importNode($this->sigNode, true);

        if ($beforeNode == null) {
            return $node->insertBefore($signatureElement);
        } else {
            return $node->insertBefore($signatureElement, $beforeNode);
        }
    }

    /**
     * @param DOMNode $parentNode
     * @param bool $insertBefore
     * @return DOMNode
     */
    public function appendSignature($parentNode, $insertBefore = false)
    {
        $beforeNode = $insertBefore ? $parentNode->firstChild : null;
        return $this->insertSignature($parentNode, $beforeNode);
    }

    /**
     * @param string $cert
     * @param bool $isPEMFormat
     * @return string
     */
    public static function get509XCert($cert, $isPEMFormat=true)
    {
        $certs = self::staticGet509XCerts($cert, $isPEMFormat);
        if (! empty($certs)) {
            return $certs[0];
        }
        return '';
    }

    /**
     * @param string $certs
     * @param bool $isPEMFormat
     * @return array
     */
    public static function staticGet509XCerts($certs, $isPEMFormat=true)
    {
        if ($isPEMFormat) {
            $data = '';
            $certlist = array();
            $arCert = explode("\n", $certs);
            $inData = false;
            foreach ($arCert AS $curData) {
                if (! $inData) {
                    if (strncmp($curData, '-----BEGIN CERTIFICATE', 22) == 0) {
                        $inData = true;
                    }
                } else {
                    if (strncmp($curData, '-----END CERTIFICATE', 20) == 0) {
                        $inData = false;
                        $certlist[] = $data;
                        $data = '';
                        continue;
                    }
                    $data .= trim($curData);
                }
            }
            return $certlist;
        } else {
            return array($certs);
        }
    }

    /**
     * @param DOMElement $parentRef
     * @param string $cert
     * @param bool $isPEMFormat
     * @param bool $isURL
     * @param null|DOMXPath $xpath
     * @param null|array $options
     * @throws Exception
     */
    public static function staticAdd509Cert($parentRef, $cert, $isPEMFormat=true, $isURL=false, $xpath=null, $options=null)
    {
        if ($isURL) {
            $cert = file_get_contents($cert);
        }
        if (! $parentRef instanceof DOMElement) {
            throw new Exception('Invalid parent Node parameter');
        }
        $baseDoc = $parentRef->ownerDocument;

        if (empty($xpath)) {
            $xpath = new DOMXPath($parentRef->ownerDocument);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
        }

        $query = "./secdsig:KeyInfo";
        $nodeset = $xpath->query($query, $parentRef);
        $keyInfo = $nodeset->item(0);
        $dsig_pfx = '';
        if (! $keyInfo) {
            $pfx = $parentRef->lookupPrefix(self::XMLDSIGNS);
            if (! empty($pfx)) {
                $dsig_pfx = $pfx.":";
            }
            $inserted = false;
            $keyInfo = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx.'KeyInfo');
            $query = "./secdsig:Object";
            $nodeset = $xpath->query($query, $parentRef);
            if ($sObject = $nodeset->item(0)) {
                $sObject->parentNode->insertBefore($keyInfo, $sObject);
                $inserted = true;
            }

            if (! $inserted) {
                $parentRef->appendChild($keyInfo);
            }
        } else {
            $pfx = $keyInfo->lookupPrefix(self::XMLDSIGNS);
            if (! empty($pfx)) {
                $dsig_pfx = $pfx.":";
            }
        }

        // Add all certs if there are more than one
        $certs = self::staticGet509XCerts($cert, $isPEMFormat);

        // Attach X509 data node
        $x509DataNode = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx.'X509Data');
        $keyInfo->appendChild($x509DataNode);

        $issuerSerial = false;
        $subjectName = false;
        if (is_array($options)) {
            if (! empty($options['issuerSerial'])) {
                $issuerSerial = true;
            }
            if (! empty($options['subjectName'])) {
                $subjectName = true;
            }
        }

        // Attach all certificate nodes and any additional data
        foreach ($certs as $X509Cert) {
          if ($certData = openssl_x509_parse("-----BEGIN CERTIFICATE-----\n".chunk_split($X509Cert, 64, "\n")."-----END CERTIFICATE-----\n")) {
            if ($issuerSerial || $subjectName) {
                    if ($subjectName && ! empty($certData['subject'])) {
                        if (is_array($certData['subject'])) {
                            $parts = array();
                            foreach ($certData['subject'] AS $key => $value) {
                                if (is_array($value)) {
                                    foreach ($value as $valueElement) {
                                        array_unshift($parts, "$key=$valueElement");
                                    }
                                } else {
                                    array_unshift($parts, "$key=$value");
                                }
                            }
                            $subjectNameValue = implode(',', $parts);
                        } else {
                            $subjectNameValue = $certData['issuer'];
                        }
                        $x509SubjectNode = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx.'X509SubjectName', $subjectNameValue);
                        $x509DataNode->appendChild($x509SubjectNode);
                    }
                    if ($issuerSerial && ! empty($certData['issuer']) && ! empty($certData['serialNumber'])) {
                        if (is_array($certData['issuer'])) {
                            $parts = array();
                            foreach ($certData['issuer'] AS $key => $value) {
                                array_unshift($parts, "$key=$value");
                            }
                            $issuerName = implode(',', $parts);
                        } else {
                            $issuerName = $certData['issuer'];
                        }

                        $x509IssuerNode = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx.'X509IssuerSerial');
                        $x509DataNode->appendChild($x509IssuerNode);

                        $x509Node = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx.'X509IssuerName', $issuerName);
                        $x509IssuerNode->appendChild($x509Node);
                        $x509Node = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx.'X509SerialNumber', $certData['serialNumber']);
                        $x509IssuerNode->appendChild($x509Node);
                    }
                }

                if ($certData["validTo_time_t"] <= time()){
                  throw new Exception("One of the certificates is expired! Please use a valid certificate and try again.");
                }

                $x509CertNode = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx.'X509Certificate', $X509Cert);
                $x509DataNode->appendChild($x509CertNode);
            }
        }
    }

    /**
     * @param string $cert
     * @param bool $isPEMFormat
     * @param bool $isURL
     * @param null|array $options
     */
    public function add509Cert($cert, $isPEMFormat=true, $isURL=false, $options=null)
    {
        if ($xpath = $this->getXPathObj()) {
            try {
                self::staticAdd509Cert($this->sigNode, $cert, $isPEMFormat, $isURL, $xpath, $options);
            } catch (Exception $ex){
                die($ex->getMessage());
            }
        }
    }

    /**
     * This function appends a node to the KeyInfo.
     *
     * The KeyInfo element will be created if one does not exist in the document.
     *
     * @param DOMNode $node The node to append to the KeyInfo.
     *
     * @return DOMNode The KeyInfo element node
     */
    public function appendToKeyInfo($node)
    {
        $parentRef = $this->sigNode;
        $baseDoc = $parentRef->ownerDocument;

        $xpath = $this->getXPathObj();
        if (empty($xpath)) {
            $xpath = new DOMXPath($parentRef->ownerDocument);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
        }

        $query = "./secdsig:KeyInfo";
        $nodeset = $xpath->query($query, $parentRef);
        $keyInfo = $nodeset->item(0);
        if (! $keyInfo) {
            $dsig_pfx = '';
            $pfx = $parentRef->lookupPrefix(self::XMLDSIGNS);
            if (! empty($pfx)) {
                $dsig_pfx = $pfx.":";
            }
            $inserted = false;
            $keyInfo = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx.'KeyInfo');

            $query = "./secdsig:Object";
            $nodeset = $xpath->query($query, $parentRef);
            if ($sObject = $nodeset->item(0)) {
                $sObject->parentNode->insertBefore($keyInfo, $sObject);
                $inserted = true;
            }

            if (! $inserted) {
                $parentRef->appendChild($keyInfo);
            }
        }

        $keyInfo->appendChild($node);

        return $keyInfo;
    }

    /**
     * This function retrieves an associative array of the validated nodes.
     *
     * The array will contain the id of the referenced node as the key and the node itself
     * as the value.
     *
     * Returns:
     *  An associative array of validated nodes or null if no nodes have been validated.
     *
     *  @return array Associative array of validated nodes
     */
    public function getValidatedNodes()
    {
        return $this->validatedNodes;
    }

    public function getKeyInfoNode(){
        if ($xpath = $this->getXPathObj()) {
            $query = "./secdsig:KeyInfo";
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($sInfo = $nodeset->item(0)) {
                return $sInfo;
            }
        }
        return null;
    }

    public function createNewXadesNode($name, $value=null, $options=null )
    {
        $doc = $this->sigNode->ownerDocument;
        if (!is_null($value)) {
            $node = $doc->createElement('xades:'.$name, $value);
        } else {
            $node = $doc->createElement('xades:'.$name);
        }
        if (is_array($options)){
            foreach ($options as $key =>  $option){
                $node->setAttribute($key, $option);
            }
        }
        return $node;
    }

    public function createNewXadesNodeNS($name, $value=null, $options=null)
    {
        $doc = $this->sigNode->ownerDocument;
        if (!is_null($value)) {
            $node = $doc->createElementNS(self::XADES,'xades:'.$name, $value);
        } else {
            $node = $doc->createElementNS(self::XADES,'xades:'.$name);
        }
        if (is_array($options)){
            foreach ($options as $key =>  $option){
                $node->setAttribute($key, $option);
            }
        }
        return $node;
    }

    public function getXadesNode(){
        $nodeset = $this->sigNode->getElementsByTagName('xades:SignedProperties');
        if ($xInfo = $nodeset->item(0)) {
            return $xInfo;
        }
        return null;
    }

    public function loadCertInfo($pfx,$pin){
        $certInfo = [];
        if (!$pfx = file_get_contents($pfx)) {
            return null;
        }
        if (openssl_pkcs12_read($pfx, $key, $pin)) {
            $certInfo["publicKey"] = $key["cert"];
            $certInfo["privateKey"] = $key["pkey"];
            $keyGet = openssl_pkey_get_private($key["pkey"]);
            $keyComplem = openssl_pkey_get_details($keyGet);
            $certInfo["Modulus"] = base64_encode($keyComplem['rsa']['n']);
            $certInfo["Exponent"] = base64_encode($keyComplem['rsa']['e']);
        } else {
            return null;
        }
        return $certInfo;
    }

    public function appendXades($certInfo){
        $objectNode = $this->createNewSignNode('Object');
        $this->sigNode->appendChild($objectNode);
        $objectNode->setAttribute('Id', $this->xadesObjectId);
        $qualifyingPropertiesNode = $this->createNewXadesNode('QualifyingProperties', null,
            [ "Id" => $this->qualifyingProperties, "Target" => "#".$this->signatureId ]
        );
        $qualifyingPropertiesNode->setAttributeNS("http://www.w3.org/2000/xmlns/","xmlns:xades",self::XADES);
        $objectNode->appendChild($qualifyingPropertiesNode);
        $signedPropertiesNode = $this->createNewXadesNode('SignedProperties', null, [ "Id" => $this->signedProperties ]);
        $qualifyingPropertiesNode->appendChild($signedPropertiesNode);
        $signedSignaturePropertiesNode = $this->createNewXadesNode('SignedSignatureProperties');
        $signedPropertiesNode->appendChild($signedSignaturePropertiesNode);
        $signingTimeNode = $this->createNewXadesNode('SigningTime', date('Y-m-d\TH:i:s-06:00'));
        $signedSignaturePropertiesNode->appendChild($signingTimeNode);
        $signingCertificateNode = $this->createNewXadesNode('SigningCertificate');
        $signedSignaturePropertiesNode->appendChild($signingCertificateNode);
        $certNode = $this->createNewXadesNode('Cert');
        $signingCertificateNode->appendChild($certNode);
        $certDigestNode = $this->createNewXadesNode('CertDigest');
        $certNode->appendChild($certDigestNode);
        $issuerSerialNode = $this->createNewXadesNode('IssuerSerial');
        $certNode->appendChild($issuerSerialNode);
        $digestMethodNode = $this->createNewSignNode('DigestMethod');
        $certDigestNode->appendChild($digestMethodNode);
        $digestMethodNode->setAttribute('Algorithm', $this::SHA256);
        $digestValue = base64_encode(openssl_x509_fingerprint($certInfo["publicKey"], "sha256", true));
        $digestValueNode = $this->createNewSignNode('DigestValue', $digestValue);
        $certDigestNode->appendChild($digestValueNode);
        $certData = openssl_x509_parse($certInfo["publicKey"]);
        $certIssuer = [];
        foreach ($certData['issuer'] as $item => $value) {
            $certIssuer[] = $item . '=' . $value;
        }
        $certIssuer = implode(', ', array_reverse($certIssuer));
        $X509IssuerNameNode = $this->createNewSignNode('X509IssuerName', $certIssuer);
        $issuerSerialNode->appendChild($X509IssuerNameNode);
        $X509SerialNumber = $this->createNewSignNode('X509SerialNumber', $certData['serialNumber']);
        $issuerSerialNode->appendChild($X509SerialNumber);
        $signaturePolicyIdentifierNode = $this->createNewXadesNode('SignaturePolicyIdentifier');
        $signedSignaturePropertiesNode->appendChild($signaturePolicyIdentifierNode);
        $signaturePolicyIdNode = $this->createNewXadesNode('SignaturePolicyId');
        $signaturePolicyIdentifierNode->appendChild($signaturePolicyIdNode);
        $sigPolicyIdNode = $this->createNewXadesNode('SigPolicyId');
        $signaturePolicyIdNode->appendChild($sigPolicyIdNode);
        $identifierNode = $this->createNewXadesNode('Identifier', $this->signPolicy['url']);
        $sigPolicyIdNode->appendChild($identifierNode);
        $descriptionNode = $this->createNewXadesNode('Description');
        $sigPolicyIdNode->appendChild($descriptionNode);
        $sigPolicyHashNode = $this->createNewXadesNode('SigPolicyHash');
        $signaturePolicyIdNode->appendChild($sigPolicyHashNode);
        $digestMethodNode = $this->createNewSignNode('DigestMethod');
        $sigPolicyHashNode->appendChild($digestMethodNode);
        $digestMethodNode->setAttribute('Algorithm', $this::SHA1);
        $digestValueNode = $this->createNewSignNode('DigestValue', $this->signPolicy['digest']);
        $sigPolicyHashNode->appendChild($digestValueNode);
        $signedDataObjectPropertiesNode = $this->createNewXadesNode('SignedDataObjectProperties');
        $signedPropertiesNode->appendChild($signedDataObjectPropertiesNode);
        $dataObjectFormatNode = $this->createNewXadesNode('DataObjectFormat', null, [ "ObjectReference" => "#".$this->reference0Id ]);
        $signedDataObjectPropertiesNode->appendChild($dataObjectFormatNode);
        $mimeTypeNode = $this->createNewXadesNode('MimeType', 'text/xml');
        $dataObjectFormatNode->appendChild($mimeTypeNode);
        $encodingNode = $this->createNewXadesNode('Encoding', 'UTF-8');
        $dataObjectFormatNode->appendChild($encodingNode);
    }

    public function setKeyInfoId()
    {
        $parentRef = $this->sigNode;
        $xpath = $this->getXPathObj();
        if (empty($xpath)) {
            $xpath = new DOMXPath($parentRef->ownerDocument);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
        }
        $query = "./secdsig:KeyInfo";
        $nodeset = $xpath->query($query, $parentRef);
        $keyInfo = $nodeset->item(0);
        $keyInfo->setAttribute('Id', $this->keyInfoId);
    }

    public function appendKeyValue($certInfo){
        $keyValueNode = $this->createNewSignNode('KeyValue');
        $RSAKeyValueNode = $this->createNewSignNode('RSAKeyValue');
        $keyValueNode->appendChild($RSAKeyValueNode);

        $modulusNode = $this->createNewSignNode('Modulus', $certInfo["Modulus"]);
        $RSAKeyValueNode->appendChild($modulusNode);

        $exponentNode = $this->createNewSignNode('Exponent', $certInfo["Exponent"]);
        $RSAKeyValueNode->appendChild($exponentNode);

        $this->appendToKeyInfo($keyValueNode);
        $this->setKeyInfoId();
    }
}
