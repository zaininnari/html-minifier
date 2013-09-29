<?php
namespace zz\Html;
use zz\Html;

class HTMLMinifyTest extends \PHPUnit_Framework_TestCase {

    protected $_test_file_dir = 'minify';

    /**
     * @dataProvider providerMinify
     */
    public function testMinify($filebase) {

        $dir = __DIR__ . DIRECTORY_SEPARATOR . $this->_test_file_dir . DIRECTORY_SEPARATOR;
        $suffix = '.html';

        $source = file_get_contents($dir . $filebase . $suffix);
        $expect = rtrim(file_get_contents($dir . $filebase . '_after' . $suffix));
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);
    }

    /**
     * @dataProvider providerMinify
     */
    public function testMinifyOptimizeNewline($filebase) {

        $dir = __DIR__ . DIRECTORY_SEPARATOR . $this->_test_file_dir . DIRECTORY_SEPARATOR;
        $suffix = '.html';

        $source = file_get_contents($dir . $filebase . $suffix);
        $expect = rtrim(file_get_contents($dir . $filebase . '_optimize' . $suffix));
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);
    }

    public function providerMinify() {
        return array(
            array(
                'base_simple',
            ),
            array(
                'base',
            ),
        );
    }

    public function testOptimizeTagNewline() {
        // no option : no optimize
        $source = chr(10) . chr(10) . '<div>' . chr(10) . chr(10) . '</div>' . chr(10) . chr(10) . '<div></div>' . chr(10) . chr(10);
        $expect = '<div>' . chr(10) . '</div>' . chr(10) . '<div></div>';
        $option = array();
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        // optimize option NO
        $source = '<div></div>' . chr(10) . '<div></div>' . chr(10) . '<div></div>';
        $expect = '<div></div>' . chr(10) . '<div></div>' . chr(10) . '<div></div>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_SIMPLE);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        // optimize option YES
        $source = chr(10) . '<!doctype html>' . chr(10) . '<html></html>';
        $expect = '<!doctype html><html></html>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = chr(10) . '<div></div>' . chr(10) . '<div>' . chr(10) . '</div>' . chr(10);
        $expect = '<div></div><div></div>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<div>A' . chr(10) . chr(10) . 'Z</div>' . chr(10) . chr(10) . '<div>' . chr(10) . '</div>' . chr(10);
        $expect = '<div>A' . chr(10) . 'Z</div><div></div>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<i>A' . chr(10) . chr(10) . 'Z</i>' . chr(10) . chr(10) . '<i>' . chr(10) . '</i>' . chr(10);
        $expect = '<i>A' . chr(10) . 'Z</i>' . chr(10) . '<i>' . chr(10) . '</i>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);
        $source = '<div> <img> <div> </div> <img> </div>';
        $expect = '<div><img /><div></div><img /></div>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<div> <i> char </i> <div> </div>' . chr(10) . '<em>' . chr(10) . 'char' . chr(10) . '</em>' . chr(10) . '</div>';
        $expect = '<div><i> char </i><div></div><em>' . chr(10) . 'char' . chr(10) . '</em></div>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<div> <unknown> <div> </div> <unknown> </unknown> </div>';
        $expect = '<div><unknown><div></div><unknown> </unknown></div>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        // optimize option YES, but no optimize
        // pre, textarea, script and style : no modify
        // script and style : try trim
        $source = '<pre>' . chr(10) . '<div></div>' . chr(10) . '</pre>';
        $expect = '<pre>' . chr(10) . '<div></div>' . chr(10) . '</pre>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<textarea>' . chr(10) . '<div></div>' . chr(10) . '</textarea>';
        $expect = '<textarea>' . chr(10) . '<div></div>' . chr(10) . '</textarea>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<script>' . chr(10) . 'var a = 1;' . chr(10) . '</script>';
        $expect = '<script>var a = 1;</script>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<style>' . chr(10) . '.selector{color : red;}' . chr(10) . '</style>';
        $expect = '<style>.selector{color : red;}</style>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        // nest
        $source = '<textarea>' . chr(10) . '<pre>' . chr(10) . '<div></div>' . chr(10) . '</pre>' . chr(10) . '</textarea>';
        $expect = '<textarea>' . chr(10) . '<pre>' . chr(10) . '<div></div>' . chr(10) . '</pre>' . chr(10) . '</textarea>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        // conditionalComment
        $source = '<!-- HTML -->' . chr(10) . '<!--[if expression]>' . chr(10) . ' HTML ' . chr(10) . '<![endif]-->' . chr(10) . '<![if expression]>' . chr(10) . ' HTML ' . chr(10) . '<![endif]>';
        $expect = '<!--[if expression]>' . chr(10) . ' HTML ' . chr(10) . '<![endif]--><![if expression]>HTML<![endif]>';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<div><div>' . chr(10) . 'a' . chr(10) . '<!--[if expression]>' . chr(10) . ' HTML ' . chr(10) . '<![endif]-->';
        $expect = '<div><div>a<!--[if expression]>' . chr(10) . ' HTML ' . chr(10) . '<![endif]-->';
        $option = array('optimizationLevel' => HTMLMinify::OPTIMIZATION_ADVANCED);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);
    }

    public function testOptimizeByOptionDoctype() {
        $source = '<br><br/><br />';
        $expect = '<br /><br /><br />';
        $option = array();
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<br><br/><br />';
        $expect = '<br><br><br>';
        $option = array('doctype' => HTMLMinify::DOCTYPE_HTML4);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<br><br/><br />';
        $expect = '<br /><br /><br />';
        $option = array('doctype' => HTMLMinify::DOCTYPE_XHTML1);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<br><br/><br />';
        $expect = '<br><br><br>';
        $option = array('doctype' => HTMLMinify::DOCTYPE_HTML5);
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);
    }

    public function testOptimizeByOptionEmptyElement() {
        $source = '<br><br/><br />';
        $expect = '<br /><br /><br />';
        $option = array();
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<br><br/><br />';
        $expect = '<br><br><br>';
        $option = array(
            'emptyElementAddSlash' => false,
            'emptyElementAddWhitespaceBeforeSlash' => false,
        );
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<br><br/><br />';
        $expect = '<br><br><br>';
        $option = array(
            'emptyElementAddSlash' => false,
            'emptyElementAddWhitespaceBeforeSlash' => true,
        );
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<br><br/><br />';
        $expect = '<br/><br/><br/>';
        $option = array(
            'emptyElementAddSlash' => true,
            'emptyElementAddWhitespaceBeforeSlash' => false,
        );
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);

        $source = '<br><br/><br />';
        $expect = '<br /><br /><br />';
        $option = array(
            'emptyElementAddSlash' => true,
            'emptyElementAddWhitespaceBeforeSlash' => true,
        );
        $actual = HTMLMinify::minify($source, $option);
        $this->assertEquals($expect, $actual);
    }

    public function testMinifyDOCTYPE() {
        $source = '<!DOCTYPE html>';
        $expect = '<!DOCTYPE html>';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);
    }

    public function testExcludeComment() {
        $source = '<!--nocache-->remove<!--/nocache-->';
        $expect = 'remove';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<!--nocache-->no remove<!--/nocache-->';
        $expect = '<!--nocache-->no remove';
        $actual = HTMLMinify::minify($source, array('excludeComment' => array('/<!--nocache-->/')));
        $this->assertEquals($expect, $actual);

        $source = '<!--nocache-->no remove<!--/nocache-->';
        $expect = '<!--nocache-->no remove<!--/nocache-->';
        $actual = HTMLMinify::minify($source, array('excludeComment' => array('/<!--\/?nocache-->/')));
        $this->assertEquals($expect, $actual);
    }

    public function testRemoveWhitespaceInCharacter() {
        $source = ' char';
        $expect = 'char';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = 'char ';
        $expect = 'char';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<br>    <br>';
        $expect = '<br /> <br />';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = 'char <br> ';
        $expect = 'char <br />';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<p>char
   <span>
   b
   </span>
   c
   cc

   ccc
   </p>';
        $expect = '<p>char
<span>
b
</span>
c
cc
ccc
</p>';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);
    }

    public function testRemoveWhitespaceInTag() {
        $source = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >';
        $expect = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<img src="" />';
        $expect = '<img src="" />';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<html  xmlns="http://www.w3.org/1999/xhtml"  xml:lang="en">';
        $expect = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<link
rel="Shortcut Icon"/>';
        $expect = '<link rel="Shortcut Icon" />';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<link

 rel="Shortcut Icon"/>';
        $expect = '<link rel="Shortcut Icon" />';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<link rel="Shortcut Icon"
type="image/x-icon"/>';
        $expect = '<link rel="Shortcut Icon" type="image/x-icon" />';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<link
		rel="Shortcut Icon"
		type="image/x-icon"
		href="http://www.example.com/favicon.ico" />
	<link
		rel="alternate"
		type="application/rss+xml"
		title="RSS"
		href="http://www.example.com/zengarden.xml" />';
        $expect = '<link rel="Shortcut Icon" type="image/x-icon" href="http://www.example.com/favicon.ico" />
<link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.example.com/zengarden.xml" />';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

    }

    public function testNoRemoveWhitespace() {
        $source = "<script type=\"text/javascript\"><!--
// js comment inside SCRIPT element
    var a = 1;
        a++;
// --></script>";
        $expect = $source;
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = "<script type=\"text/javascript\">
// js comment inside SCRIPT element
    var a = 1;
    a++;
</script>";
        $expect = $source;
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);


        $source = '<script type="text/javascript">
 //<![CDATA[
  var i = 0;
  while  (++i < 10)
  {
    // ...
  }
 //]]>
