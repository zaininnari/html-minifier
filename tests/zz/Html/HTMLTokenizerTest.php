<?php
namespace zz\Html;
use zz\Html;

class HTMLTokenizerTest extends \PHPUnit_Framework_TestCase {

    public function testSetState() {
        $html = '';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $expect = HTMLTokenizer::RAWTEXTState;
        $HTMLTokenizer->setState($expect);
        $actual = $HTMLTokenizer->getState();
        $this->assertEquals($expect, $actual);
    }

    public function testGetState() {
        $html = '';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $actual = $HTMLTokenizer->getState();
        $expect = HTMLTokenizer::DataState;
        $this->assertEquals($expect, $actual);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidState() {
        $html = 'text';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->setState('');
        $HTMLTokenizer->tokenizer();
    }

    public function testEmpty() {
        $html = '';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array();
        $this->assertEquals($expect, $actual);

        $html = '    ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Character',
                'data' => '    ',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '    ',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testSingleChar() {
        $html = 'a';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Character',
                'data' => 'a',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => 'a',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = 'ab';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Character',
                'data' => 'ab',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => 'ab',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    protected static function getHtml($tokens) {
        $html = '';
        foreach ($tokens as $token) {
            foreach ($token['state'] as $state) {
                $html .= $state[1];
            }
        }
        return $html;
    }

    public function testDoctype() {
        // HTML 5
        $source = '<!DOCTYPE html>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => false,
                    'mode' => 'NoQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // HTML 4.01 Strict
        $source = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    49 => 'AfterDOCTYPEPublicIdentifierState',
                    50 => 'BetweenDOCTYPEPublicAndSystemIdentifiersState',
                    51 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                    89 => 'AfterDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '-//W3C//DTD HTML 4.01//EN',
                    'systemIdentifier' => 'http://www.w3.org/TR/html4/strict.dtd',
                    'forceQuirks' => false,
                    'mode' => 'NoQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // HTML 4.01 Transitional
        $source = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    62 => 'AfterDOCTYPEPublicIdentifierState',
                    63 => 'BetweenDOCTYPEPublicAndSystemIdentifiersState',
                    64 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                    101 => 'AfterDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '-//W3C//DTD HTML 4.01 Transitional//EN',
                    'systemIdentifier' => 'http://www.w3.org/TR/html4/loose.dtd',
                    'forceQuirks' => false,
                    'mode' => 'LimitedQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // HTML 4.01 Frameset
        $source = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    58 => 'AfterDOCTYPEPublicIdentifierState',
                    59 => 'BetweenDOCTYPEPublicAndSystemIdentifiersState',
                    60 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                    100 => 'AfterDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '-//W3C//DTD HTML 4.01 Frameset//EN',
                    'systemIdentifier' => 'http://www.w3.org/TR/html4/frameset.dtd',
                    'forceQuirks' => false,
                    'mode' => 'LimitedQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // XHTML 1.0 Strict
        $source = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    56 => 'AfterDOCTYPEPublicIdentifierState',
                    57 => 'BetweenDOCTYPEPublicAndSystemIdentifiersState',
                    58 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                    108 => 'AfterDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '-//W3C//DTD XHTML 1.0 Strict//EN',
                    'systemIdentifier' => 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd',
                    'forceQuirks' => false,
                    'mode' => 'NoQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // XHTML 1.0 Transitional
        $source = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    62 => 'AfterDOCTYPEPublicIdentifierState',
                    63 => 'BetweenDOCTYPEPublicAndSystemIdentifiersState',
                    64 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                    120 => 'AfterDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '-//W3C//DTD XHTML 1.0 Transitional//EN',
                    'systemIdentifier' => 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd',
                    'forceQuirks' => false,
                    'mode' => 'LimitedQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // XHTML 1.0 Frameset
        $source = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    58 => 'AfterDOCTYPEPublicIdentifierState',
                    59 => 'BetweenDOCTYPEPublicAndSystemIdentifiersState',
                    60 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                    112 => 'AfterDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '-//W3C//DTD XHTML 1.0 Frameset//EN',
                    'systemIdentifier' => 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd',
                    'forceQuirks' => false,
                    'mode' => 'LimitedQuirksMode',
                ),

            ),
        );
        $this->assertEquals($expect, $actual);

        // XHTML 1.1
        $source = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    49 => 'AfterDOCTYPEPublicIdentifierState',
                    50 => 'BetweenDOCTYPEPublicAndSystemIdentifiersState',
                    51 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                    96 => 'AfterDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '-//W3C//DTD XHTML 1.1//EN',
                    'systemIdentifier' => 'http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd',
                    'forceQuirks' => false,
                    'mode' => 'NoQuirksMode',
                ),

            ),
        );
        $this->assertEquals($expect, $actual);

        // memo
        $source = '<!DOCTYPE memo>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'memo',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => false,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // +//Silmaril//dtd html Pro v0r11 19970101//
        $source = '<!DOCTYPE HTML PUBLIC "+//Silmaril//dtd html Pro v0r11 19970101//">';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!DOCTYPE HTML PUBLIC "+//Silmaril//dtd html Pro v0r11 19970101//">',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    66 => 'AfterDOCTYPEPublicIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '+//Silmaril//dtd html Pro v0r11 19970101//',
                    'systemIdentifier' => '',
                    'forceQuirks' => false,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // -//W3C//DTD HTML 4.01 Transitional//
        $source = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//">';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//">',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    60 => 'AfterDOCTYPEPublicIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '-//W3C//DTD HTML 4.01 Transitional//',
                    'systemIdentifier' => '',
                    'forceQuirks' => false,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testTagBlock() {
        $html = '<p>a</p>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<p>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => 'a',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => 'a',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
            2 => array(
                'type' => 'EndTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '</p>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'EndTagOpenState',
                    3 => 'TagNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = '<P>a</P>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<P>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => 'a',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => 'a',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
            2 => array(
                'type' => 'EndTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '</P>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'EndTagOpenState',
                    3 => 'TagNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testEntityDecimal() {
        $html = '&#34;';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Character',
                'data' => '&#34;',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '&#34;',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testEntityNamed() {
        $html = '&amp;';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Character',
                'data' => '&amp;',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '&amp;',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = '<p id="&a';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<p id="&a',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => '&a',
                        'quoted' => false,
                    ),
                ),
                'parseError' => true,
                'html' => '<p id="&a',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    6 => 'BeforeAttributeValueState',
                    7 => 'AttributeValueDoubleQuotedState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = '<p id="&amp;">';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => '&amp;',
                        'quoted' => '"',
                    ),
                ),
                'parseError' => false,
                'html' => '<p id="&amp;">',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    6 => 'BeforeAttributeValueState',
                    7 => 'AttributeValueDoubleQuotedState',
                    13 => 'AfterAttributeValueQuotedState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = '<p id="aaaaa&amp;aaaa">';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => 'aaaaa&amp;aaaa',
                        'quoted' => '"',
                    ),
                ),
                'parseError' => false,
                'html' => '<p id="aaaaa&amp;aaaa">',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    6 => 'BeforeAttributeValueState',
                    7 => 'AttributeValueDoubleQuotedState',
                    22 => 'AfterAttributeValueQuotedState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testEntityInvalid() {
        $html = $source = '&';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '&',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '&',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '&&';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '&&',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '&&',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '&&&';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '&&&',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '&&&',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testEntityDecimalInvalid() {
        $html = '&#34';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Character',
                'data' => '&#34',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '&#34',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testTag() {
        $html = '<a href="http://example.com/">link</a>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'a',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'href',
                        'value' => 'http://example.com/',
                        'quoted' => '"',
                    ),
                ),
                'parseError' => false,
                'html' => '<a href="http://example.com/">',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    8 => 'BeforeAttributeValueState',
                    9 => 'AttributeValueDoubleQuotedState',
                    29 => 'AfterAttributeValueQuotedState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => 'link',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => 'link',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
            2 => array(
                'type' => 'EndTag',
                'data' => 'a',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '</a>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'EndTagOpenState',
                    3 => 'TagNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = '<I/>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'i',
                'selfClosing' => true,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<I/>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'SelfClosingStartTagState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

    }


    public function testComment() {
        $html = '<!-- comment -->';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!-- comment -->',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!-- comment -->',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                    5 => 'CommentState',
                    14 => 'CommentEndDashState',
                    15 => 'CommentEndState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testCDATA() {
        $html = '<![CDATA[' . chr(10) . '  var i = 0;' . chr(10) . ' //]]>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Character',
                'data' => $html,
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => $html,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'CDATASectionState',
                    27 => 'CDATASectionRightSquareBracketState',
                    28 => 'CDATASectionDoubleRightSquareBracketState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testConditionalComment() {
        $source = '<!--[if expression]>' . chr(10) . '          HTML <![endif]-->';
        $SegmentedString = new SegmentedString($source);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => $source,
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                    5 => 'CommentState',
                    46 => 'CommentEndDashState',
                    47 => 'CommentEndState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<![if expression]> HTML <![endif]>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<![if expression]>',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<![if expression]>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'ContinueBogusCommentState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => ' HTML ',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => ' HTML ',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
            2 => array(
                'type' => 'Comment',
                'data' => '<![endif]>',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<![endif]>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'ContinueBogusCommentState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testTagImg() {
        $html = $source = '<img id="test" src="img" />';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'img',
                'selfClosing' => true,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => 'test',
                        'quoted' => '"',
                    ),
                    1 => array(
                        'name' => 'src',
                        'value' => 'img',
                        'quoted' => '"',
                    ),
                ),
                'parseError' => false,
                'html' => '<img id="test" src="img" />',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    5 => 'BeforeAttributeNameState',
                    6 => 'AttributeNameState',
                    8 => 'BeforeAttributeValueState',
                    9 => 'AttributeValueDoubleQuotedState',
                    14 => 'AfterAttributeValueQuotedState',
                    15 => 'BeforeAttributeNameState',
                    16 => 'AttributeNameState',
                    19 => 'BeforeAttributeValueState',
                    20 => 'AttributeValueDoubleQuotedState',
                    24 => 'AfterAttributeValueQuotedState',
                    25 => 'BeforeAttributeNameState',
                    26 => 'SelfClosingStartTagState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testEndOfFile() {
        $html = $source = '<style>end';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'style',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<style>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => 'end',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => 'end',
                'state' => array(
                    0 => 'RAWTEXTState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script>end';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => 'end',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => 'end',
                'state' => array(
                    0 => 'ScriptDataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<PLAINTEXT>end';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'plaintext',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<PLAINTEXT>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => 'end',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => 'end',
                'state' => array(
                    0 => 'PLAINTEXTState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<DIV>end' . '</';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'div',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<DIV>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => 'end',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => 'end',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
            2 => array(
                'type' => 'Character',
                'data' => '</',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '</',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script><!-- ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '<!-- ',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!-- ',
                'state' => array(
                    0 => 'ScriptDataState',
                    1 => 'ScriptDataLessThanSignState',
                    2 => 'ScriptDataEscapeStartState',
                    3 => 'ScriptDataEscapeStartDashState',
                    4 => 'ScriptDataEscapedDashDashState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script><!--a-';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '<!--a-',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!--a-',
                'state' => array(
                    0 => 'ScriptDataState',
                    1 => 'ScriptDataLessThanSignState',
                    2 => 'ScriptDataEscapeStartState',
                    3 => 'ScriptDataEscapeStartDashState',
                    4 => 'ScriptDataEscapedDashDashState',
                    5 => 'ScriptDataEscapedState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script><!--';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '<!--',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!--',
                'state' => array(
                    0 => 'ScriptDataState',
                    1 => 'ScriptDataLessThanSignState',
                    2 => 'ScriptDataEscapeStartState',
                    3 => 'ScriptDataEscapeStartDashState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script><!--<script ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '<!--<script ',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!--<script ',
                'state' => array(
                    0 => 'ScriptDataState',
                    1 => 'ScriptDataLessThanSignState',
                    2 => 'ScriptDataEscapeStartState',
                    3 => 'ScriptDataEscapeStartDashState',
                    4 => 'ScriptDataEscapedDashDashState',
                    5 => 'ScriptDataEscapedLessThanSignState',
                    6 => 'ScriptDataDoubleEscapeStartState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script><!--<script -';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '<!--<script -',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!--<script -',
                'state' => array(
                    0 => 'ScriptDataState',
                    1 => 'ScriptDataLessThanSignState',
                    2 => 'ScriptDataEscapeStartState',
                    3 => 'ScriptDataEscapeStartDashState',
                    4 => 'ScriptDataEscapedDashDashState',
                    5 => 'ScriptDataEscapedLessThanSignState',
                    6 => 'ScriptDataDoubleEscapeStartState',
                    12 => 'ScriptDataDoubleEscapedState',
                ),

            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script><!--<script --';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '<!--<script --',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!--<script --',
                'state' => array(
                    0 => 'ScriptDataState',
                    1 => 'ScriptDataLessThanSignState',
                    2 => 'ScriptDataEscapeStartState',
                    3 => 'ScriptDataEscapeStartDashState',
                    4 => 'ScriptDataEscapedDashDashState',
                    5 => 'ScriptDataEscapedLessThanSignState',
                    6 => 'ScriptDataDoubleEscapeStartState',
                    12 => 'ScriptDataDoubleEscapedState',
                    13 => 'ScriptDataDoubleEscapedDashState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<p ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<p ',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<p ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<p i';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<p i',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'i',
                        'value' => '',
                        'quoted' => false,
                    ),
                ),
                'parseError' => true,
                'html' => '<p i',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<p id ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<p id ',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => '',
                        'quoted' => false,
                    ),
                ),
                'parseError' => true,
                'html' => '<p id ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<p id=';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<p id=',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => '',
                        'quoted' => false,
                    ),
                ),
                'parseError' => true,
                'html' => '<p id=',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<p id="';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<p id="',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => '',
                        'quoted' => false,
                    ),
                ),
                'parseError' => true,
                'html' => '<p id="',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    6 => 'BeforeAttributeValueState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<p id=\'';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<p id=\'',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => '',
                        'quoted' => false,
                    ),
                ),
                'parseError' => true,
                'html' => '<p id=\'',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    6 => 'BeforeAttributeValueState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<p id="id"';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<p id="id"',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => 'id',
                        'quoted' => '"',
                    ),
                ),
                'parseError' => true,
                'html' => '<p id="id"',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    6 => 'BeforeAttributeValueState',
                    7 => 'AttributeValueDoubleQuotedState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<img id="id"/';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<img id="id"/',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => 'id',
                        'quoted' => '"',
                    ),
                ),
                'parseError' => true,
                'html' => '<img id="id"/',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    5 => 'BeforeAttributeNameState',
                    6 => 'AttributeNameState',
                    8 => 'BeforeAttributeValueState',
                    9 => 'AttributeValueDoubleQuotedState',
                    12 => 'AfterAttributeValueQuotedState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!-';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Uninitialized',
                'data' => '<!',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '-',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '-',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!--';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!--',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!--',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!-->';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!-->',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!-->',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!---';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!---',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!---',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!--->';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!--->',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!--->',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                    5 => 'CommentStartDashState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!----';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!----',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!----',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                    5 => 'CommentStartDashState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!----!';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!----!',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!----!',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                    5 => 'CommentStartDashState',
                    6 => 'CommentEndState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => '',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => '',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE P';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE P',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE P ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE P ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE html PUBLIC';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html PUBLIC',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE html PUBLIC ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html PUBLIC ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE html PUBLIC "';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html PUBLIC "',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE html PUBLIC \'';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html PUBLIC \'',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE html PUBLIC ""';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html PUBLIC ""',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE html PUBLIC "" ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html PUBLIC "" ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    24 => 'AfterDOCTYPEPublicIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE memo SYSTEM';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'memo',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE memo SYSTEM',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE memo SYSTEM ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'memo',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE memo SYSTEM ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE memo SYSTEM "';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'memo',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE memo SYSTEM "',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                    22 => 'BeforeDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE memo SYSTEM \'';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'memo',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE memo SYSTEM \'',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                    22 => 'BeforeDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE memo SYSTEM ""';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'memo',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE memo SYSTEM ""',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                    22 => 'BeforeDOCTYPESystemIdentifierState',
                    23 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE memo SYSTEM "http://www.4dd.co.jp/DTD/memo.dtd"';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'memo',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE memo SYSTEM "http://www.4dd.co.jp/DTD/memo.dtd"',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                    22 => 'BeforeDOCTYPESystemIdentifierState',
                    23 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => 'http://www.4dd.co.jp/DTD/memo.dtd',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!DOCTYPE memo D"';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'memo',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE memo D"',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    16 => 'BogusDOCTYPEState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<![CDATA[';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<![CDATA[',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<![CDATA[',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<a';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<a',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<a',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<!-- -';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!-- -',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!-- -',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                    5 => 'CommentState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testParseError() {
        // TagOpenState
        $html = $source = '<<?';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Character',
                'data' => '<',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
            1 => array(
                'type' => 'Comment',
                'data' => '<?',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<?',
                'state' => array(
                    0 => 'DataState',
                    1 => 'ContinueBogusCommentState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // TagOpenState
        $html = $source = '<<?';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Character',
                'data' => '<',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
            1 => array(
                'type' => 'Comment',
                'data' => '<?',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<?',
                'state' => array(
                    0 => 'DataState',
                    1 => 'ContinueBogusCommentState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // EndTagOpenState
        $html = $source = '</>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '</>',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '</>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'EndTagOpenState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // EndTagOpenState
        $html = $source = '<//';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<//',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<//',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'ContinueBogusCommentState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // AfterAttributeNameState
        $html = $source = '<IMG SRC "="img.png">';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'img',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'src',
                        'value' => '',
                        'quoted' => false,
                    ),
                    1 => array(
                        'name' => '"',
                        'value' => 'img.png',
                        'quoted' => '"',
                    ),
                ),
                'parseError' => true,
                'html' => '<IMG SRC "="img.png">',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    5 => 'BeforeAttributeNameState',
                    6 => 'AttributeNameState',
                    9 => 'AfterAttributeNameState',
                    10 => 'AttributeNameState',
                    11 => 'BeforeAttributeValueState',
                    12 => 'AttributeValueDoubleQuotedState',
                    20 => 'AfterAttributeValueQuotedState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // BeforeAttributeValueState
        $html = $source = '<IMG SRC=>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'img',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'src',
                        'value' => '',
                        'quoted' => false,
                    ),
                ),
                'parseError' => true,
                'html' => '<IMG SRC=>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    5 => 'BeforeAttributeNameState',
                    6 => 'AttributeNameState',
                    9 => 'BeforeAttributeValueState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // BeforeAttributeValueState
        $html = $source = '<IMG SRC=<"';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<IMG SRC=<"',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'src',
                        'value' => '<"',
                        'quoted' => false,
                    ),
                ),
                'parseError' => true,
                'html' => '<IMG SRC=<"',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    5 => 'BeforeAttributeNameState',
                    6 => 'AttributeNameState',
                    9 => 'BeforeAttributeValueState',
                    10 => 'AttributeValueUnquotedState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // AfterAttributeValueQuotedState
        $html = $source = '<IMG SRC="""';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<IMG SRC="""',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'src',
                        'value' => '',
                        'quoted' => '"',
                    ),
                    1 => array(
                        'name' => '"',
                        'value' => '',
                        'quoted' => false,
                    ),
                ),
                'parseError' => true,
                'html' => '<IMG SRC="""',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    5 => 'BeforeAttributeNameState',
                    6 => 'AttributeNameState',
                    9 => 'BeforeAttributeValueState',
                    10 => 'AttributeValueDoubleQuotedState',
                    11 => 'BeforeAttributeNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // SelfClosingStartTagState
        $html = $source = '<IMG SRC=""/ ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'EndOfFile',
                'data' => '<IMG SRC=""/ ',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'src',
                        'value' => '',
                        'quoted' => '"',
                    ),
                ),
                'parseError' => true,
                'html' => '<IMG SRC=""/ ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    5 => 'BeforeAttributeNameState',
                    6 => 'AttributeNameState',
                    9 => 'BeforeAttributeValueState',
                    10 => 'AttributeValueDoubleQuotedState',
                    11 => 'AfterAttributeValueQuotedState',
                    12 => 'BeforeAttributeNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // CommentEndState
        $html = $source = '<!-- ---';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!-- ---',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!-- ---',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                    5 => 'CommentState',
                    6 => 'CommentEndDashState',
                    7 => 'CommentEndState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // CommentEndState
        $html = $source = '<!-- --<';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!-- --<',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!-- --<',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                    5 => 'CommentState',
                    6 => 'CommentEndDashState',
                    7 => 'CommentEndState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // DOCTYPEState
        $html = $source = '<!DOCTYPE_';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => '_',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE_',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'BeforeDOCTYPENameState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // BeforeDOCTYPENameState
        $html = $source = '<!DOCTYPE >';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => '',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE >',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // BeforeDOCTYPENameState
        $html = $source = '<!DOCTYPE HTML PUBLIC"';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE HTML PUBLIC"',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // BeforeDOCTYPENameState
        $html = $source = '<!DOCTYPE HTML PUBLIC\'';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE HTML PUBLIC\'',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // BeforeDOCTYPENameState
        $html = $source = '<!DOCTYPE HTML PUBLIC>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE HTML PUBLIC>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // BeforeDOCTYPENameState
        $html = $source = '<!DOCTYPE HTML PUBLIC_';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE HTML PUBLIC_',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // BeforeDOCTYPEPublicIdentifierState
        $html = $source = '<!DOCTYPE HTML PUBLIC >';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE HTML PUBLIC >',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // BeforeDOCTYPEPublicIdentifierState
        $html = $source = '<!DOCTYPE HTML PUBLIC _';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE HTML PUBLIC _',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // DOCTYPEPublicIdentifierDoubleQuotedState
        $html = $source = '<!DOCTYPE HTML PUBLIC ">';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE HTML PUBLIC ">',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // DOCTYPEPublicIdentifierSingleQuotedState
        $html = $source = '<!DOCTYPE HTML PUBLIC \'>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE HTML PUBLIC \'>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierSingleQuotedState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // AfterDOCTYPEPublicIdentifierState
        $html = $source = '<!DOCTYPE html PUBLIC """';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html PUBLIC """',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    24 => 'AfterDOCTYPEPublicIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // AfterDOCTYPEPublicIdentifierState
        $html = $source = '<!DOCTYPE html PUBLIC ""\'';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html PUBLIC ""\'',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    24 => 'AfterDOCTYPEPublicIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // AfterDOCTYPEPublicIdentifierState
        $html = $source = '<!DOCTYPE html PUBLIC ""-';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html PUBLIC ""-',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    24 => 'AfterDOCTYPEPublicIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // BetweenDOCTYPEPublicAndSystemIdentifiersState
        $html = $source = '<!DOCTYPE html PUBLIC "" -';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html PUBLIC "" -',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    24 => 'AfterDOCTYPEPublicIdentifierState',
                    25 => 'BetweenDOCTYPEPublicAndSystemIdentifiersState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // AfterDOCTYPESystemKeywordState
        $html = $source = '<!DOCTYPE html SYSTEM"';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html SYSTEM"',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // AfterDOCTYPESystemKeywordState
        $html = $source = '<!DOCTYPE html SYSTEM\'';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html SYSTEM\'',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // AfterDOCTYPESystemKeywordState
        $html = $source = '<!DOCTYPE html SYSTEM>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html SYSTEM>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // AfterDOCTYPESystemKeywordState
        $html = $source = '<!DOCTYPE html SYSTEM-';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html SYSTEM-',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // BeforeDOCTYPESystemIdentifierState
        $html = $source = '<!DOCTYPE html SYSTEM >';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html SYSTEM >',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                    22 => 'BeforeDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // BeforeDOCTYPESystemIdentifierState
        $html = $source = '<!DOCTYPE html SYSTEM -';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html SYSTEM -',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                    22 => 'BeforeDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // DOCTYPESystemIdentifierDoubleQuotedState
        $html = $source = '<!DOCTYPE html SYSTEM ">';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html SYSTEM ">',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                    22 => 'BeforeDOCTYPESystemIdentifierState',
                    23 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // DOCTYPESystemIdentifierDoubleQuotedState
        $html = $source = '<!DOCTYPE html SYSTEM \'>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html SYSTEM \'>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                    22 => 'BeforeDOCTYPESystemIdentifierState',
                    23 => 'DOCTYPESystemIdentifierSingleQuotedState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        // AfterDOCTYPESystemIdentifierState
        $html = $source = '<!DOCTYPE html SYSTEM ""-';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html SYSTEM ""-',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                    22 => 'BeforeDOCTYPESystemIdentifierState',
                    23 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                    24 => 'AfterDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => false,
                    'mode' => 'NoQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testRAWTEXTState() {
        $html = $source = '<style>  /**/  </style>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'style',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<style>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  ',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  ',
                'state' => array(
                    0 => 'RAWTEXTState',
                ),
            ),
            2 => array(
                'type' => 'EndTag',
                'data' => 'style',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '</style>',
                'state' => array(
                    0 => 'RAWTEXTState',
                    1 => 'RAWTEXTLessThanSignState',
                    2 => 'RAWTEXTEndTagOpenState',
                    3 => 'RAWTEXTEndTagNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<STYLE>  /**/  </STYLE>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'style',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<STYLE>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  ',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  ',
                'state' => array(
                    0 => 'RAWTEXTState',
                ),
            ),
            2 => array(
                'type' => 'EndTag',
                'data' => 'style',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '</STYLE>',
                'state' => array(
                    0 => 'RAWTEXTState',
                    1 => 'RAWTEXTLessThanSignState',
                    2 => 'RAWTEXTEndTagOpenState',
                    3 => 'RAWTEXTEndTagNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<style>  /**/  </>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'style',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<style>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  </>',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  </>',
                'state' => array(
                    0 => 'RAWTEXTState',
                    9 => 'RAWTEXTLessThanSignState',
                    10 => 'RAWTEXTState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<style>  /**/  <a';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'style',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<style>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  <a',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  <a',
                'state' => array(
                    0 => 'RAWTEXTState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<style>  /**/  </stylea>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'style',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<style>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  </stylea>',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  </stylea>',
                'state' => array(
                    0 => 'RAWTEXTState',
                    9 => 'RAWTEXTLessThanSignState',
                    10 => 'RAWTEXTEndTagOpenState',
                    11 => 'RAWTEXTEndTagNameState',
                    16 => 'RAWTEXTState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<style>  /**/  </a_';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'style',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<style>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  </a_',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  </a_',
                'state' => array(
                    0 => 'RAWTEXTState',
                    9 => 'RAWTEXTLessThanSignState',
                    10 => 'RAWTEXTEndTagOpenState',
                    11 => 'RAWTEXTState'
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testScriptDataState() {
        $html = $source = '<script>  /**/  </script>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  ',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  ',
                'state' => array(
                    0 => 'ScriptDataState',
                ),
            ),
            2 => array(
                'type' => 'EndTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '</script>',
                'state' => array(
                    0 => 'ScriptDataState',
                    1 => 'ScriptDataLessThanSignState',
                    2 => 'ScriptDataEndTagOpenState',
                    3 => 'ScriptDataEndTagNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<SCRIPT>  /**/  </SCRIPT>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<SCRIPT>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  ',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  ',
                'state' => array(
                    0 => 'ScriptDataState',
                ),
            ),
            2 => array(
                'type' => 'EndTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '</SCRIPT>',
                'state' => array(
                    0 => 'ScriptDataState',
                    1 => 'ScriptDataLessThanSignState',
                    2 => 'ScriptDataEndTagOpenState',
                    3 => 'ScriptDataEndTagNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script>  /**/  </>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  </>',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  </>',
                'state' => array(
                    0 => 'ScriptDataState',
                    9 => 'ScriptDataLessThanSignState',
                    10 => 'ScriptDataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script>  /**/  <a';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  <a',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  <a',
                'state' => array(
                    0 => 'ScriptDataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script>  /**/  </scripta>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  </scripta>',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  </scripta>',
                'state' => array(
                    0 => 'ScriptDataState',
                    9 => 'ScriptDataLessThanSignState',
                    10 => 'ScriptDataEndTagOpenState',
                    11 => 'ScriptDataEndTagNameState',
                    17 => 'ScriptDataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script>  /**/  </a_';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  </a_',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  </a_',
                'state' => array(
                    0 => 'ScriptDataState',
                    9 => 'ScriptDataLessThanSignState',
                    10 => 'ScriptDataEndTagOpenState',
                    11 => 'ScriptDataState'
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testRCDATAState() {
        $html = $source = '<title>  /**/  </title>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'title',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<title>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  ',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  ',
                'state' => array(
                    0 => 'RCDATAState',
                ),
            ),
            2 => array(
                'type' => 'EndTag',
                'data' => 'title',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '</title>',
                'state' => array(
                    0 => 'RCDATAState',
                    1 => 'RCDATALessThanSignState',
                    2 => 'RCDATAEndTagOpenState',
                    3 => 'RCDATAEndTagNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<TITLE>  /**/  </TITLE>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'title',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<TITLE>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  ',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  ',
                'state' => array(
                    0 => 'RCDATAState',
                ),
            ),
            2 => array(
                'type' => 'EndTag',
                'data' => 'title',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '</TITLE>',
                'state' => array(
                    0 => 'RCDATAState',
                    1 => 'RCDATALessThanSignState',
                    2 => 'RCDATAEndTagOpenState',
                    3 => 'RCDATAEndTagNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<title>  /**/  ' . '</>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'title',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<title>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  </>',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  </>',
                'state' => array(
                    0 => 'RCDATAState',
                    9 => 'RCDATALessThanSignState',
                    10 => 'RCDATAState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<title>  /**/  <a';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'title',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<title>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  <a',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  <a',
                'state' => array(
                    0 => 'RCDATAState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<title>  /**/  </titlea>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'title',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<title>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  </titlea>',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  </titlea>',
                'state' => array(
                    0 => 'RCDATAState',
                    9 => 'RCDATALessThanSignState',
                    10 => 'RCDATAEndTagOpenState',
                    11 => 'RCDATAEndTagNameState',
                    16 => 'RCDATAState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<title>  /**/  </a_';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'title',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<title>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '  /**/  </a_',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '  /**/  </a_',
                'state' => array(
                    0 => 'RCDATAState',
                    9 => 'RCDATALessThanSignState',
                    10 => 'RCDATAEndTagOpenState',
                    11 => 'RCDATAState'
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testNestDataState() {
        $html = $source = '<textarea><script> var Hello = \'world\';</script></textarea>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'textarea',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<textarea>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '<script> var Hello = \'world\';</script>',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script> var Hello = \'world\';</script>',
                'state' => array(
                    0 => 'RCDATAState',
                    30 => 'RCDATALessThanSignState',
                    31 => 'RCDATAEndTagOpenState',
                    32 => 'RCDATAEndTagNameState',
                    37 => 'RCDATAState',
                ),
            ),
            2 => array(
                'type' => 'EndTag',
                'data' => 'textarea',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '</textarea>',
                'state' => array(
                    0 => 'RCDATAState',
                    1 => 'RCDATALessThanSignState',
                    2 => 'RCDATAEndTagOpenState',
                    3 => 'RCDATAEndTagNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testRAWTEXT_FLUSH_AND_ADVANCE_TO() {
        $html = $source = '<style></style>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'style',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</style>',
            'state' => array(
                0 => 'RAWTEXTState',
                1 => 'RAWTEXTLessThanSignState',
                2 => 'RAWTEXTEndTagOpenState',
                3 => 'RAWTEXTEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<style></style >';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'style',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</style >',
            'state' => array(
                0 => 'RAWTEXTState',
                1 => 'RAWTEXTLessThanSignState',
                2 => 'RAWTEXTEndTagOpenState',
                3 => 'RAWTEXTEndTagNameState',
                8 => 'BeforeAttributeNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<style>text</style >';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'style',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</style >',
            'state' => array(
                0 => 'RAWTEXTState',
                1 => 'RAWTEXTLessThanSignState',
                2 => 'RAWTEXTEndTagOpenState',
                3 => 'RAWTEXTEndTagNameState',
                8 => 'BeforeAttributeNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<style>' . '</style ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndOfFile',
            'data' => '</style ',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => '</style ',
            'state' => array(
                0 => 'RAWTEXTState',
                1 => 'RAWTEXTLessThanSignState',
                2 => 'RAWTEXTEndTagOpenState',
                3 => 'RAWTEXTEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<style>' . '</style/>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'style',
            'selfClosing' => true,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</style/>',
            'state' => array(
                0 => 'RAWTEXTState',
                1 => 'RAWTEXTLessThanSignState',
                2 => 'RAWTEXTEndTagOpenState',
                3 => 'RAWTEXTEndTagNameState',
                8 => 'SelfClosingStartTagState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<style>text' . '</style/>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'style',
            'selfClosing' => true,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</style/>',
            'state' => array(
                0 => 'RAWTEXTState',
                1 => 'RAWTEXTLessThanSignState',
                2 => 'RAWTEXTEndTagOpenState',
                3 => 'RAWTEXTEndTagNameState',
                8 => 'SelfClosingStartTagState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<style>' . '</style/';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndOfFile',
            'data' => '</style/',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => '</style/',
            'state' => array(
                0 => 'RAWTEXTState',
                1 => 'RAWTEXTLessThanSignState',
                2 => 'RAWTEXTEndTagOpenState',
                3 => 'RAWTEXTEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testScript_FLUSH_AND_ADVANCE_TO() {
        $html = $source = '<script></script>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'script',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</script>',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script></script >';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'script',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</script >',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
                9 => 'BeforeAttributeNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script>text</script >';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'script',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</script >',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
                9 => 'BeforeAttributeNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script>' . '</script ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndOfFile',
            'data' => '</script ',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => '</script ',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script>' . '</script/>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'script',
            'selfClosing' => true,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</script/>',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
                9 => 'SelfClosingStartTagState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script>text' . '</script/>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'script',
            'selfClosing' => true,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</script/>',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
                9 => 'SelfClosingStartTagState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script>' . '</script/';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndOfFile',
            'data' => '</script/',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => '</script/',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testScriptEscaped_FLUSH_AND_ADVANCE_TO() {
        $html = $source = '<script><!--</script>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'script',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</script>',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script><!--</script >';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'script',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</script >',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
                9 => 'BeforeAttributeNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script>text<!--</script >';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'script',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</script >',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
                9 => 'BeforeAttributeNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script><!--' . '</script ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndOfFile',
            'data' => '</script ',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => '</script ',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script><!--' . '</script/>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'script',
            'selfClosing' => true,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</script/>',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
                9 => 'SelfClosingStartTagState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script>text<!--' . '</script/>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'script',
            'selfClosing' => true,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</script/>',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
                9 => 'SelfClosingStartTagState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<script><!--' . '</script/';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndOfFile',
            'data' => '</script/',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => '</script/',
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEndTagOpenState',
                3 => 'ScriptDataEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testRCDATA_FLUSH_AND_ADVANCE_TO() {
        $html = $source = '<textarea></textarea>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'textarea',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</textarea>',
            'state' => array(
                0 => 'RCDATAState',
                1 => 'RCDATALessThanSignState',
                2 => 'RCDATAEndTagOpenState',
                3 => 'RCDATAEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<textarea></textarea >';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'textarea',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</textarea >',
            'state' => array(
                0 => 'RCDATAState',
                1 => 'RCDATALessThanSignState',
                2 => 'RCDATAEndTagOpenState',
                3 => 'RCDATAEndTagNameState',
                11 => 'BeforeAttributeNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<textarea>text</textarea >';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'textarea',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</textarea >',
            'state' => array(
                0 => 'RCDATAState',
                1 => 'RCDATALessThanSignState',
                2 => 'RCDATAEndTagOpenState',
                3 => 'RCDATAEndTagNameState',
                11 => 'BeforeAttributeNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<textarea>' . '</textarea ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndOfFile',
            'data' => '</textarea ',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => '</textarea ',
            'state' => array(
                0 => 'RCDATAState',
                1 => 'RCDATALessThanSignState',
                2 => 'RCDATAEndTagOpenState',
                3 => 'RCDATAEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<textarea>' . '</textarea/>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'textarea',
            'selfClosing' => true,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</textarea/>',
            'state' => array(
                0 => 'RCDATAState',
                1 => 'RCDATALessThanSignState',
                2 => 'RCDATAEndTagOpenState',
                3 => 'RCDATAEndTagNameState',
                11 => 'SelfClosingStartTagState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<textarea>text' . '</textarea/>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndTag',
            'data' => 'textarea',
            'selfClosing' => true,
            'attributes' => array(),
            'parseError' => false,
            'html' => '</textarea/>',
            'state' => array(
                0 => 'RCDATAState',
                1 => 'RCDATALessThanSignState',
                2 => 'RCDATAEndTagOpenState',
                3 => 'RCDATAEndTagNameState',
                11 => 'SelfClosingStartTagState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<textarea>' . '</textarea/';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'EndOfFile',
            'data' => '</textarea/',
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => '</textarea/',
            'state' => array(
                0 => 'RCDATAState',
                1 => 'RCDATALessThanSignState',
                2 => 'RCDATAEndTagOpenState',
                3 => 'RCDATAEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testScriptDataEscaped() {
        $source = '<script>';
        $html = '<!- ';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!-- ';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!---';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!-- <';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!-- -<';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedState',
                6 => 'ScriptDataEscapedDashState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!-- -_';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedState',
                6 => 'ScriptDataEscapedDashState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--</';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<A';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--</A';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataEscapedEndTagOpenState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--</a';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataEscapedEndTagOpenState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--</aA';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataEscapedEndTagOpenState',
                7 => 'ScriptDataEscapedEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--</aa';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataEscapedEndTagOpenState',
                7 => 'ScriptDataEscapedEndTagNameState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--</scripta>';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataEscapedEndTagOpenState',
                7 => 'ScriptDataEscapedEndTagNameState',
                13 => 'ScriptDataEscapedState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<scripta ';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataDoubleEscapeStartState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<scriptA';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataDoubleEscapeStartState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<script><';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataDoubleEscapeStartState',
                12 => 'ScriptDataDoubleEscapedState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<script>a';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataDoubleEscapeStartState',
                12 => 'ScriptDataDoubleEscapedState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<script>-<';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataDoubleEscapeStartState',
                12 => 'ScriptDataDoubleEscapedState',
                13 => 'ScriptDataDoubleEscapedDashState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<script>-a';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataDoubleEscapeStartState',
                12 => 'ScriptDataDoubleEscapedState',
                13 => 'ScriptDataDoubleEscapedDashState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<script>---';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataDoubleEscapeStartState',
                12 => 'ScriptDataDoubleEscapedState',
                13 => 'ScriptDataDoubleEscapedDashState',
                14 => 'ScriptDataDoubleEscapedDashDashState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<script>--<';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataDoubleEscapeStartState',
                12 => 'ScriptDataDoubleEscapedState',
                13 => 'ScriptDataDoubleEscapedDashState',
                14 => 'ScriptDataDoubleEscapedDashDashState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<script>-->';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => false,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataDoubleEscapeStartState',
                12 => 'ScriptDataDoubleEscapedState',
                13 => 'ScriptDataDoubleEscapedDashState',
                14 => 'ScriptDataDoubleEscapedDashDashState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<script>--a';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataDoubleEscapeStartState',
                12 => 'ScriptDataDoubleEscapedState',
                13 => 'ScriptDataDoubleEscapedDashState',
                14 => 'ScriptDataDoubleEscapedDashDashState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<script>--</';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataDoubleEscapeStartState',
                12 => 'ScriptDataDoubleEscapedState',
                13 => 'ScriptDataDoubleEscapedDashState',
                14 => 'ScriptDataDoubleEscapedDashDashState',
                15 => 'ScriptDataDoubleEscapedLessThanSignState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<script>--</>';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataDoubleEscapeStartState',
                12 => 'ScriptDataDoubleEscapedState',
                13 => 'ScriptDataDoubleEscapedDashState',
                14 => 'ScriptDataDoubleEscapedDashDashState',
                15 => 'ScriptDataDoubleEscapedLessThanSignState',
                16 => 'ScriptDataDoubleEscapeEndState',
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = '<!--<script>--</scRipt>';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $actual = array_pop($actual);
        $expect = array(
            'type' => 'Character',
            'data' => $html,
            'selfClosing' => false,
            'attributes' => array(),
            'parseError' => true,
            'html' => $html,
            'state' => array(
                0 => 'ScriptDataState',
                1 => 'ScriptDataLessThanSignState',
                2 => 'ScriptDataEscapeStartState',
                3 => 'ScriptDataEscapeStartDashState',
                4 => 'ScriptDataEscapedDashDashState',
                5 => 'ScriptDataEscapedLessThanSignState',
                6 => 'ScriptDataDoubleEscapeStartState',
                12 => 'ScriptDataDoubleEscapedState',
                13 => 'ScriptDataDoubleEscapedDashState',
                14 => 'ScriptDataDoubleEscapedDashDashState',
                15 => 'ScriptDataDoubleEscapedLessThanSignState',
                16 => 'ScriptDataDoubleEscapeEndState',
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testTokenSplit() {
        $source = '<script>';
        $html = 'aa</script>';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => 'aa',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => 'aa',
                'state' => array(
                    0 => 'ScriptDataState',
                ),
            ),
            2 => array(
                'type' => 'EndTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '</script>',
                'state' => array(
                    0 => 'ScriptDataState',
                    1 => 'ScriptDataLessThanSignState',
                    2 => 'ScriptDataEndTagOpenState',
                    3 => 'ScriptDataEndTagNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<script>';
        $html = 'a</aa</script/>';
        $source .= $html;
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'script',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<script>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => 'a</aa',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => 'a</aa',
                'state' => array(
                    0 => 'ScriptDataState',
                    2 => 'ScriptDataLessThanSignState',
                    3 => 'ScriptDataEndTagOpenState',
                    4 => 'ScriptDataEndTagNameState',
                ),
            ),
            2 => array(
                'type' => 'EndTag',
                'data' => 'script',
                'selfClosing' => true,
                'attributes' => array(),
                'parseError' => false,
                'html' => '</script/>',
                'state' => array(
                    0 => 'ScriptDataState',
                    1 => 'ScriptDataLessThanSignState',
                    2 => 'ScriptDataEndTagOpenState',
                    3 => 'ScriptDataEndTagNameState',
                    9 => 'SelfClosingStartTagState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testAttribute() {
        $source = '<p i/>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'p',
                'selfClosing' => true,
                'attributes' => array(
                    0 => array(
                        'name' => 'i',
                        'value' => '',
                        'quoted' => false,
                    ),
                ),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    5 => 'SelfClosingStartTagState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<p i>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'i',
                        'value' => '',
                        'quoted' => false,
                    ),
                ),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<p i">';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'i"',
                        'value' => '',
                        'quoted' => false,
                    ),
                ),
                'parseError' => true,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<p i  =>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'i',
                        'value' => '',
                        'quoted' => false,
                    ),
                ),
                'parseError' => true,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    5 => 'AfterAttributeNameState',
                    7 => 'BeforeAttributeValueState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<p i  >';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'i',
                        'value' => '',
                        'quoted' => false,
                    ),
                ),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    5 => 'AfterAttributeNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<p id  TITLE>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => '',
                        'quoted' => false,
                    ),
                    1 => array(
                        'name' => 'title',
                        'value' => '',
                        'quoted' => false,
                    ),

                ),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    6 => 'AfterAttributeNameState',
                    8 => 'AttributeNameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<p id= &>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => '&',
                        'quoted' => false,
                    ),
                ),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    6 => 'BeforeAttributeValueState',
                    7 => 'AttributeValueUnquotedState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<p id=\'&\'>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => '&',
                        'quoted' => '\'',
                    ),
                ),
                'parseError' => false,
                'html' => '<p id=\'&\'>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    6 => 'BeforeAttributeValueState',
                    7 => 'AttributeValueSingleQuotedState',
                    9 => 'AfterAttributeValueQuotedState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<p id=\'&\'>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'StartTag',
                'data' => 'p',
                'selfClosing' => false,
                'attributes' => array(
                    0 => array(
                        'name' => 'id',
                        'value' => '&',
                        'quoted' => '\'',
                    ),
                ),
                'parseError' => false,
                'html' => '<p id=\'&\'>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'TagNameState',
                    3 => 'BeforeAttributeNameState',
                    4 => 'AttributeNameState',
                    6 => 'BeforeAttributeValueState',
                    7 => 'AttributeValueSingleQuotedState',
                    9 => 'AfterAttributeValueQuotedState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testMarkupDeclarationOpenState() {
        $source = '<!';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
        $source = '<!D';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Uninitialized',
                'data' => '<!',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => 'D',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => 'D',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DenoughCharacters>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!DenoughCharacters>',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DenoughCharacters>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'ContinueBogusCommentState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<![';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Uninitialized',
                'data' => '<!',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '[',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '[',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<![';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Uninitialized',
                'data' => '<!',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                ),
            ),
            1 => array(
                'type' => 'Character',
                'data' => '[',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '[',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testCommentState() {
        $source = '<!---a';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => $source,
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                    5 => 'CommentStartDashState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!-- -a';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => $source,
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                    5 => 'CommentState',
                    6 => 'CommentEndDashState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!-- --!-';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => $source,
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                    5 => 'CommentState',
                    6 => 'CommentEndDashState',
                    7 => 'CommentEndState',
                    8 => 'CommentEndBangState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!-- --!>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => $source,
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                    5 => 'CommentState',
                    6 => 'CommentEndDashState',
                    7 => 'CommentEndState',
                    8 => 'CommentEndBangState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!-- --!a';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => $source,
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    4 => 'CommentStartState',
                    5 => 'CommentState',
                    6 => 'CommentEndDashState',
                    7 => 'CommentEndState',
                    8 => 'CommentEndBangState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testDOCTYPEState() {
        $source = '<!DOCTYPE  ';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => '',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE  ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DOCTYPE html  >';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!DOCTYPE html  >',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => false,
                    'mode' => 'NoQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DOCTYPE html P';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html P',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DOCTYPE html S';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html S',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DOCTYPE html PUBLIC  ';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE html PUBLIC  ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DOCTYPE HTML PUBLIC \'\'';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE HTML PUBLIC \'\'',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierSingleQuotedState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DOCTYPE HTML PUBLIC \'a';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE HTML PUBLIC \'a',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierSingleQuotedState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => 'a',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DOCTYPE HTML PUBLIC \'\'>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!DOCTYPE HTML PUBLIC \'\'>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierSingleQuotedState',
                    24 => 'AfterDOCTYPEPublicIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => false,
                    'mode' => 'NoQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DOCTYPE HTML PUBLIC ""  >';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!DOCTYPE HTML PUBLIC ""  >',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    24 => 'AfterDOCTYPEPublicIdentifierState',
                    25 => 'BetweenDOCTYPEPublicAndSystemIdentifiersState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => false,
                    'mode' => 'NoQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DOCTYPE HTML PUBLIC "" \'\'>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!DOCTYPE HTML PUBLIC "" \'\'>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPEPublicKeywordState',
                    22 => 'BeforeDOCTYPEPublicIdentifierState',
                    23 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    24 => 'AfterDOCTYPEPublicIdentifierState',
                    25 => 'BetweenDOCTYPEPublicAndSystemIdentifiersState',
                    26 => 'DOCTYPESystemIdentifierSingleQuotedState',
                    27 => 'AfterDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => true,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => false,
                    'mode' => 'NoQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DOCTYPE HTML SYSTEM  >';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE HTML SYSTEM  >',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                    22 => 'BeforeDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => false,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => true,
                    'mode' => 'QuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DOCTYPE HTML SYSTEM \'a\'>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!DOCTYPE HTML SYSTEM \'a\'>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                    22 => 'BeforeDOCTYPESystemIdentifierState',
                    23 => 'DOCTYPESystemIdentifierSingleQuotedState',
                    25 => 'AfterDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => 'a',
                    'forceQuirks' => false,
                    'mode' => 'NoQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DOCTYPE HTML SYSTEM ""  >';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => '<!DOCTYPE HTML SYSTEM ""  >',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                    22 => 'BeforeDOCTYPESystemIdentifierState',
                    23 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                    24 => 'AfterDOCTYPESystemIdentifierState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => false,
                    'mode' => 'NoQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<!DOCTYPE HTML SYSTEM "" \'\'>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => 'html',
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => true,
                'html' => '<!DOCTYPE HTML SYSTEM "" \'\'>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'DOCTYPEState',
                    10 => 'BeforeDOCTYPENameState',
                    11 => 'DOCTYPENameState',
                    15 => 'AfterDOCTYPENameState',
                    21 => 'AfterDOCTYPESystemKeywordState',
                    22 => 'BeforeDOCTYPESystemIdentifierState',
                    23 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                    24 => 'AfterDOCTYPESystemIdentifierState',
                    26 => 'BogusDOCTYPEState',
                ),
                'doctypeData' => array(
                    'hasPublicIdentifier' => false,
                    'hasSystemIdentifier' => true,
                    'publicIdentifier' => '',
                    'systemIdentifier' => '',
                    'forceQuirks' => false,
                    'mode' => 'NoQuirksMode',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testCDATASectionState() {
        $source = '<![CDATA[>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Character',
                'data' => $source,
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'CDATASectionState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<![CDATA[]>';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Character',
                'data' => $source,
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'CDATASectionState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $source = '<![CDATA[ //]]a';
        $SegmentedString = new SegmentedString($source);
        $sourceTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $sourceTokenizer->tokenizer();
        $actual = $sourceTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Character',
                'data' => $source,
                'selfClosing' => false,
                'attributes' => array(),
                'parseError' => false,
                'html' => $source,
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    9 => 'CDATASectionState',
                    13 => 'CDATASectionRightSquareBracketState',
                    14 => 'CDATASectionState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testParseDOCTYPE() {
    }

}