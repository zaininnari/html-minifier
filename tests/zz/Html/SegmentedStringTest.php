<?php
namespace zz\Html;
use zz\Html;

class SegmentedStringTest extends \PHPUnit_Framework_TestCase {

    public function testGet() {
        $html = 'html';
        $SegmentedString = new SegmentedString($html);
        $this->assertEquals('html', $SegmentedString->get());
    }

    public function testCurrentChar() {
        $html = 'html';
        $SegmentedString = new SegmentedString($html);
        $this->assertEquals('h', $SegmentedString->getCurrentChar());
        $this->assertEquals(4, $SegmentedString->seek($SegmentedString->len()));
        $SegmentedString->advance();
        $this->assertEquals(false, $SegmentedString->getCurrentChar());
    }

    public function testRead() {
        $html = 'html';
        $SegmentedString = new SegmentedString($html);
        $this->assertEquals('h', $SegmentedString->read(1));
        $this->assertEquals('t', $SegmentedString->read(1));
        $this->assertEquals('m', $SegmentedString->read(1));
        $this->assertEquals('l', $SegmentedString->read(1));
        $this->assertEquals(false, $SegmentedString->read(1));
    }

    public function testSeek() {
        $html = 'html';
        $SegmentedString = new SegmentedString($html);
        $this->assertEquals(false, $SegmentedString->seek($SegmentedString->len() + 1));
        $this->assertEquals(4, $SegmentedString->seek($SegmentedString->len() - 1));
        $this->assertEquals(false, $SegmentedString->seek(10, SegmentedString::current));
        $SegmentedString->seek(0);
        $this->assertEquals(true, $SegmentedString->seek(1, SegmentedString::current));
        $this->assertEquals(1, $SegmentedString->tell());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSeekInvalidArgumentException() {
        $html = 'html';
        $SegmentedString = new SegmentedString($html);
        $this->assertEquals(false, $SegmentedString->seek(10, -1));
    }

}