</script>';
        $expect = $source;
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '		</div>
        <textarea name="comment" id="comment" rows="6" class="maxwidth" cols="80">66666

1234567890<script> var Hello = \'world\';</script></textarea>';
        $expect = '</div>
<textarea name="comment" id="comment" rows="6" class="maxwidth" cols="80">66666

1234567890<script> var Hello = \'world\';</script></textarea>';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

    }

    public function testRemoveComment() {
        $source = '<!---->';
        $expect = '';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '1<!---->';
        $expect = '1';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<!---->1';
        $expect = '1';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '1<!---->1';
        $expect = '11';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = ' <!----> ';
        $expect = '';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = ' <!----> <!----><!----> <!----><!----><!----> ';
        $expect = '';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<!--[if expression]>
          HTML <![endif]-->';
        $expect = $source;
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<!--[if expression]> HTML';
        $expect = $source;
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<!--[if expression]> HTML <![endif]-->';
        $expect = $source;
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<![if expression]> HTML <![endif]>';
        $expect = $source;
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<!-- HTML --><!--[if expression]> HTML <![endif]--><![if expression]> HTML <![endif]>';
        $expect = '<!--[if expression]> HTML <![endif]--><![if expression]> HTML <![endif]>';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<!--[if !IE]>--><p>Browser != IE</p><!--<![endif]-->';
        $expect = $source;
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<!--[if gt IE 6]><!-->
This code displays on non-IE browsers and on IE 7 or higher.
<!--<![endif]-->';
        $expect = '<!--[if gt IE 6]><!-->
This code displays on non-IE browsers and on IE 7 or higher.
<!--<![endif]-->';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<!--[if !condition]><![IGNORE[--><![IGNORE[]]> HTML <!--<![endif]-->';
        $expect = '<!--[if !condition]><![IGNORE[--> HTML <!--<![endif]-->';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<script>var i = 0;</script><!----><script>var i = 0;</script>';
        $expect = '<script>var i = 0;</script><script>var i = 0;</script>';

        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);
    }

    public function testTagTitle() {
        $source = '	<title>    html    css    javascript    </title>

<title>    html    css    javascript    </title>

 <title>
  html
  css
  javascript
  </title>';
        $expect = '<title> html css javascript </title>
<title> html css javascript </title>
<title>
html
css
javascript
</title>';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<title>&';
        $expect = '<title>&';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<title>&#34;';
        $expect = '<title>&#34;';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);
    }

    public function testTagInvalid() {
        $source = '<DIV>    end' . chr(10) . '    </';
        $expect = '<div> end' . chr(10) . '</';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<title>&';
        $expect = '<title>&';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<title>&#34;';
        $expect = '<title>&#34;';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);
    }

    public function testOptimizeStartTagAttributes() {
        $source = '<p title="title1" title="title2" class="class1" class="class2">';
        $expect = '<p title="title1" class="class1">';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<p title="title1" title="title2" class="class1" class="class2">';
        $expect = '<p title="title1" title="title2" class="class1" class="class2">';
        $actual = HTMLMinify::minify($source, array('removeDuplicateAttribute' => false));
        $this->assertEquals($expect, $actual);
    }

    public function testGetTokens() {
        $source = '<p title="title1" class="class1">';
        $expect = 1;
        $instance = new HTMLMinify($source);
        $instance->process();
        $actual = $instance->getTokens();
        $this->assertEquals($expect, count($actual));
        $this->assertTrue($actual[0] instanceof HTMLToken);
    }

    public function testAttributeQuoted() {
        $source = '<img id=id class=\'class\' src="img.png" title />';
        $expect = '<img id=id class=\'class\' src="img.png" title />';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);
    }
}