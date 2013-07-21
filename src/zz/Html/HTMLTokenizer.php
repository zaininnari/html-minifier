<?php
/**
 * The Blink HTMLTokenizer ported to PHP.
 *
 * Copyright (C) 2008 Apple Inc. All Rights Reserved.
 * Copyright (C) 2009 Torch Mobile, Inc. http://www.torchmobile.com/
 * Copyright (C) 2010 Google, Inc. All Rights Reserved.
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

class HTMLTokenizer {

    const DataState = 'DataState';
    const CharacterReferenceInDataState = 'CharacterReferenceInDataState';
    const RCDATAState = 'RCDATAState';
    const CharacterReferenceInRCDATAState = 'CharacterReferenceInRCDATAState';
    const RAWTEXTState = 'RAWTEXTState';
    const ScriptDataState = 'ScriptDataState';
    const PLAINTEXTState = 'PLAINTEXTState';
    const TagOpenState = 'TagOpenState';
    const EndTagOpenState = 'EndTagOpenState';
    const TagNameState = 'TagNameState';
    const RCDATALessThanSignState = 'RCDATALessThanSignState';
    const RCDATAEndTagOpenState = 'RCDATAEndTagOpenState';
    const RCDATAEndTagNameState = 'RCDATAEndTagNameState';
    const RAWTEXTLessThanSignState = 'RAWTEXTLessThanSignState';
    const RAWTEXTEndTagOpenState = 'RAWTEXTEndTagOpenState';
    const RAWTEXTEndTagNameState = 'RAWTEXTEndTagNameState';
    const ScriptDataLessThanSignState = 'ScriptDataLessThanSignState';
    const ScriptDataEndTagOpenState = 'ScriptDataEndTagOpenState';
    const ScriptDataEndTagNameState = 'ScriptDataEndTagNameState';
    const ScriptDataEscapeStartState = 'ScriptDataEscapeStartState';
    const ScriptDataEscapeStartDashState = 'ScriptDataEscapeStartDashState';
    const ScriptDataEscapedState = 'ScriptDataEscapedState';
    const ScriptDataEscapedDashState = 'ScriptDataEscapedDashState';
    const ScriptDataEscapedDashDashState = 'ScriptDataEscapedDashDashState';
    const ScriptDataEscapedLessThanSignState = 'ScriptDataEscapedLessThanSignState';
    const ScriptDataEscapedEndTagOpenState = 'ScriptDataEscapedEndTagOpenState';
    const ScriptDataEscapedEndTagNameState = 'ScriptDataEscapedEndTagNameState';
    const ScriptDataDoubleEscapeStartState = 'ScriptDataDoubleEscapeStartState';
    const ScriptDataDoubleEscapedState = 'ScriptDataDoubleEscapedState';
    const ScriptDataDoubleEscapedDashState = 'ScriptDataDoubleEscapedDashState';
    const ScriptDataDoubleEscapedDashDashState = 'ScriptDataDoubleEscapedDashDashState';
    const ScriptDataDoubleEscapedLessThanSignState = 'ScriptDataDoubleEscapedLessThanSignState';
    const ScriptDataDoubleEscapeEndState = 'ScriptDataDoubleEscapeEndState';
    const BeforeAttributeNameState = 'BeforeAttributeNameState';
    const AttributeNameState = 'AttributeNameState';
    const AfterAttributeNameState = 'AfterAttributeNameState';
    const BeforeAttributeValueState = 'BeforeAttributeValueState';
    const AttributeValueDoubleQuotedState = 'AttributeValueDoubleQuotedState';
    const AttributeValueSingleQuotedState = 'AttributeValueSingleQuotedState';
    const AttributeValueUnquotedState = 'AttributeValueUnquotedState';
    const CharacterReferenceInAttributeValueState = 'CharacterReferenceInAttributeValueState';
    const AfterAttributeValueQuotedState = 'AfterAttributeValueQuotedState';
    const SelfClosingStartTagState = 'SelfClosingStartTagState';
    const BogusCommentState = 'BogusCommentState';
    const ContinueBogusCommentState = 'ContinueBogusCommentState';
    const MarkupDeclarationOpenState = 'MarkupDeclarationOpenState';
    const CommentStartState = 'CommentStartState';
    const CommentStartDashState = 'CommentStartDashState';
    const CommentState = 'CommentState';
    const CommentEndDashState = 'CommentEndDashState';
    const CommentEndState = 'CommentEndState';
    const CommentEndBangState = 'CommentEndBangState';
    const DOCTYPEState = 'DOCTYPEState';
    const BeforeDOCTYPENameState = 'BeforeDOCTYPENameState';
    const DOCTYPENameState = 'DOCTYPENameState';
    const AfterDOCTYPENameState = 'AfterDOCTYPENameState';
    const AfterDOCTYPEPublicKeywordState = 'AfterDOCTYPEPublicKeywordState';
    const BeforeDOCTYPEPublicIdentifierState = 'BeforeDOCTYPEPublicIdentifierState';
    const DOCTYPEPublicIdentifierDoubleQuotedState = 'DOCTYPEPublicIdentifierDoubleQuotedState';
    const DOCTYPEPublicIdentifierSingleQuotedState = 'DOCTYPEPublicIdentifierSingleQuotedState';
    const AfterDOCTYPEPublicIdentifierState = 'AfterDOCTYPEPublicIdentifierState';
    const BetweenDOCTYPEPublicAndSystemIdentifiersState = 'BetweenDOCTYPEPublicAndSystemIdentifiersState';
    const AfterDOCTYPESystemKeywordState = 'AfterDOCTYPESystemKeywordState';
    const BeforeDOCTYPESystemIdentifierState = 'BeforeDOCTYPESystemIdentifierState';
    const DOCTYPESystemIdentifierDoubleQuotedState = 'DOCTYPESystemIdentifierDoubleQuotedState';
    const DOCTYPESystemIdentifierSingleQuotedState = 'DOCTYPESystemIdentifierSingleQuotedState';
    const AfterDOCTYPESystemIdentifierState = 'AfterDOCTYPESystemIdentifierState';
    const BogusDOCTYPEState = 'BogusDOCTYPEState';
    const CDATASectionState = 'CDATASectionState';
    const CDATASectionRightSquareBracketState = 'CDATASectionRightSquareBracketState';
    const CDATASectionDoubleRightSquareBracketState = 'CDATASectionDoubleRightSquareBracketState';

    const kEndOfFileMarker = null;
    /**
     * @var SegmentedString
     */
    protected $_SegmentedString;

    /**
     * @var HtmlToken
     */
    protected $_Token;

    protected $_pluginsEnabled = true;
    protected $_scriptEnabled = true;

    protected $_stack = array();
    protected $_buffer = array();

    /**
     * @var HtmlToken[]
     */
    protected $_tokens = array();
    protected $_state;
    protected $_additionalAllowedCharacter = null;

    /**
     * @var string
     */
    protected $_temporaryBuffer = '';

    /**
     * @var string
     */
    protected $_bufferedEndTagName = '';

    /**
     * @var string
     */
    protected $_appropriateEndTagName = '';

    public function __construct(SegmentedString $SegmentedString, $option = array()) {
        $this->_SegmentedString = $SegmentedString;
        $this->_Token = new HTMLToken();
        $this->_state = static::DataState;
        $this->_option = $option + array('debug' => false);
    }

    /**
     * @return HtmlToken[]
     */
    public function tokenizer() {
        while (true) {
            $startPos = $this->_SegmentedString->tell();
            $this->nextToken($this->_SegmentedString);
            $this->_state = static::DataState;
            $endPos = $this->_SegmentedString->tell();
            if ($endPos - $startPos > 0) {
                $this->_compactBuffer($startPos, $endPos);

                $token = $this->_Token;
                $this->_tokens[] = $token;

                // FIXME: The tokenizer should do this work for us.
                if ($this->_Token->getType() === HTMLToken::StartTag) {
                    $this->_updateStateFor($token->getTagName());
                } else {
                    $this->_state = static::DataState;
                }
            } else {
                $this->_state = static::DataState;
            }

            $this->_buffer = array();
            $this->_Token = new HTMLToken();
            if ($this->_SegmentedString->eos()) {
                break;
            }
        }
        return $this->_tokens;
    }

    public function getTokensAsArray() {
        $result = array();
        foreach ($this->_tokens as $token) {
            $result[] = $token->toArray();
        }
        return $result;
    }

    protected function _compactBuffer($startPos, $endPos) {
        $compactBuffer = array();
        $before = null;
        $html = $this->_SegmentedString->substr($startPos, $endPos - $startPos);
        foreach ($this->_buffer as $i => $state) {
            if ($before !== $state) {
                $before = $compactBuffer[$i] = $state;
            }
        }
        switch ($this->_Token->getType()) {
            case HTMLToken::Uninitialized:
            case HTMLToken::EndOfFile:
            case HTMLToken::DOCTYPE:
            case HTMLToken::Character:
            case HTMLToken::Comment:
                $this->_Token->setData($html);
                break;
        }

        if ($this->_option['debug']) {
            $this->_Token->setHtmlOrigin($html);
            $this->_Token->setState($compactBuffer);
        }
        $this->_Token->clean();
    }


    protected function _updateStateFor($tagName) {
        if ($tagName === HTMLNames::textareaTag || $tagName === HTMLNames::titleTag) {
            $this->_state = static::RCDATAState;
        } else if ($tagName === HTMLNames::plaintextTag) {
            $this->_state = static::PLAINTEXTState;
        } else if ($tagName === HTMLNames::scriptTag) {
            $this->_state = static::ScriptDataState;
        } else if ($tagName === HTMLNames::styleTag || $tagName === HTMLNames::iframeTag || $tagName === HTMLNames::xmpTag || ($tagName === HTMLNames::noembedTag && $this->_pluginsEnabled) || $tagName === HTMLNames::noframesTag || ($tagName === HTMLNames::noscriptTag && $this->_scriptEnabled)) {
            $this->_state = static::RAWTEXTState;
        }
    }

    protected function _isEndTagBufferingState($state) {
        switch ($state) {
            case HTMLTokenizer::RCDATAEndTagOpenState:
            case HTMLTokenizer::RCDATAEndTagNameState:
            case HTMLTokenizer::RAWTEXTEndTagOpenState:
            case HTMLTokenizer::RAWTEXTEndTagNameState:
            case HTMLTokenizer::ScriptDataEndTagOpenState:
            case HTMLTokenizer::ScriptDataEndTagNameState:
            case HTMLTokenizer::ScriptDataEscapedEndTagOpenState:
            case HTMLTokenizer::ScriptDataEscapedEndTagNameState:
                return true;
            default:
                return false;
        }
    }

    // http://www.whatwg.org/specs/web-apps/current-work/#tokenization
    public function nextToken(SegmentedString $source) {
        while (true) {
            // if (!$this->_isEndTagBufferingState($this->_state)) {
            //     // FIXME: This should call flushBufferedEndTag().
            //     // We started an end tag during our last iteration.
            //     $this->_Token->beginEndTag($this->_bufferedEndTagName);
            //     $this->_bufferedEndTagName = '';
            //     $this->_appropriateEndTagName = '';
            //     $this->_temporaryBuffer = '';
            //     if ($this->_state === HTMLTokenizer::DataState) {
            //         // We're back in the data state, so we must be done with the tag.
            //         return true;
            //     }
            // }

            $char = $this->_nextInputCharacter($this->_state);
            if ($char !== null) {
                $this->_buffer[] = $this->_state;
            }
            switch ($this->_state) {
                case static::DataState :
                    if ($char === '&') {
                        $this->_HTML_ADVANCE_TO(static::CharacterReferenceInDataState);
                    } elseif ($char === '<') {
                        if ($this->_Token->getType() === HTMLToken::Character) {
                            // We have a bunch of character tokens queued up that we
                            // are emitting lazily here.
                            $this->_bufferPop();
                            return true;
                        }
                        $this->_HTML_ADVANCE_TO(static::TagOpenState);
                    } elseif ($char === static::kEndOfFileMarker) {
                        return $this->_emitEndOfFile($source);
                    } else {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::DataState);
                    }
                    break;

                case static::CharacterReferenceInDataState:
                    if (!$this->_processEntity($source)) {
                        return $this->_haveBufferedCharacterToken();
                    }
                    $this->_HTML_SWITCH_TO(static::DataState);
                    break;

                case static::RCDATAState:
                    if ($char === '&') {
                        $this->_HTML_ADVANCE_TO(static::CharacterReferenceInRCDATAState);
                    } else if ($char === '<') {
                        $this->_HTML_ADVANCE_TO(static::RCDATALessThanSignState);
                    } else if ($char === static::kEndOfFileMarker) {
                        return $this->_emitEndOfFile($source);
                    } else {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::RCDATAState);
                    }
                    break;

                case static::CharacterReferenceInRCDATAState:
                    if (!$this->_processEntity($source)) {
                        return $this->_haveBufferedCharacterToken();
                    }
                    $this->_HTML_SWITCH_TO(static::RCDATAState);
                    break;

                case static::RAWTEXTState:
                    if ($char === '<') {
                        $this->_HTML_ADVANCE_TO(static::RAWTEXTLessThanSignState);
                    } else if ($char === static::kEndOfFileMarker) {
                        return $this->_emitEndOfFile($source);
                    } else {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::RAWTEXTState);
                    }
                    break;

                case static::ScriptDataState:
                    if ($char === '<') {
                        $this->_HTML_ADVANCE_TO(static::ScriptDataLessThanSignState);
                    } else if ($char === static::kEndOfFileMarker) {
                        return $this->_emitEndOfFile($source);
                    } else {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataState);
                    }
                    break;

                case static::PLAINTEXTState:
                    if ($char === static::kEndOfFileMarker) {
                        return $this->_emitEndOfFile($source);
                    } else {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::PLAINTEXTState);
                    }
                    break;

                case static::TagOpenState:
                    if ($char === '!') {
                        $this->_HTML_ADVANCE_TO(static::MarkupDeclarationOpenState);
                    } else if ($char === '/') {
                        $this->_HTML_ADVANCE_TO(static::EndTagOpenState);
                    } else if ($this->_isASCIIUpper($char)) {
                        $this->_Token->beginStartTag(strtolower($char));
                        $this->_HTML_ADVANCE_TO(static::TagNameState);
                    } else if ($this->_isASCIILower($char)) {
                        $this->_Token->beginStartTag(strtolower($char));
                        $this->_HTML_ADVANCE_TO(static::TagNameState);
                    } else if ($char === '?') {
                        $this->_parseError();
                        // The spec consumes the current character before switching
                        // to the bogus comment state, but it's easier to implement
                        // if we reconsume the current character.
                        $this->_HTML_RECONSUME_IN(static::BogusCommentState);
                    } else {
                        $this->_parseError();
                        $this->_bufferCharacter('<');
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    }
                    break;

                case static::EndTagOpenState:
                    if ($this->_isASCIIUpper($char)) {
                        $this->_Token->beginEndTag(strtolower($char));
                        $this->_HTML_ADVANCE_TO(static::TagNameState);
                    } elseif ($this->_isASCIILower($char)) {
                        $this->_Token->beginEndTag(strtolower($char));
                        $this->_HTML_ADVANCE_TO(static::TagNameState);
                    } else if ($char === '>') {
                        $this->_parseError();
                        $this->_HTML_ADVANCE_TO(static::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_bufferCharacter('<');
                        $this->_bufferCharacter('/');
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::BogusCommentState);
                    }
                    break;

                case static::TagNameState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::BeforeAttributeNameState);
                    } else if ($char === '/') {
                        $this->_HTML_ADVANCE_TO(static::SelfClosingStartTagState);
                    } elseif ($char === '>') {
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($this->_isASCIIUpper($char)) {
                        $this->_Token->appendToName(strtolower($char));
                        $this->_HTML_ADVANCE_TO(static::TagNameState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        $this->_Token->appendToName($char);
                        $this->_HTML_ADVANCE_TO(static::TagNameState);
                    }
                    break;

                case static::RCDATALessThanSignState:
                    if ($char === '/') {
                        $this->_temporaryBuffer = '';
                        $this->_HTML_ADVANCE_TO(static::RCDATAEndTagOpenState);
                    } else {
                        $this->_bufferCharacter('<');
                        $this->_bufferPop();
                        $this->_HTML_RECONSUME_IN(static::RCDATAState);
                    }
                    break;

                case static::RCDATAEndTagOpenState:
                    if ($this->_isASCIIUpper($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= strtolower($char);
                        $this->_HTML_ADVANCE_TO(static::RCDATAEndTagNameState);
                    } else if ($this->_isASCIILower($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= $char;
                        $this->_HTML_ADVANCE_TO(static::RCDATAEndTagNameState);
                    } else {
                        $this->_bufferCharacter('<');
                        $this->_bufferCharacter('/');
                        $this->_HTML_RECONSUME_IN(static::RCDATAState);
                    }
                    break;

                case static::RCDATAEndTagNameState:
                    if ($this->_isASCIIUpper($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= strtolower($char);
                        // HTML_ADVANCE_TO(RCDATAEndTagNameState);
                        $this->_HTML_ADVANCE_TO(static::RCDATAState);
                    } else if ($this->_isASCIILower($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= $char;
                        // HTML_ADVANCE_TO(RCDATAEndTagNameState);
                        $this->_HTML_ADVANCE_TO(static::RCDATAEndTagNameState);
                    } else {
                        if ($this->_isTokenizerWhitespace($char)) {
                            if ($this->_isAppropriateEndTag()) {
                                $this->_temporaryBuffer .= $char;
                                $this->_FLUSH_AND_ADVANCE_TO(static::BeforeAttributeNameState);
                            }
                        } else if ($char === '/') {
                            if ($this->_isAppropriateEndTag()) {
                                $this->_temporaryBuffer .= $char;
                                $this->_FLUSH_AND_ADVANCE_TO(static::SelfClosingStartTagState);

                            }
                        } else if ($char === '>') {
                            if ($this->_isAppropriateEndTag()) {
                                $this->_temporaryBuffer .= $char;
                                return $this->_flushEmitAndResumeIn($source, HTMLTokenizer::DataState);
                            }
                        }
                        $this->_bufferCharacter('<');
                        $this->_bufferCharacter('/');
                        // $this->_Token->appendToCharacter(m_temporaryBuffer);
                        $this->_bufferedEndTagName = '';
                        $this->_temporaryBuffer = '';
                        $this->_bufferPop();
                        $this->_HTML_RECONSUME_IN(static::RCDATAState);
                    }
                    break;

                case static::RAWTEXTLessThanSignState:
                    if ($char === '/') {
                        $this->_temporaryBuffer = '';
                        $this->_HTML_ADVANCE_TO(static::RAWTEXTEndTagOpenState);
                    } else {
                        $this->_bufferCharacter('<');
                        $this->_HTML_RECONSUME_IN(static::RAWTEXTState);
                    }
                    break;

                case static::RAWTEXTEndTagOpenState:
                    if ($this->_isASCIIUpper($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= strtolower($char);
                        $this->_HTML_ADVANCE_TO(static::RAWTEXTEndTagNameState);
                    } else if ($this->_isASCIILower($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= $char;
                        $this->_HTML_ADVANCE_TO(static::RAWTEXTEndTagNameState);
                    } else {
                        $this->_bufferCharacter('<');
                        $this->_bufferCharacter('/');
                        $this->_HTML_RECONSUME_IN(static::RAWTEXTState);
                    }
                    break;

                case static::RAWTEXTEndTagNameState:
                    if ($this->_isASCIIUpper($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= strtolower($char);
                        $this->_HTML_ADVANCE_TO(static::RAWTEXTEndTagNameState);
                    } else if ($this->_isASCIILower($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= $char;
                        $this->_HTML_ADVANCE_TO(static::RAWTEXTEndTagNameState);
                    } else {
                        if ($this->_isTokenizerWhitespace($char)) {
                            if ($this->_isAppropriateEndTag()) {
                                $this->_temporaryBuffer .= $char;
                                $this->_FLUSH_AND_ADVANCE_TO(static::BeforeAttributeNameState);
                            }
                        } else if ($char == '/') {
                            if ($this->_isAppropriateEndTag()) {
                                $this->_temporaryBuffer .= $char;
                                $this->_FLUSH_AND_ADVANCE_TO(static::SelfClosingStartTagState);
                            }
                        } else if ($char == '>') {
                            if ($this->_isAppropriateEndTag()) {
                                $this->_temporaryBuffer .= $char;
                                return $this->_flushEmitAndResumeIn($source, HTMLTokenizer::DataState);
                            }
                        }
                        $this->_bufferCharacter('<');
                        $this->_bufferCharacter('/');
                        $this->_Token->appendToCharacter($this->_temporaryBuffer);
                        $this->_bufferedEndTagName = array();
                        $this->_temporaryBuffer = '';
                        $this->_HTML_RECONSUME_IN(static::RAWTEXTState);
                    }
                    break;

                case static::ScriptDataLessThanSignState:
                    if ($char === '/') {
                        $this->_temporaryBuffer = '';
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEndTagOpenState);
                    } else if ($char === '!') {
                        $this->_bufferCharacter('<');
                        $this->_bufferCharacter('!');
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapeStartState);
                    } else {
                        $this->_bufferCharacter('<');
                        $this->_bufferPop();
                        $this->_HTML_RECONSUME_IN(static::ScriptDataState);
                    }
                    break;

                case static::ScriptDataEndTagOpenState:
                    if ($this->_isASCIIUpper($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= strtolower($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEndTagNameState);
                    } else if ($this->_isASCIILower($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= $char;
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEndTagNameState);
                    } else {
                        $this->_bufferCharacter('<');
                        $this->_bufferCharacter('/');
                        $this->_HTML_RECONSUME_IN(static::ScriptDataState);
                    }
                    break;

                case static::ScriptDataEndTagNameState:
                    if ($this->_isASCIIUpper($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= strtolower($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEndTagNameState);
                    } else if ($this->_isASCIILower($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= $char;
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEndTagNameState);
                    } else {
                        if ($this->_isTokenizerWhitespace($char)) {
                            if ($this->_isAppropriateEndTag()) {
                                $this->_temporaryBuffer .= $char;
                                $this->_FLUSH_AND_ADVANCE_TO(static::BeforeAttributeNameState);
                            }
                        } else if ($char === '/') {
                            if ($this->_isAppropriateEndTag()) {
                                $this->_temporaryBuffer .= $char;
                                $this->_FLUSH_AND_ADVANCE_TO(static::SelfClosingStartTagState);
                            }
                        } else if ($char === '>') {
                            if ($this->_isAppropriateEndTag()) {
                                $this->_temporaryBuffer .= $char;
                                return $this->_flushEmitAndResumeIn($source, HTMLTokenizer::DataState);
                            }
                        }
                        $this->_bufferCharacter('<');
                        $this->_bufferCharacter('/');
                        $this->_Token->appendToCharacter($this->_temporaryBuffer);
                        $this->_bufferedEndTagName = array();
                        $this->_temporaryBuffer = '';
                        $this->_HTML_RECONSUME_IN(static::ScriptDataState);
                    }
                    break;

                case static::ScriptDataEscapeStartState:
                    if ($char === '-') {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapeStartDashState);
                    } else {
                        $this->_bufferPop();
                        $this->_HTML_RECONSUME_IN(static::ScriptDataState);
                    }
                    break;

                case static::ScriptDataEscapeStartDashState:
                    if ($char === '-') {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedDashDashState);
                    } else {
                        $this->_HTML_RECONSUME_IN(static::ScriptDataState);
                    }
                    break;

                case static::ScriptDataEscapedState:
                    if ($char === '-') {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedDashState);
                    } else if ($char === '<') {
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedLessThanSignState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedState);
                    }
                    break;

                case static::ScriptDataEscapedDashState:
                    if ($char === '-') {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedDashDashState);
                    } else if ($char === '<') {
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedLessThanSignState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedState);
                    }
                    break;

                case static::ScriptDataEscapedDashDashState:
                    if ($char === '-') {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedDashDashState);
                    } else if ($char === '<') {
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedLessThanSignState);
                    } else if ($char === '>') {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedState);
                    }
                    break;

                case static::ScriptDataEscapedLessThanSignState:
                    if ($char === '/') {
                        $this->_temporaryBuffer = '';
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedEndTagOpenState);
                    } else if ($this->_isASCIIUpper($char)) {
                        $this->_bufferCharacter('<');
                        $this->_bufferCharacter($char);
                        $this->_temporaryBuffer = '';

                        $this->_temporaryBuffer = strtolower($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapeStartState);
                    } else if ($this->_isASCIILower($char)) {
                        $this->_bufferCharacter('<');
                        $this->_bufferCharacter($char);
                        $this->_temporaryBuffer = '';
                        $this->_temporaryBuffer .= $char;
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapeStartState);
                    } else {
                        $this->_bufferCharacter('<');
                        $this->_HTML_RECONSUME_IN(static::ScriptDataEscapedState);
                    }
                    break;

                case static::ScriptDataEscapedEndTagOpenState:
                    if ($this->_isASCIIUpper($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= strtolower($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedEndTagNameState);
                    } else if ($this->_isASCIILower($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= $char;
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedEndTagNameState);
                    } else {
                        $this->_bufferCharacter('<');
                        $this->_bufferCharacter('/');
                        $this->_HTML_RECONSUME_IN(static::ScriptDataEscapedState);
                    }
                    break;

                case static::ScriptDataEscapedEndTagNameState:
                    if ($this->_isASCIIUpper($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= strtolower($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedEndTagNameState);
                    } else if ($this->_isASCIILower($char)) {
                        $this->_temporaryBuffer .= $char;
                        $this->_bufferedEndTagName .= $char;
                        $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedEndTagNameState);
                    } else {
                        if ($this->_isTokenizerWhitespace($char)) {
                            if ($this->_isAppropriateEndTag()) {
                                $this->_temporaryBuffer .= $char;
                                $this->_temporaryBuffer .= $char;
                                $this->_FLUSH_AND_ADVANCE_TO(static::BeforeAttributeNameState);
                            }
                        } else if ($char === '/') {
                            if ($this->_isAppropriateEndTag()) {
                                $this->_temporaryBuffer .= $char;
                                $this->_temporaryBuffer .= $char;
                                $this->_FLUSH_AND_ADVANCE_TO(static::SelfClosingStartTagState);
                            }
                        } else if ($char === '>') {
                            if ($this->_isAppropriateEndTag()) {
                                $this->_temporaryBuffer .= $char;
                                $this->_temporaryBuffer .= $char;
                                return $this->_flushEmitAndResumeIn($$source, HTMLTokenizer::DataState);
                            }
                        }
                        $this->_bufferCharacter('<');
                        $this->_bufferCharacter('/');
                        $this->_Token->appendToCharacter($this->_temporaryBuffer);
                        $this->_bufferedEndTagName = array();
                        $this->_temporaryBuffer = '';
                        $this->_HTML_RECONSUME_IN(static::ScriptDataEscapedState);
                    }
                    break;

                case static::ScriptDataDoubleEscapeStartState:
                    if ($this->_isTokenizerWhitespace($char) || $char === '/' || $char === '>') {
                        $this->_bufferCharacter($char);
                        if ($this->_temporaryBufferIs(HTMLNames::scriptTag)) {
                            $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapedState);
                        } else {
                            $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedState);
                        }
                    } else if ($this->_isASCIIUpper($char)) {
                        $this->_bufferCharacter($char);
                        $this->_temporaryBuffer .= strtolower($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapeStartState);
                    } else if ($this->_isASCIILower($char)) {
                        $this->_bufferCharacter($char);
                        $this->_temporaryBuffer .= $char;
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapeStartState);
                    } else {
                        $this->_HTML_RECONSUME_IN(static::ScriptDataEscapedState);
                    }
                    break;

                case static::ScriptDataDoubleEscapedState:
                    if ($char === '-') {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapedDashState);
                    } else if ($char === '<') {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapedLessThanSignState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapedState);
                    }
                    break;

                case static::ScriptDataDoubleEscapedDashState:
                    if ($char === '-') {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapedDashDashState);
                    } else if ($char === '<') {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapedLessThanSignState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapedState);
                    }
                    break;

                case static::ScriptDataDoubleEscapedDashDashState:
                    if ($char === '-') {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapedDashDashState);
                    } else if ($char === '<') {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapedLessThanSignState);
                    } else if ($char === '>') {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapedState);
                    }
                    break;

                case static::ScriptDataDoubleEscapedLessThanSignState:
                    if ($char === '/') {
                        $this->_bufferCharacter($char);
                        $this->_temporaryBuffer = '';
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapeEndState);
                    } else
                        $this->_HTML_RECONSUME_IN(static::ScriptDataDoubleEscapedState);
                    break;

                case static::ScriptDataDoubleEscapeEndState:
                    if ($this->_isTokenizerWhitespace($char) || $char === '/' || $char === '>') {
                        $this->_bufferCharacter($char);
                        if ($this->_temporaryBufferIs(HTMLNames::scriptTag)) {
                            $this->_HTML_ADVANCE_TO(static::ScriptDataEscapedState);
                        } else {
                            $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapedState);
                        }
                    } else if ($this->_isASCIIUpper($char)) {
                        $this->_bufferCharacter($char);
                        $this->_temporaryBuffer .= strtolower($char);
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapeEndState);
                    } else if ($this->_isASCIILower($char)) {
                        $this->_bufferCharacter($char);
                        $this->_temporaryBuffer .= $char;
                        $this->_HTML_ADVANCE_TO(static::ScriptDataDoubleEscapeEndState);
                    } else {
                        $this->_HTML_RECONSUME_IN(static::ScriptDataDoubleEscapedState);
                    }
                    break;

                case static::BeforeAttributeNameState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::BeforeAttributeNameState);
                    } else if ($char === '/') {
                        $this->_HTML_ADVANCE_TO(static::SelfClosingStartTagState);
                    } else if ($char === '>') {
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($this->_isASCIIUpper($char)) {
                        $this->_Token->addNewAttribute();
                        $this->_Token->beginAttributeName($source->numberOfCharactersConsumed());
                        $this->_Token->appendToAttributeName(strtolower($char));
                        $this->_HTML_ADVANCE_TO(static::AttributeNameState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        if ($char === '"' || $char === '\'' || $char === '<' || $char === '=') $this->_parseError();
                        $this->_Token->addNewAttribute();
                        $this->_Token->beginAttributeName($source->numberOfCharactersConsumed());
                        $this->_Token->appendToAttributeName($char);
                        $this->_HTML_ADVANCE_TO(static::AttributeNameState);
                    }
                    break;

                case static::AttributeNameState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_Token->endAttributeName($source->numberOfCharactersConsumed());
                        $this->_HTML_ADVANCE_TO(static::AfterAttributeNameState);
                    } else if ($char === '/') {
                        $this->_Token->endAttributeName($source->numberOfCharactersConsumed());
                        $this->_HTML_ADVANCE_TO(static::SelfClosingStartTagState);
                    } else if ($char === '=') {
                        $this->_Token->endAttributeName($source->numberOfCharactersConsumed());
                        $this->_HTML_ADVANCE_TO(static::BeforeAttributeValueState);
                    } else if ($char === '>') {
                        $this->_Token->endAttributeName($source->numberOfCharactersConsumed());
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($this->_isASCIIUpper($char)) {
                        $this->_Token->appendToAttributeName(strtolower($char));
                        $this->_HTML_ADVANCE_TO(static::AttributeNameState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->endAttributeName($source->numberOfCharactersConsumed());
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        if ($char === '"' || $char === '\'' || $char === '<' || $char === '=') $this->_parseError();
                        $this->_Token->appendToAttributeName($char);
                        $this->_HTML_ADVANCE_TO(static::AttributeNameState);
                    }
                    break;

                case static::AfterAttributeNameState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::AfterAttributeNameState);
                    } else if ($char === '/') {
                        $this->_HTML_ADVANCE_TO(static::SelfClosingStartTagState);
                    } else if ($char === '=') {
                        $this->_HTML_ADVANCE_TO(static::BeforeAttributeValueState);
                    } else if ($char === '>') {
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($this->_isASCIIUpper($char)) {
                        $this->_Token->addNewAttribute();
                        $this->_Token->beginAttributeName($source->numberOfCharactersConsumed());
                        $this->_Token->appendToAttributeName(strtolower($char));
                        $this->_HTML_ADVANCE_TO(static::AttributeNameState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        if ($char === '"' || $char === '\'' || $char === '<') $this->_parseError();
                        $this->_Token->addNewAttribute();
                        $this->_Token->beginAttributeName($source->numberOfCharactersConsumed());
                        $this->_Token->appendToAttributeName($char);
                        $this->_HTML_ADVANCE_TO(static::AttributeNameState);
                    }
                    break;

                case static::BeforeAttributeValueState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::BeforeAttributeValueState);
                    } else if ($char === '"') {
                        $this->_Token->beginAttributeValue($source->numberOfCharactersConsumed() + 1);
                        $this->_HTML_ADVANCE_TO(static::AttributeValueDoubleQuotedState);
                    } else if ($char === '&') {
                        $this->_Token->beginAttributeValue($source->numberOfCharactersConsumed());
                        $this->_HTML_RECONSUME_IN(static::AttributeValueUnquotedState);
                    } else if ($char === '\'') {
                        $this->_Token->beginAttributeValue($source->numberOfCharactersConsumed() + 1);
                        $this->_HTML_ADVANCE_TO(static::AttributeValueSingleQuotedState);
                    } else if ($char === '>') {
                        $this->_parseError();
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        if ($char === '<' || $char === '=' || $char === '`') $this->_parseError();
                        $this->_Token->beginAttributeValue($source->numberOfCharactersConsumed());
                        $this->_Token->appendToAttributeValue($char);
                        $this->_HTML_ADVANCE_TO(static::AttributeValueUnquotedState);
                    }
                    break;

                case static::AttributeValueDoubleQuotedState:
                    if ($char === '"') {
                        $this->_Token->endAttributeValue($source->numberOfCharactersConsumed());
                        $this->_HTML_ADVANCE_TO(static::AfterAttributeValueQuotedState);
                    } else if ($char === '&') {
                        $this->_additionalAllowedCharacter = '"';
                        $this->_HTML_ADVANCE_TO(static::CharacterReferenceInAttributeValueState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->endAttributeValue($source->numberOfCharactersConsumed());
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        $this->_Token->appendToAttributeValue($char);
                        $this->_HTML_ADVANCE_TO(static::AttributeValueDoubleQuotedState);
                    }
                    break;

                case static::AttributeValueSingleQuotedState:
                    if ($char === '\'') {
                        $this->_Token->endAttributeValue($source->numberOfCharactersConsumed());
                        $this->_HTML_ADVANCE_TO(static::AfterAttributeValueQuotedState);
                    } else if ($char === '&') {
                        $this->_additionalAllowedCharacter = '\'';
                        $this->_HTML_ADVANCE_TO(static::CharacterReferenceInAttributeValueState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->endAttributeValue($source->numberOfCharactersConsumed());
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        $this->_Token->appendToAttributeValue($char);
                        $this->_HTML_ADVANCE_TO(static::AttributeValueSingleQuotedState);
                    }
                    break;

                case static::AttributeValueUnquotedState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_Token->endAttributeValue($source->numberOfCharactersConsumed());
                        $this->_HTML_ADVANCE_TO(static::BeforeAttributeNameState);
                    } else if ($char === '&') {
                        $this->_additionalAllowedCharacter = '>';
                        $this->_HTML_ADVANCE_TO(static::CharacterReferenceInAttributeValueState);
                    } else if ($char === '>') {
                        $this->_Token->endAttributeValue($source->numberOfCharactersConsumed());
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->endAttributeValue($source->numberOfCharactersConsumed());
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        if ($char === '"' || $char === '\'' || $char === '<' || $char === '=' || $char === '`') $this->_parseError();
                        $this->_Token->appendToAttributeValue($char);
                        $this->_HTML_ADVANCE_TO(static::AttributeValueUnquotedState);
                    }
                    break;

                case static::CharacterReferenceInAttributeValueState:
                    $notEnoughCharacters = false;
                    $decodedEntity = array();
                    $success = $this->_consumeHTMLEntity($source, $decodedEntity, $notEnoughCharacters, $this->_additionalAllowedCharacter);
                    if ($notEnoughCharacters) {
                        return $this->_haveBufferedCharacterToken();
                    }
                    if (!$success) {
                        $this->_Token->appendToAttributeValue('&');
                    } else {
                        for ($i = 0; $i < count($decodedEntity); ++$i) $this->_Token->appendToAttributeValue($decodedEntity[$i]);
                    }
                    // We're supposed to switch back to the attribute value state that
                    // we were in when we were switched into this state. Rather than
                    // keeping track of this explictly, we observe that the previous
                    // state can be determined by $this->_additionalAllowedCharacter.
                    if ($this->_additionalAllowedCharacter == '"') {
                        $this->_HTML_SWITCH_TO(static::AttributeValueDoubleQuotedState);
                    } else if ($this->_additionalAllowedCharacter == '\'') {
                        $this->_HTML_SWITCH_TO(static::AttributeValueSingleQuotedState);
                    } else if ($this->_additionalAllowedCharacter == '>') {
                        $this->_HTML_SWITCH_TO(static::AttributeValueUnquotedState);
                    } else {
                        // ASSERT_NOT_REACHED();
                    }
                    break;

                case static::AfterAttributeValueQuotedState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::BeforeAttributeNameState);
                    } else if ($char === '/') {
                        $this->_HTML_ADVANCE_TO(static::SelfClosingStartTagState);
                    } else if ($char === '>') {
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::BeforeAttributeNameState);
                    }
                    break;

                case static::SelfClosingStartTagState:
                    if ($char === '>') {
                        $this->_Token->setSelfClosing();
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::BeforeAttributeNameState);
                    }
                    break;

                case static::BogusCommentState:
                    $this->_Token->beginComment();
                    $this->_bufferPop();
                    $this->_SegmentedString->seek($this->_SegmentedString->tell() - 1);
                    $this->_HTML_RECONSUME_IN(static::ContinueBogusCommentState);
                    break;

                case static::ContinueBogusCommentState:
                    if ($char === '>') {
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_Token->appendToComment($char);
                        $this->_HTML_ADVANCE_TO(static::ContinueBogusCommentState);
                    }
                    break;

                case static::MarkupDeclarationOpenState:
                    $dashDashString = '--';
                    $doctypeString = 'doctype';
                    $cdataString = '[CDATA[';
                    if ($char === '-') {
                        $result = $source->lookAhead($dashDashString);
                        if ($result == SegmentedString::DidMatch) {
                            $str = $this->_SegmentedString->read(strlen('--'));
                            $this->_Token->beginComment();
                            $this->_HTML_SWITCH_TO(static::CommentStartState);
                            continue;
                        } else if ($result === SegmentedString::NotEnoughCharacters) {
                            // FIXME
                            $this->_SegmentedString->advance();
                            return $this->_haveBufferedCharacterToken();
                        }
                    } else if ($char === 'D' || $char === 'd') {
                        // advanceStringAndASSERTIgnoringCase(source, "doctype");
                        if ($this->_SegmentedString->lookAheadIgnoringCase($doctypeString)) {
                            $str = $this->_SegmentedString->read(strlen($doctypeString));
                            $this->_HTML_SWITCH_TO(static::DOCTYPEState);
                            continue;
                        } else {
                            // FIXME
                            $this->_SegmentedString->advance();
                            return $this->_haveBufferedCharacterToken();
                        }
                    } else if ($char === '[' && $this->_shouldAllowCDATA()) {
                        $result = $source->lookAhead($cdataString);
                        if ($result == SegmentedString::DidMatch) {
                            // advanceStringAndASSERT($source, "[CDATA[");
                            $str = $this->_SegmentedString->read(strlen($cdataString));
                            $this->_HTML_SWITCH_TO(static::CDATASectionState);
                            continue;
                        } else if ($result === SegmentedString::NotEnoughCharacters) {
                            // FIXME
                            $this->_SegmentedString->advance();
                            return $this->_haveBufferedCharacterToken();
                        }
                    }
                    $this->_parseError();
                    $this->_bufferPop();
                    $this->_SegmentedString->seek($this->_SegmentedString->tell() - 1);
                    $this->_HTML_RECONSUME_IN(static::BogusCommentState);
                    break;

                case static::CommentStartState:
                    if ($char === '-') {
                        $this->_HTML_ADVANCE_TO(static::CommentStartDashState);
                    } else if ($char === '>') {
                        $this->_parseError();
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_Token->appendToComment($char);
                        $this->_HTML_ADVANCE_TO(static::CommentState);
                    }
                    break;

                case static::CommentStartDashState:
                    if ($char === '-') {
                        $this->_HTML_ADVANCE_TO(static::CommentEndState);
                    } else if ($char === '>') {
                        $this->_parseError();
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_Token->appendToComment('-');
                        $this->_Token->appendToComment($char);
                        $this->_HTML_ADVANCE_TO(static::CommentState);
                    }
                    break;

                case static::CommentState:
                    if ($char === '-') {
                        $this->_HTML_ADVANCE_TO(static::CommentEndDashState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_Token->appendToComment($char);
                        $this->_HTML_ADVANCE_TO(static::CommentState);
                    }
                    break;

                case static::CommentEndDashState:
                    if ($char === '-') {
                        $this->_HTML_ADVANCE_TO(static::CommentEndState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_Token->appendToComment('-');
                        $this->_Token->appendToComment($char);
                        $this->_HTML_ADVANCE_TO(static::CommentState);
                    }
                    break;

                case static::CommentEndState:
                    if ($char === '>') {
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === '!') {
                        $this->_parseError();
                        $this->_HTML_ADVANCE_TO(static::CommentEndBangState);
                    } else if ($char === '-') {
                        $this->_parseError();
                        $this->_Token->appendToComment('-');
                        $this->_HTML_ADVANCE_TO(static::CommentEndState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError(true);
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_parseError();
                        $this->_Token->appendToComment('-');
                        $this->_Token->appendToComment('-');
                        $this->_Token->appendToComment($char);
                        $this->_HTML_ADVANCE_TO(static::CommentState);
                    }
                    break;

                case static::CommentEndBangState:
                    if ($char === '-') {
                        $this->_Token->appendToComment('-');
                        $this->_Token->appendToComment('-');
                        $this->_Token->appendToComment('!');
                        $this->_HTML_ADVANCE_TO(static::CommentEndDashState);
                    } else if ($char === '>') {
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError(true);
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_Token->appendToComment('-');
                        $this->_Token->appendToComment('-');
                        $this->_Token->appendToComment('!');
                        $this->_Token->appendToComment($char);
                        $this->_HTML_ADVANCE_TO(static::CommentState);
                    }
                    break;

                case static::DOCTYPEState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::BeforeDOCTYPENameState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->beginDOCTYPE();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_parseError();
                        $this->_HTML_RECONSUME_IN(static::BeforeDOCTYPENameState);
                    }
                    break;

                case static::BeforeDOCTYPENameState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::BeforeDOCTYPENameState);
                    } else if ($this->_isASCIIUpper($char)) {
                        $this->_Token->beginDOCTYPE(strtolower($char));
                        $this->_HTML_ADVANCE_TO(static::DOCTYPENameState);
                    } else if ($char === '>') {
                        $this->_parseError();
                        $this->_Token->beginDOCTYPE();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError(true);
                        $this->_Token->beginDOCTYPE();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_Token->beginDOCTYPE($char);
                        $this->_HTML_ADVANCE_TO(static::DOCTYPENameState);
                    }
                    break;

                case static::DOCTYPENameState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::AfterDOCTYPENameState);
                    } else if ($char === '>') {
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($this->_isASCIIUpper($char)) {
                        $this->_Token->appendToName(strtolower($char));
                        $this->_HTML_ADVANCE_TO(static::DOCTYPENameState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError(true);
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_Token->appendToName($char);
                        $this->_HTML_ADVANCE_TO(static::DOCTYPENameState);
                    }
                    break;

                case static::AfterDOCTYPENameState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::AfterDOCTYPENameState);
                    } else if ($char === '>') {
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError(true);
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        // DEFINE_STATIC_LOCAL(String, publicString, (ASCIILiteral("public")));
                        $publicString = 'public';
                        // DEFINE_STATIC_LOCAL(String, systemString, (ASCIILiteral("system")));
                        $systemString = 'system';
                        if ($char === 'P' || $char === 'p') {
                            $result = $source->lookAheadIgnoringCase($publicString);
                            if ($result == SegmentedString::DidMatch) {
                                // advanceStringAndASSERTIgnoringCase($source, "public");
                                $this->_HTML_SWITCH_TO(static::AfterDOCTYPEPublicKeywordState);
                                $str = $this->_SegmentedString->read(strlen($publicString));
                            } else if ($result == SegmentedString::NotEnoughCharacters) {
                                return $this->_haveBufferedCharacterToken();
                            }
                        } else if ($char === 'S' || $char === 's') {
                            $result = $source->lookAheadIgnoringCase($systemString);
                            if ($result == SegmentedString::DidMatch) {
                                // advanceStringAndASSERTIgnoringCase($source, "system");
                                $this->_HTML_SWITCH_TO(static::AfterDOCTYPESystemKeywordState);
                                $str = $this->_SegmentedString->read(strlen($systemString));
                            } else if ($result == SegmentedString::NotEnoughCharacters) {
                                return $this->_haveBufferedCharacterToken();
                            }
                        } else {
                            $this->_parseError();
                            $this->_Token->setForceQuirks();
                            $this->_HTML_ADVANCE_TO(static::BogusDOCTYPEState);
                        }
                    }
                    break;

                case static::AfterDOCTYPEPublicKeywordState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::BeforeDOCTYPEPublicIdentifierState);
                    } else if ($char === '"') {
                        $this->_parseError();
                        $this->_Token->setPublicIdentifierToEmptyString();
                        $this->_HTML_ADVANCE_TO(static::DOCTYPEPublicIdentifierDoubleQuotedState);
                    } else if ($char === '\'') {
                        $this->_parseError();
                        $this->_Token->setPublicIdentifierToEmptyString();
                        $this->_HTML_ADVANCE_TO(static::DOCTYPEPublicIdentifierSingleQuotedState);
                    } else if ($char === '>') {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError(true);
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        $this->_HTML_ADVANCE_TO(static::BogusDOCTYPEState);
                    }
                    break;

                case static::BeforeDOCTYPEPublicIdentifierState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::BeforeDOCTYPEPublicIdentifierState);
                    } else if ($char === '"') {
                        $this->_Token->setPublicIdentifierToEmptyString();
                        $this->_HTML_ADVANCE_TO(static::DOCTYPEPublicIdentifierDoubleQuotedState);
                    } else if ($char === '\'') {
                        $this->_Token->setPublicIdentifierToEmptyString();
                        $this->_HTML_ADVANCE_TO(static::DOCTYPEPublicIdentifierSingleQuotedState);
                    } else if ($char === '>') {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError(true);
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        $this->_HTML_ADVANCE_TO(static::BogusDOCTYPEState);
                    }
                    break;

                case static::DOCTYPEPublicIdentifierDoubleQuotedState:
                    if ($char === '"') {
                        $this->_HTML_ADVANCE_TO(static::AfterDOCTYPEPublicIdentifierState);
                    } else if ($char === '>') {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_Token->appendToPublicIdentifier($char);
                        $this->_HTML_ADVANCE_TO(static::DOCTYPEPublicIdentifierDoubleQuotedState);
                    }
                    break;

                case static::DOCTYPEPublicIdentifierSingleQuotedState:
                    if ($char === '\'') {
                        $this->_HTML_ADVANCE_TO(static::AfterDOCTYPEPublicIdentifierState);
                    } else if ($char === '>') {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_Token->appendToPublicIdentifier($char);
                        $this->_HTML_ADVANCE_TO(static::DOCTYPEPublicIdentifierSingleQuotedState);
                    }
                    break;

                case static::AfterDOCTYPEPublicIdentifierState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::BetweenDOCTYPEPublicAndSystemIdentifiersState);
                    } else if ($char === '>') {
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === '"') {
                        $this->_parseError();
                        $this->_Token->setSystemIdentifierToEmptyString();
                        $this->_HTML_ADVANCE_TO(static::DOCTYPESystemIdentifierDoubleQuotedState);
                    } else if ($char === '\'') {
                        $this->_parseError();
                        $this->_Token->setSystemIdentifierToEmptyString();
                        $this->_HTML_ADVANCE_TO(static::DOCTYPESystemIdentifierSingleQuotedState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        $this->_HTML_ADVANCE_TO(static::BogusDOCTYPEState);
                    }
                    break;

                case static::BetweenDOCTYPEPublicAndSystemIdentifiersState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::BetweenDOCTYPEPublicAndSystemIdentifiersState);
                    } else if ($char === '>') {
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === '"') {
                        $this->_Token->setSystemIdentifierToEmptyString();
                        $this->_HTML_ADVANCE_TO(static::DOCTYPESystemIdentifierDoubleQuotedState);
                    } else if ($char === '\'') {
                        $this->_Token->setSystemIdentifierToEmptyString();
                        $this->_HTML_ADVANCE_TO(static::DOCTYPESystemIdentifierSingleQuotedState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        $this->_HTML_ADVANCE_TO(static::BogusDOCTYPEState);
                    }
                    break;

                case static::AfterDOCTYPESystemKeywordState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::BeforeDOCTYPESystemIdentifierState);
                    } else if ($char === '"') {
                        $this->_parseError();
                        $this->_Token->setSystemIdentifierToEmptyString();
                        $this->_HTML_ADVANCE_TO(static::DOCTYPESystemIdentifierDoubleQuotedState);
                    } else if ($char === '\'') {
                        $this->_parseError();
                        $this->_Token->setSystemIdentifierToEmptyString();
                        $this->_HTML_ADVANCE_TO(static::DOCTYPESystemIdentifierSingleQuotedState);
                    } else if ($char === '>') {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        $this->_HTML_ADVANCE_TO(static::BogusDOCTYPEState);
                    }
                    break;

                case static::BeforeDOCTYPESystemIdentifierState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::BeforeDOCTYPESystemIdentifierState);
                        continue;
                    }
                    if ($char === '"') {
                        $this->_Token->setSystemIdentifierToEmptyString();
                        $this->_HTML_ADVANCE_TO(static::DOCTYPESystemIdentifierDoubleQuotedState);
                    } else if ($char === '\'') {
                        $this->_Token->setSystemIdentifierToEmptyString();
                        $this->_HTML_ADVANCE_TO(static::DOCTYPESystemIdentifierSingleQuotedState);
                    } else if ($char === '>') {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        $this->_HTML_ADVANCE_TO(static::BogusDOCTYPEState);
                    }
                    break;

                case static::DOCTYPESystemIdentifierDoubleQuotedState:
                    if ($char === '"') {
                        $this->_HTML_ADVANCE_TO(static::AfterDOCTYPESystemIdentifierState);
                    } else if ($char === '>') {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_Token->appendToSystemIdentifier($char);
                        $this->_HTML_ADVANCE_TO(static::DOCTYPESystemIdentifierDoubleQuotedState);
                    }
                    break;

                case static::DOCTYPESystemIdentifierSingleQuotedState:
                    if ($char === '\'') {
                        $this->_HTML_ADVANCE_TO(static::AfterDOCTYPESystemIdentifierState);
                    } else if ($char === '>') {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_Token->appendToSystemIdentifier($char);
                        $this->_HTML_ADVANCE_TO(static::DOCTYPESystemIdentifierSingleQuotedState);
                    }
                    break;

                case static::AfterDOCTYPESystemIdentifierState:
                    if ($this->_isTokenizerWhitespace($char)) {
                        $this->_HTML_ADVANCE_TO(static::AfterDOCTYPESystemIdentifierState);
                    } else if ($char === '>') {
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_parseError();
                        $this->_Token->setForceQuirks();
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    } else {
                        $this->_parseError();
                        $this->_HTML_ADVANCE_TO(static::BogusDOCTYPEState);
                    }
                    break;

                case static::BogusDOCTYPEState:
                    if ($char === '>') {
                        return $this->_emitAndResumeIn($source, HTMLTokenizer::DataState);
                    } else if ($char === static::kEndOfFileMarker) {
                        return $this->_emitAndReconsumeIn($source, HTMLTokenizer::DataState);
                    }
                    $this->_HTML_ADVANCE_TO(static::BogusDOCTYPEState);
                    break;

                case static::CDATASectionState:
                    if ($char === ']') {
                        $this->_HTML_ADVANCE_TO(static::CDATASectionRightSquareBracketState);
                    } else if ($char === static::kEndOfFileMarker) {
                        $this->_HTML_RECONSUME_IN(static::DataState);
                    } else {
                        $this->_bufferCharacter($char);
                        $this->_HTML_ADVANCE_TO(static::CDATASectionState);
                    }
                    break;

                case static::CDATASectionRightSquareBracketState:
                    if ($char === ']') {
                        $this->_HTML_ADVANCE_TO(static::CDATASectionDoubleRightSquareBracketState);
                    } else {
                        $this->_bufferCharacter(']');
                        $this->_HTML_RECONSUME_IN(static::CDATASectionState);
                    }
                    break;

                case static::CDATASectionDoubleRightSquareBracketState:
                    if ($char === '>') {
                        $this->_HTML_ADVANCE_TO(static::DataState);
                    } else {
                        $this->_bufferCharacter(']');
                        $this->_bufferCharacter(']');
                        $this->_HTML_RECONSUME_IN(static::CDATASectionState);
                    }
                    break;
            }
        }
        return false;
    }

    protected function _nextInputCharacter($m_state) {
        if ($this->_SegmentedString->eos()) {
            return static::kEndOfFileMarker;
        }
        return $this->_SegmentedString->getCurrentChar();
    }

    protected function _parseError() {
        $this->_Token->parseError();
        $this->_notImplemented();
    }

    protected function _notImplemented() {
        // Source/core/platform/NotImplemented.h
        // logger
    }

    protected function _processEntity(SegmentedString $source) {
        $notEnoughCharacters = false;
        $decodedEntity = array();
        $startPosition = $source->tell();
        $success = $this->_consumeHTMLEntity($source, $decodedEntity, $notEnoughCharacters);
        if ($success === 'notEnoughCharacters') {
            return false;
        }
        $source->seek($startPosition);
        $this->_bufferPop();
        if (!$success) {
            $this->_bufferCharacter('&');
        } else {
            $currentPosition = $source->tell();
            $source->seek($startPosition);
            $decodedEntity = '&' . $source->read($currentPosition - $startPosition);
            $this->_bufferCharacter($decodedEntity);
        }
        return true;
    }

