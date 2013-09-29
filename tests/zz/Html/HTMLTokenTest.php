<?php
namespace zz\Html;
use zz\Html;

class HTMLTokenTest extends \PHPUnit_Framework_TestCase {

    public function testGetter() {
        $html = '<img src="img.png" />';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $tokens = $HTMLTokenizer->tokenizer();
        $token = $tokens[0];
        $this->assertEquals(HTMLToken::StartTag, $token->getType());
        $this->assertEquals(HTMLNames::imgTag, $token->getName());
        $this->assertEquals(HTMLNames::imgTag, $token->getTagName());
        $this->assertEquals(array(
            array(
                'name' => 'src',
                'value' => 'img.png',
                'quoted' => '"'
            )
        ), $token->getAttributes());
        $this->assertEquals(HTMLNames::imgTag, $token->getData());
        $this->assertEquals($html, $token->getHtmlOrigin());
        $this->assertEquals(array(
            0 => 'DataState',
            1 => 'TagOpenState',
            2 => 'TagNameState',
            5 => 'BeforeAttributeNameState',
            6 => 'AttributeNameState',
            9 => 'BeforeAttributeValueState',
            10 => 'AttributeValueDoubleQuotedState',
            18 => 'AfterAttributeValueQuotedState',
            19 => 'BeforeAttributeNameState',
            20 => 'SelfClosingStartTagState',
        ), $token->getState());
        $this->assertEquals(true, $token->selfClosing());

        $html = ' ';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $tokens = $HTMLTokenizer->tokenizer();
        $token = $tokens[0];
        $this->assertEquals(false, $token->getTagName());
    }

    public function testToString() {
        $html = '<img src="img.png" />';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $tokens = $HTMLTokenizer->tokenizer();
        $token = $tokens[0];
        $this->assertEquals(HTMLNames::imgTag, $token->__toString());
    }

    public function testClear() {
        $Token = new HTMLToken();
        $this->assertEquals(HTMLToken::Uninitialized, $Token->getType());
        $this->assertEquals('', $Token->getData());

        $Token->beginComment();
        $Token->appendToComment('a');
        $this->assertEquals(HTMLToken::Comment, $Token->getType());
        $this->assertEquals('a', $Token->getData());

        $Token->clear();
        $this->assertEquals(HTMLToken::Uninitialized, $Token->getType());
        $this->assertEquals('', $Token->getData());
    }

    public function testForceQuirks() {
        $Token = new HTMLToken();
        $this->assertEquals(null, $Token->forceQuirks());
    }

    public function testSelfClosing() {
        $Token = new HTMLToken();
        $this->assertEquals(false, $Token->hasSelfClosing());
    }

    public function testHasParseError() {
        $Token = new HTMLToken();
        $this->assertEquals(false, $Token->hasParseError());
    }

    public function testGetDoctypeData() {
        $Token = new HTMLToken();
        $this->assertEquals(array(
            'hasPublicIdentifier' => false,
            'hasSystemIdentifier' => false,
            'publicIdentifier' => '',
            'systemIdentifier' => '',
            'forceQuirks' => false,
        ), $Token->getDoctypeData());
    }

    public function testAttributeQuoted() {
        $html = '<img id=id class=\'class\' src="img.png" />';
        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, array('debug' => true));
        $tokens = $HTMLTokenizer->tokenizer();
        $token = $tokens[0];
        $this->assertEquals(array(
            0 => array(
                'name' => 'id',
                'value' => 'id',
                'quoted' => false,
            ),
            1 => array(
                'name' => 'class',
                'value' => 'class',
                'quoted' => '\'',
            ),
            2 => array(
                'name' => 'src',
                'value' => 'img.png',
                'quoted' => '"',
            ),
        ), $token->getAttributes());
    }
}