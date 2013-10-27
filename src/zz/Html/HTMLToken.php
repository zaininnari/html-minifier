<?php
/**
 * The Blink HTMLTokenizer ported to PHP.
 *
 * Copyright (C) 2013 Google, Inc. All Rights Reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY APPLE INC. ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL APPLE INC. OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace zz\Html;

class HTMLToken {
    const Uninitialized = 'Uninitialized';
    const DOCTYPE = 'DOCTYPE';
    const StartTag = 'StartTag';
    const EndTag = 'EndTag';
    const Comment = 'Comment';
    const Character = 'Character';
    const EndOfFile = 'EndOfFile';

    const QuirksMode = 'QuirksMode';
    const LimitedQuirksMode = 'LimitedQuirksMode';
    const NoQuirksMode = 'NoQuirksMode';

    const DoubleQuoted = '"';
    const SingleQuoted = '\'';

    protected $_type;
    protected $_data = '';
    protected $_selfClosing = false;
    protected $_currentAttribute = 0;
    protected $_attributes = array();
    protected $_parseError = false;
    protected $_doctypeData = array(
        'hasPublicIdentifier' => false,
        'hasSystemIdentifier' => false,
        'publicIdentifier' => '',
        'systemIdentifier' => '',
        'forceQuirks' => false,
    );

    protected $_html = '';
    protected $_state = array();

    public function __construct() {
        $this->_type = static::Uninitialized;
    }

    public function __toString() {
        return $this->_data;
    }

    public function toArray() {
        $data = array(
            'type' => $this->_type,
            'data' => $this->_data,
            'selfClosing' => $this->_selfClosing,
            'attributes' => $this->_attributes,
            'parseError' => $this->_parseError,
            'html' => $this->_html,
            'state' => $this->_state,
        );
        if ($this->getType() === static::DOCTYPE) {
            $doctypeData = $this->_doctypeData;
            if ($doctypeData['forceQuirks']) {
                $mode = static::QuirksMode;
            } else {
                $mode = $this->setCompatibilityModeFromDoctype($this->_data, $doctypeData['publicIdentifier'], $doctypeData['systemIdentifier']);
            }
            $doctypeData['mode'] = $mode;
            $data['doctypeData'] = $doctypeData;
        }
        return $data;
    }

    /**
     * Source/core/html/parser/HTMLConstructionSite.cpp
     * HTMLConstructionSite::setCompatibilityModeFromDoctype
     *
     * [QuirksMode]
     * startsWith publicId
     * `+//Silmaril//dtd html Pro v0r11 19970101//`
     * `-//AdvaSoft Ltd//DTD HTML 3.0 asWedit + extensions//`
     * `-//AS//DTD HTML 3.0 asWedit + extensions//`
     * `-//IETF//DTD HTML 2.0 Level 1//`
     * `-//IETF//DTD HTML 2.0 Level 2//`
     * `-//IETF//DTD HTML 2.0 Strict Level 1//`
     * `-//IETF//DTD HTML 2.0 Strict Level 2//`
     * `-//IETF//DTD HTML 2.0 Strict//`
     * `-//IETF//DTD HTML 2.0//`
     * `-//IETF//DTD HTML 2.1E//`
     * `-//IETF//DTD HTML 3.0//`
     * `-//IETF//DTD HTML 3.2 Final//`
     * `-//IETF//DTD HTML 3.2//`
     * `-//IETF//DTD HTML 3//`
     * `-//IETF//DTD HTML Level 0//`
     * `-//IETF//DTD HTML Level 1//`
     * `-//IETF//DTD HTML Level 2//`
     * `-//IETF//DTD HTML Level 3//`
     * `-//IETF//DTD HTML Strict Level 0//`
     * `-//IETF//DTD HTML Strict Level 1//`
     * `-//IETF//DTD HTML Strict Level 2//`
     * `-//IETF//DTD HTML Strict Level 3//`
     * `-//IETF//DTD HTML Strict//`
     * `-//IETF//DTD HTML//`
     * `-//Metrius//DTD Metrius Presentational//`
     * `-//Microsoft//DTD Internet Explorer 2.0 HTML Strict//`
     * `-//Microsoft//DTD Internet Explorer 2.0 HTML//`
     * `-//Microsoft//DTD Internet Explorer 2.0 Tables//`
     * `-//Microsoft//DTD Internet Explorer 3.0 HTML Strict//`
     * `-//Microsoft//DTD Internet Explorer 3.0 HTML//`
     * `-//Microsoft//DTD Internet Explorer 3.0 Tables//`
     * `-//Netscape Comm. Corp.//DTD HTML//`
     * `-//Netscape Comm. Corp.//DTD Strict HTML//`
     * `-//O'Reilly and Associates//DTD HTML 2.0//`
     * `-//O'Reilly and Associates//DTD HTML Extended 1.0//`
     * `-//O'Reilly and Associates//DTD HTML Extended Relaxed 1.0//`
     * `-//SoftQuad Software//DTD HoTMetaL PRO 6.0::19990601::extensions to HTML 4.0//`
     * `-//SoftQuad//DTD HoTMetaL PRO 4.0::19971010::extensions to HTML 4.0//`
     * `-//Spyglass//DTD HTML 2.0 Extended//`
     * `-//SQ//DTD HTML 2.0 HoTMetaL + extensions//`
     * `-//Sun Microsystems Corp.//DTD HotJava HTML//`
     * `-//Sun Microsystems Corp.//DTD HotJava Strict HTML//`
     * `-//W3C//DTD HTML 3 1995-03-24//`
     * `-//W3C//DTD HTML 3.2 Draft//`
     * `-//W3C//DTD HTML 3.2 Final//`
     * `-//W3C//DTD HTML 3.2//`
     * `-//W3C//DTD HTML 3.2S Draft//`
     * `-//W3C//DTD HTML 4.0 Frameset//`
     * `-//W3C//DTD HTML 4.0 Transitional//`
     * `-//W3C//DTD HTML Experimental 19960712//`
     * `-//W3C//DTD HTML Experimental 970421//`
     * `-//W3C//DTD W3 HTML//`
     * `-//W3O//DTD W3 HTML 3.0//`
     * `-//WebTechs//DTD Mozilla HTML 2.0//`
     * `-//WebTechs//DTD Mozilla HTML//`
     *
     * IgnoringCase publicId
     * `-//W3O//DTD W3 HTML Strict 3.0//EN//`
     * `-/W3C/DTD HTML 4.0 Transitional/EN`
     * `HTML`
     *
     * IgnoringCase systemId
     * `http://www.ibm.com/data/dtd/v11/ibmxhtml1-transitional.dtd`
     *
     * systemId.isEmpty() && publicId.startsWith
     * `-//W3C//DTD HTML 4.01 Frameset//`
     * `-//W3C//DTD HTML 4.01 Transitional//`
     *
     * [LimitedQuirksMode]
     * startsWith publicId
     * `-//W3C//DTD XHTML 1.0 Frameset//`
     * `-//W3C//DTD XHTML 1.0 Transitional//`
     *
     * !systemId.isEmpty() && publicId.startsWith
     * `-//W3C//DTD HTML 4.01 Frameset//`
     * `-//W3C//DTD HTML 4.01 Transitional//`
     */
    protected function setCompatibilityModeFromDoctype($name, $publicId, $systemId) {

        if ($name !== 'html') {
            return static::QuirksMode;
        }
        $startsWithPublicId = "/^(?:-\/\/(?:S(?:oftQuad(?: Software\/\/DTD HoTMetaL PRO 6\.0::19990601|\/\/DTD HoTMetaL PRO 4\.0::19971010)::extensions to HTML 4\.0|un Microsystems Corp\.\/\/DTD HotJava(?: Strict)? HTML|Q\/\/DTD HTML 2\.0 HoTMetaL \+ extensions|pyglass\/\/DTD HTML 2\.0 Extended)|W(?:3(?:C\/\/DTD (?:HTML (?:3(?:\.2(?: (?:Draft|Final)|S Draft)?| 1995-03-24)|Experimental (?:19960712|970421)|4\.0 (?:Transitional|Frameset))|W3 HTML)|O\/\/DTD W3 HTML 3\.0)|ebTechs\/\/DTD Mozilla HTML(?: 2\.0)?)|IETF\/\/DTD HTML(?: (?:2\.(?:0(?: (?:Strict(?: Level [12])?|Level [12]))?|1E)|3(?:\.(?:2(?: Final)?|0))?|Strict(?: Level [0123])?|Level [0123]))?|M(?:icrosoft\/\/DTD Internet Explorer [23]\.0 (?:HTML(?: Strict)?|Tables)|etrius\/\/DTD Metrius Presentational)|O'Reilly and Associates\/\/DTD HTML (?:Extend(?:ed Relax)?ed 1|2)\.0|A(?:dvaSoft Ltd|S)\/\/DTD HTML 3\.0 asWedit \+ extensions|Netscape Comm\. Corp\.\/\/DTD(?: Strict)? HTML)|\+\/\/Silmaril\/\/dtd html Pro v0r11 19970101)\/\//";
        $ignoringCasePublicId = '/^(?:-\/(?:\/W3O\/\/DTD W3 HTML Strict 3\.0\/\/EN\/\/|W3C\/DTD HTML 4\.0 Transitional\/EN)|HTML)$/i';
        $ignoringCaseSystemId = '/^http:\/\/www\.ibm\.com\/data\/dtd\/v11\/ibmxhtml1-transitional\.dtd$/i';
        $startsWithPublicId2 = '/^-\/\/W3C\/\/DTD HTML 4\.01 (?:Transitional|Frameset)\/\//';

        if (preg_match($startsWithPublicId, $publicId) || preg_match($ignoringCasePublicId, $publicId) || preg_match($ignoringCaseSystemId, $systemId)) {
            return static::QuirksMode;
        }

        if ($systemId === '' && preg_match($startsWithPublicId2, $publicId)) {
            return static::QuirksMode;
        }

        $pattern1 = '/^-\/\/W3C\/\/DTD XHTML 1\.0 (?:Transitional|Frameset)\/\//';
        $pattern2 = ' /^-\/\/W3C\/\/DTD HTML 4\.01 (?:Transitional|Frameset)\/\//';
        if (preg_match($pattern1, $publicId) || ($systemId !== '' && preg_match($pattern2, $publicId))) {
            return static::LimitedQuirksMode;
        }

        return static::NoQuirksMode;
    }

    public function clean() {
        unset($this->_currentAttribute);
    }

    public function getType() {
        return $this->_type;
    }

    public function getName() {
        return $this->_data;
    }

    public function setType($type) {
        $this->_type = $type;
    }

    public function getHtmlOrigin() {
        return $this->_html;
    }

    public function setHtmlOrigin($html) {
        $this->_html = $html;
    }

    public function getState() {
        return $this->_state;
    }

    public function setState($states) {
        $this->_state = $states;
    }

    public function getTagName() {
        $type = $this->getType();
        if ($type !== static::StartTag && $type !== static::EndTag) {
            return false;
        }
        return $this->getName();
    }

    public function setData($data) {
        $this->_data = $data;
    }

    public function getData() {
        return $this->_data;
    }

    public function getAttributes() {
        return $this->_attributes;
    }

    public function setAttributes($attributes) {
        $this->_attributes = $attributes;
    }

    public function getDoctypeData() {
        return $this->_doctypeData;
    }

    public function hasSelfClosing() {
        return $this->_selfClosing;
    }

    public function hasParseError() {
        return $this->_parseError;
    }

    public function parseError() {
        $this->_parseError = true;
    }

    public function clear() {
        $this->_type = static::Uninitialized;
        $this->_data = '';
    }

    public function ensureIsCharacterToken() {
        $this->_type = static::Character;
    }

    public function makeEndOfFile() {
        $this->_type = static::EndOfFile;
    }

    public function appendToCharacter($character) {
        $this->_data .= $character;
    }

    public function beginComment() {
        $this->_type = static::Comment;
    }

    public function appendToComment($character) {
        $this->_data .= $character;
    }

    public function appendToName($character) {
        $this->_data .= $character;
    }

    public function setDoubleQuoted() {
        $this->_currentAttribute['quoted'] = static::DoubleQuoted;
    }

    public function setSingleQuoted() {
        $this->_currentAttribute['quoted'] = static::SingleQuoted;
    }

    /* Start/End Tag Tokens */

    public function selfClosing() {
        return $this->_selfClosing;
    }

    public function setSelfClosing() {
        $this->_selfClosing = true;
    }

    public function beginStartTag($character) {
        $this->setType(static::StartTag);
        $this->_selfClosing = false;
        $this->_currentAttribute = 0;
        $this->_attributes = array();
        $this->_data .= $character;
    }

    public function beginEndTag($character) {
        $this->setType(static::EndTag);
        $this->_selfClosing = false;
        $this->_currentAttribute = 0;
        $this->_attributes = array();
        $this->_data .= $character;
    }

    public function addNewAttribute() {
        // m_attributes.grow(m_attributes.size() + 1);
        // m_currentAttribute = &m_attributes.last();
        $_default = array(
            'name' => '',
            'value' => '',
            'quoted' => false,
        );
        unset($this->_currentAttribute);
        $this->_currentAttribute = $_default;
        $this->_attributes[] = & $this->_currentAttribute;
    }

    public function beginAttributeName($offset) {
        // m_currentAttribute->nameRange.start = offset - m_baseOffset;
        // $this->_currentAttribute['nameRange']['start'] = $offset;
    }

    public function endAttributeName($offset) {
        // int index = offset - m_baseOffset;
        // m_currentAttribute->nameRange.end = index;
        // m_currentAttribute->valueRange.start = index;
        // m_currentAttribute->valueRange.end = index;
        // $this->_currentAttribute['nameRange']['end'] = $offset;
        // $this->_currentAttribute['valueRange']['start'] = $offset;
        // $this->_currentAttribute['valueRange']['end'] = $offset;
    }

    public function beginAttributeValue($offset) {
        // m_currentAttribute->valueRange.start = offset - m_baseOffset;
        // #ifndef NDEBUG
        // m_currentAttribute->valueRange.end = 0;
        // #endif
        // $this->_currentAttribute['valueRange']['start'] = $offset;
    }

    public function endAttributeValue($offset) {
        // m_currentAttribute->valueRange.end = offset - m_baseOffset;
        // $this->_currentAttribute['valueRange']['end'] = $offset;
    }

    public function appendToAttributeName($character) {
        // FIXME: We should be able to add the following ASSERT once we fix
        // https://bugs.webkit.org/show_bug.cgi?id=62971
        //   ASSERT(m_currentAttribute->nameRange.start);
        // m_currentAttribute->name.append(character);
        $this->_currentAttribute['name'] .= $character;
    }

    public function appendToAttributeValue($character) {
        // FIXME: We should be able to add the following ASSERT once we fix
        // m_currentAttribute->value.append(character);
        $this->_currentAttribute['value'] .= $character;
    }

    /* DOCTYPE Tokens */

    public function  forceQuirks() {
        // return m_doctypeData->m_forceQuirks;
        return $this->_doctypeData['forceQuirks'];
    }

    public function  setForceQuirks() {
        // m_doctypeData->m_forceQuirks = true;
        $this->_doctypeData['forceQuirks'] = true;
    }

    protected function _beginDOCTYPE() {
        $this->_type = static::DOCTYPE;
        // m_doctypeData = adoptPtr(new DoctypeData);
    }

    public function beginDOCTYPE($character = null) {
        $this->_beginDOCTYPE();
        if ($character) {
            $this->_data .= $character;
        }
    }

    public function setPublicIdentifierToEmptyString() {
        // m_doctypeData->m_hasPublicIdentifier = true;
        // m_doctypeData->m_publicIdentifier.clear();
        $this->_doctypeData['hasPublicIdentifier'] = true;
        $this->_doctypeData['publicIdentifier'] = '';
    }

    public function setSystemIdentifierToEmptyString() {
        // m_doctypeData->m_hasSystemIdentifier = true;
        // m_doctypeData->m_systemIdentifier.clear();
        $this->_doctypeData['hasSystemIdentifier'] = true;
        $this->_doctypeData['systemIdentifier'] = '';
    }


    public function appendToPublicIdentifier($character) {
        // m_doctypeData->m_publicIdentifier.append(character);
        $this->_doctypeData['publicIdentifier'] .= $character;
    }

    public function appendToSystemIdentifier($character) {
        // m_doctypeData->m_systemIdentifier.append(character);
        $this->_doctypeData['systemIdentifier'] .= $character;
    }

}