// Source/core/html/parser/HTMLEntityParser.cpp
// consumeHTMLEntity
// Source/core/xml/parser/CharacterReferenceParserInlines.h
// consumeCharacterReference
    protected function _consumeHTMLEntity(SegmentedString $source, $decodedEntity, $notEnoughCharacters, $additionalAllowedCharacter = null) {
        $entityState = 'Initial';
        $result = 0;
        $consumedCharacters = array();
        while (!$source->eos()) {
            $cc = $source->getCurrentChar();
            switch ($entityState) {
                case 'Initial':
                    if ($cc === "\x09" || $cc === "\x0A" || $cc === "\x0C" || $cc === ' ' || $cc === '<' || $cc === '&') return false;
                    if ($additionalAllowedCharacter !== null && $cc === $additionalAllowedCharacter) {
                        return false;
                    }
                    if ($cc === '#') {
                        $entityState = 'Number';
                        break;
                    }
                    if (preg_match('/\A[a-zA-Z]\Z/', $cc)) {
                        $entityState = 'Named';
                        continue;
                    }
                    return false;
                case  'Number':
                    if ($cc == 'x') {
                        $entityState = 'MaybeHexLowerCaseX';
                        break;
                    }
                    if ($cc == 'X') {
                        $entityState = 'MaybeHexUpperCaseX';
                        break;
                    }
                    if (preg_match('/\A[0-9]\Z/', $cc)) {
                        $entityState = 'Decimal';
                        continue;
                    }
                    // source.push('#');
                    $source->advance();
                    return false;
                case 'MaybeHexLowerCaseX':
                    if ($this->_isHexDigit($cc)) {
                        $entityState = 'Hex';
                        continue;
                    }
                    //source.push('#');
                    //source.push('x');
                    $source->advance();
                    $source->advance();
                    return false;
                case 'MaybeHexUpperCaseX':
                    if ($this->_isHexDigit($cc)) {
                        $entityState = 'Hex';
                        continue;
                    }
                    //source.push('#');
                    //source.push('X');
                    $source->advance();
                    $source->advance();
                    return false;
                case 'Hex':
                    if ($this->_isHexDigit($cc)) {
                        if ($result !== 'kInvalidUnicode') {
                            //  result = result * 16 + asHexDigit($char);
                        }
                    } else if ($cc === ';') {
                        // source.advanceAndASSERT($char);
                        // decodedCharacter.append(ParserFunctions::legalEntityFor(result));
                        return true;
                        // } else if (ParserFunctions::acceptMalformed()) {
                        // decodedCharacter.append(ParserFunctions::legalEntityFor(result));
                        //     return true;
                    } else {
                        // unconsumeCharacters(source, consumedCharacters);
                        return false;
                    }
                    break;
                case 'Decimal':
                    if (preg_match('/\A[0-9]\Z/', $cc)) {
                        if ($result !== 'kInvalidUnicode') {
                            // result = result * 10 + cc - '0';
                        }
                    } else if ($cc === ';') {
                        // source.advanceAndASSERT($char);
                        // decodedCharacter.append(ParserFunctions::legalEntityFor(result));
                        $source->advance();
                        return true;
                        // } else if (ParserFunctions::acceptMalformed()) {
                        //    decodedCharacter.append(ParserFunctions::legalEntityFor(result));
                        //     return true;
                    } else {
                        //   unconsumeCharacters(source, consumedCharacters);
                        return false;
                    }
                    break;
                case 'Named':
                    return $this->_consumeNamedEntity($source, $decodedEntity, $notEnoughCharacters, $additionalAllowedCharacter, $cc);
            }
            // UCHAR_MAX_VALUE 0x10ffff
            if ($result > 0x10ffff) {
                $result = 'kInvalidUnicode';
            }
            //  consumedCharacters.append(cc);
            // source.advanceAndASSERT(cc);
            $consumedCharacters[] = $cc;

            $source->advance();
        }
        // $notEnoughCharacters = true;
        return 'notEnoughCharacters';
    }

    protected function _consumeNamedEntity(SegmentedString $source, $decodedEntity, $notEnoughCharacters, $additionalAllowedCharacter, $cc) {
        $pos = $source->tell();

        while (!$source->eos()) {
            $char = $source->read(1);
            if ($char === ';') {
                $source->seek($pos);
                return true;
            } else if (preg_match('/\A[a-zA-Z]\Z/', $char)) {
                continue;
            } else {
                $source->seek($pos);
                return false;
            }
        }
        $source->seek($pos);
        return false;
    }

    protected function _isHexDigit($cc) {
        return preg_match('/\A[0-9a-fA-F]\Z/', $cc);
    }

    protected function _isASCIIUpper($char) {
        return preg_match('/\A[A-Z]\Z/', $char);
    }

    protected function _isASCIILower($char) {
        return preg_match('/\A[a-z]\Z/', $char);
    }

    protected function _temporaryBufferIs($expectedString) {
        return $this->_vectorEqualsString($this->_temporaryBuffer, $expectedString);
    }

    protected function _vectorEqualsString($vector, $string) {
        return $vector === $string;
    }

    protected function _isAppropriateEndTag() {
        if ($this->_bufferedEndTagName === $this->_appropriateEndTagName) {
            // FIXME
            $this->_bufferedEndTagName = '';
            return true;
        }
        return false;
    }

    protected function _emitAndReconsumeIn(SegmentedString $source, $state) {
        $this->_saveEndTagNameIfNeeded();
        $this->_state = $state;
        return true;
    }

    protected function _saveEndTagNameIfNeeded() {
        if ($this->_Token->getType() === HTMLToken::StartTag) {
            $this->_appropriateEndTagName = $this->_Token->getName();
        }
    }

    protected function _emitEndOfFile(SegmentedString $source) {
        if ($this->_haveBufferedCharacterToken()) {
            return true;
        }

        $this->_state = HTMLTokenizer::DataState;
        //source.advanceAndUpdateLineNumber();
        //$this->_Token->clear();
        $this->_Token->makeEndOfFile();
        return true;
    }

    protected function _emitAndResumeIn() {
        $this->_saveEndTagNameIfNeeded();
        //m_state = state;
        $this->_state = static::DataState;
        //source.advanceAndUpdateLineNumber();
        $this->_SegmentedString->advance();
        return true;
    }

    protected function _flushEmitAndResumeIn($source, $state) {
        // m_state = state;
        $this->_state = $state;
        $this->_flushBufferedEndTag($source);
        return true;
    }

    protected function _flushBufferedEndTag($source) {
        $this->_SegmentedString->advance();
        if ($this->_Token->getType() === HTMLToken::Character) {
            return true;
        }
        $this->_Token->beginEndTag($this->_bufferedEndTagName);
        $this->_bufferedEndTagName = '';
        $this->_appropriateEndTagName = '';
        $this->_temporaryBuffer = '';
        return false;
    }


    protected function _haveBufferedCharacterToken() {
        return $this->_Token->getType() === HTMLToken::Character;
    }

    protected function _bufferCharacter($char, $m_state = null) {
        $this->_Token->ensureIsCharacterToken();
        $this->_Token->appendToCharacter($char);
    }

    protected function _bufferPop() {
        array_pop($this->_buffer);
    }

    // todo
    protected function _shouldAllowCDATA() {
        return true;
    }

    protected function _isTokenizerWhitespace($char) {
        return $char === ' ' || $char === "\x0A" || $char === "\x09" || $char === "\x0C";
    }

    protected function _FLUSH_AND_ADVANCE_TO($state) {
        $this->_state = $state;
        if ($this->_flushBufferedEndTag($this->_SegmentedString)) {
            return true;
        }
        // if ( !m_inputStreamPreprocessor.peek(source)) return haveBufferedCharacterToken();
        // cc = m_inputStreamPreprocessor.nextInputCharacter();
    }

    protected function _HTML_RECONSUME_IN($state) {
        $this->_state = $state;
    }

    protected function _HTML_SWITCH_TO($state) {
        $this->_state = $state;
    }

    protected function _HTML_ADVANCE_TO($state) {
        $this->_state = $state;
        $this->_SegmentedString->seek(1, SegmentedString::current);
    }


}
