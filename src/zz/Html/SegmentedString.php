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

    /**
     * @param $str
     */
    public function __construct($str) {
        $this->str = $str;
        $this->len = strlen($str);
    }

    /**
     * @return bool|string
     */
    public function  getCurrentChar() {
        $i = $this->i;
        if ($this->len <= $i) {
            return false;
        }
        return $this->str[$i];
    }

    public function advance() {
        $this->i += 1;
    }

    /**
     * @param int $i
     * @return string
     */
    public function read($i) {
        if ($this->eos() && $i > 0) {
            return false;
        }
        $this->i += $i;
        return substr($this->str, $this->i - $i, $i);
    }

    /**
     * @param int $startPos
     * @param int $length
     * @return string
     */
    public function substr($startPos, $length) {
        return substr($this->str, $startPos, $length);
    }

    /**
     * @param int $offset
     * @param int $whence
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function seek($offset, $whence = self::begin) {
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
    public function tell() {
        return $this->i;
    }

    /**
     * @return bool
     */
    public function eos() {
        return $this->len <= $this->i;
    }

    public function get() {
        return $this->str;
    }

    public function len() {
        return $this->len;
    }

    public function token($str, $caseSensitive = true) {
        $matched = $this->read(strlen($str));
        if ($caseSensitive) {
            return $str === $matched ? $str : false;
        } else {
            return strtolower($str) === strtolower($matched) ? $matched : false;
        }
    }

    public function lookAheadIgnoringCase($str) {
        return $this->_lookAhead($str, false);
    }

    public function lookAhead($str) {
        return $this->_lookAhead($str, true);
    }

    protected function _lookAhead($str, $caseSensitive = true) {
        $i = $this->i;
        $result = $this->token($str, $caseSensitive) !== false;
        $this->seek($i);
        if (strlen($str) + $i <= $this->len) {
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
        return $this->i;
    }

}
