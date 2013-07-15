<?php
namespace zz\Html;
use zz\Html;

class HTMLTokenizerTest extends \PHPUnit_Framework_TestCase {

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

        $html = '<!DOCTYPE html>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => '<!DOCTYPE html>',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE html>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);


        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'DOCTYPE',
                'data' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
                    10 => 'AfterDOCTYPEPublicKeywordState',
                    11 => 'BeforeDOCTYPEPublicIdentifierState',
                    12 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    45 => 'AfterDOCTYPEPublicIdentifierState',
                    46 => 'BetweenDOCTYPEPublicAndSystemIdentifiersState',
                    47 => 'DOCTYPESystemIdentifierDoubleQuotedState',
                    97 => 'AfterDOCTYPESystemIdentifierState',
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

    }

    public function testCharDecimal() {
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
                'html' => '&#34;',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

    }

    public function testCharNamed() {
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
                'html' => '&amp;',
                'state' => array(
                    0 => 'DataState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

    }

    public function testCharDecimalInvalid() {
        $html = '&#34';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Uninitialized',
                'data' => '&#34',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '&#34',
                'state' => array(
                    0 => 'DataState',
                    1 => 'CharacterReferenceInDataState'
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
                        'quoted' => false,
                    ),
                ),
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
                'html' => '<!-- comment -->',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'CommentStartState',
                    4 => 'CommentState',
                    13 => 'CommentEndDashState',
                    14 => 'CommentEndState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }


    public function testCDATA() {
        $html = '<![CDATA[
  var i = 0;
 //]]>';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Character',
                'data' => '<![CDATA[
  var i = 0;
 //]]>',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<![CDATA[
  var i = 0;
 //]]>',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'CDATASectionState',
                    21 => 'CDATASectionRightSquareBracketState',
                    22 => 'CDATASectionDoubleRightSquareBracketState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

    public function testConditionalComment() {
        $html = $source = '<!--[if expression]>
          HTML <![endif]-->';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Comment',
                'data' => '<!--[if expression]>
          HTML <![endif]-->',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!--[if expression]>
          HTML <![endif]-->',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'CommentStartState',
                    4 => 'CommentState',
                    45 => 'CommentEndDashState',
                    46 => 'CommentEndState',
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

    public function testCharacterReferenceInDataState() {
        $html = $source = '&';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $HTMLTokenizer->tokenizer();
        $actual = $HTMLTokenizer->getTokensAsArray();
        $expect = array(
            0 => array(
                'type' => 'Uninitialized',
                'data' => '&',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '&',
                'state' => array(
                    0 => 'DataState',
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
                        'quoted' => false,
                    ),
                    1 => array(
                        'name' => 'src',
                        'value' => 'img',
                        'quoted' => false,
                    ),
                ),
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
                'html' => 'end',
                'state' => array(
                    0 => 'PLAINTEXTState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);

        $html = $source = '<DIV>end</';
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
                        'quoted' => false,
                    ),
                ),
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
                        'quoted' => false,
                    ),
                ),
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
                'html' => '<!',
                'state' => array(
                    0 => 'DataState',
                    1 => 'ContinueBogusCommentState',
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
                'data' => '<!-',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!-',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
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
                'html' => '<!-->',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'CommentStartState',
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
                'html' => '<!---',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'CommentStartState',
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
                'html' => '<!--->',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'CommentStartState',
                    4 => 'CommentStartDashState',
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
                'html' => '<!----',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'CommentStartState',
                    4 => 'CommentStartDashState',
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
                'html' => '<!----!',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'CommentStartState',
                    4 => 'CommentStartDashState',
                    5 => 'CommentEndState',
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
                'data' => '<!DOCTYPE',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
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
                'data' => '<!DOCTYPE ',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
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
                'data' => '<!DOCTYPE P',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE P',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
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
                'data' => '<!DOCTYPE P ',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE P ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
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
                'data' => '<!DOCTYPE html PUBLIC',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE html PUBLIC',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
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
                'data' => '<!DOCTYPE html PUBLIC ',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE html PUBLIC ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
                    10 => 'AfterDOCTYPEPublicKeywordState',
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
                'data' => '<!DOCTYPE html PUBLIC "',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE html PUBLIC "',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
                    10 => 'AfterDOCTYPEPublicKeywordState',
                    11 => 'BeforeDOCTYPEPublicIdentifierState',
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
                'data' => '<!DOCTYPE html PUBLIC \'',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE html PUBLIC \'',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
                    10 => 'AfterDOCTYPEPublicKeywordState',
                    11 => 'BeforeDOCTYPEPublicIdentifierState',
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
                'data' => '<!DOCTYPE html PUBLIC ""',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE html PUBLIC ""',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
                    10 => 'AfterDOCTYPEPublicKeywordState',
                    11 => 'BeforeDOCTYPEPublicIdentifierState',
                    12 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
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
                'data' => '<!DOCTYPE html PUBLIC "" ',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE html PUBLIC "" ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
                    10 => 'AfterDOCTYPEPublicKeywordState',
                    11 => 'BeforeDOCTYPEPublicIdentifierState',
                    12 => 'DOCTYPEPublicIdentifierDoubleQuotedState',
                    13 => 'AfterDOCTYPEPublicIdentifierState',
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
                'data' => '<!DOCTYPE memo SYSTEM',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE memo SYSTEM',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
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
                'data' => '<!DOCTYPE memo SYSTEM ',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE memo SYSTEM ',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
                    10 => 'AfterDOCTYPESystemKeywordState',
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
                'data' => '<!DOCTYPE memo SYSTEM "',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE memo SYSTEM "',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
                    10 => 'AfterDOCTYPESystemKeywordState',
                    11 => 'BeforeDOCTYPESystemIdentifierState',
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
                'data' => '<!DOCTYPE memo SYSTEM \'',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE memo SYSTEM \'',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
                    10 => 'AfterDOCTYPESystemKeywordState',
                    11 => 'BeforeDOCTYPESystemIdentifierState',
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
                'data' => '<!DOCTYPE memo SYSTEM ""',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE memo SYSTEM ""',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
                    10 => 'AfterDOCTYPESystemKeywordState',
                    11 => 'BeforeDOCTYPESystemIdentifierState',
                    12 => 'DOCTYPESystemIdentifierDoubleQuotedState',
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
                'data' => '<!DOCTYPE memo SYSTEM "http://www.4dd.co.jp/DTD/memo.dtd"',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE memo SYSTEM "http://www.4dd.co.jp/DTD/memo.dtd"',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
                    10 => 'AfterDOCTYPESystemKeywordState',
                    11 => 'BeforeDOCTYPESystemIdentifierState',
                    12 => 'DOCTYPESystemIdentifierDoubleQuotedState',
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
                'data' => '<!DOCTYPE memo D"',
                'selfClosing' => false,
                'attributes' => array(),
                'html' => '<!DOCTYPE memo D"',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                    3 => 'DOCTYPEState',
                    4 => 'BeforeDOCTYPENameState',
                    5 => 'DOCTYPENameState',
                    9 => 'AfterDOCTYPENameState',
                    10 => 'BogusDOCTYPEState',
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
                'html' => '<![CDATA[',
                'state' => array(
                    0 => 'DataState',
                    1 => 'TagOpenState',
                    2 => 'MarkupDeclarationOpenState',
                ),
            ),
        );
        $this->assertEquals($expect, $actual);
    }

}