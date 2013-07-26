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

    const DoubleQuoted = '"';
    const SingleQuoted = '\'';

    protected $_type;
    protected $_data = '';
    protected $_selfClosing = false;
    protected $_currentAttribute = 0;
    protected $_attributes = array();
    protected $_parseError = false;

    protected $_html = '';
    protected $_state = array();

    public function __construct() {
        $this->_type = static::Uninitialized;
    }

    public function __toString() {
        return $this->_data;
    }

    public function toArray() {
        return array(
            'type' => $this->_type,
            'data' => $this->_data,
            'selfClosing' => $this->_selfClosing,
            'attributes' => $this->_attributes,
            'parseError' => $this->_parseError,
            'html' => $this->_html,
            'state' => $this->_state,
        );
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
        if ($this->getType() !== static::StartTag && $this->getType() !== static::EndTag) {
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

    public function hasSelfClosing() {
        return $this->_selfClosing;
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

    public function setDoubleQuoted(){
        $this->_currentAttribute['quoted'] = static::DoubleQuoted;
    }

    public function setSingleQuoted(){
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
    }

    public function  setForceQuirks() {
        // m_doctypeData->m_forceQuirks = true;
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
    }

    public function setSystemIdentifierToEmptyString() {
        // m_doctypeData->m_hasSystemIdentifier = true;
        // m_doctypeData->m_systemIdentifier.clear();
    }


    public function appendToPublicIdentifier($character) {
        // m_doctypeData->m_publicIdentifier.append(character);
    }

    public function appendToSystemIdentifier($character) {
        // m_doctypeData->m_systemIdentifier.append(character);
    }
}
