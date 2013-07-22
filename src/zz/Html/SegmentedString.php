<?php
/**
 * The Blink HTMLTokenizer ported to PHP.
 *
 * Copyright (C) 2004, 2005, 2006, 2007, 2008 Apple Inc. All rights reserved.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Library General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Library General Public License for more details.
 *
 * You should have received a copy of the GNU Library General Public License
 * along with this library; see the file COPYING.LIB.  If not, write to
 * the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA 02110-1301, USA.
 */

namespace zz\Html;

class SegmentedString {
    const ENCODING = 'UTF-8';
    const DidNotMatch = 'DidNotMatch';
    const DidMatch = 'DidMatch';
    const NotEnoughCharacters = 'NotEnoughCharacters';

    const begin = 0;
    const current = 1;
    const end = 2;

    protected $str;
    protected $i = 0;
    protected $len = 0;

    function __construct($str) {
        $this->str = $str;
        $this->len = mb_strlen($str, static::ENCODING);
    }

    public function  getCurrentChar() {
        if ($this->eos()) {
            return false;
        }
        return mb_substr($this->str, $this->i, 1, static::ENCODING);
    }

    public function advance() {
        return $this->seek(1, static::current);
    }

    /**
     * @param int $i
     * @return string
     */
    function read($i) {
        if ($this->eos() && $i > 0) {
            return false;
        }
        $this->i += $i;
        return mb_substr($this->str, $this->i - $i, $i, static::ENCODING);
    }

    function substr($startPos, $endPos) {
        return mb_substr($this->str, $startPos, $endPos, static::ENCODING);
    }

    /**
     * @param int $offset
     * @return bool
     */
    function seek($offset, $whence = self::begin) {
        switch ($whence) {
            case static::begin:
                if ($this->len < $offset) {
                    return false;
                }
                $this->i = $offset;
                return true;
                break;
            case static::current:
                $lookAhead = $this->i + $offset;
                if ($lookAhead < 0 || $lookAhead > $this->len) {
                    return false;
                }
                $this->i = $lookAhead;
                return true;
                break;
        }

        throw new \InvalidArgumentException;
    }

    /**
     * @return int
     */
    function tell() {
        return $this->i;
    }

    /**
     * @return bool
     */
    function eos() {
        return $this->len <= $this->i;
    }

    function get() {
        return $this->str;
    }

    function len() {
        return $this->len;
    }

    function token($str, $caseSensitive = true) {
        $matched = $this->read(mb_strlen($str, static::ENCODING));
        if ($caseSensitive) {
            return $str === $matched ? $str : false;
        } else {
            return strtolower($str) === strtolower($matched) ? $matched : false;
        }
    }

    function lookAheadIgnoringCase($str) {
        return $this->_lookAhead($str, false);
    }

    function lookAhead($str) {
        return $this->_lookAhead($str, true);
    }

    protected function _lookAhead($str, $caseSensitive = true) {
        $i = $this->tell();
        $result = $this->token($str, $caseSensitive) !== false;
        $this->seek($i);
        if (mb_strlen($str, static::ENCODING) + $this->tell() <= $this->len) {
            if ($result) {
                return static::DidMatch;
            }
            return static::DidNotMatch;
        }
        return static::NotEnoughCharacters;
    }

    // int numberOfCharactersConsumed() const { return m_string.length() - m_length; }
    public function numberOfCharactersConsumed() {
        // int numberOfPushedCharacters = 0;
        // if (m_pushedChar1) {
        //     ++numberOfPushedCharacters;
        //     if (m_pushedChar2)
        //         ++numberOfPushedCharacters;
        // }
        // return m_numberOfCharactersConsumedPriorToCurrentString + m_currentString.numberOfCharactersConsumed() - numberOfPushedCharacters;
        return $this->tell();
    }

}
