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
        $expect = '<br> <br>';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = 'char <br> ';
        $expect = 'char <br>';
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
        $expect = '<img src=""/>';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<html  xmlns="http://www.w3.org/1999/xhtml"  xml:lang="en">';
        $expect = '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<link
rel="Shortcut Icon"/>';
        $expect = '<link rel="Shortcut Icon"/>';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<link

 rel="Shortcut Icon"/>';
        $expect = '<link rel="Shortcut Icon"/>';
        $actual = HTMLMinify::minify($source);
        $this->assertEquals($expect, $actual);

        $source = '<link rel="Shortcut Icon"
type="image/x-icon"/>';
        $expect = '<link rel="Shortcut Icon" type="image/x-icon"/>';
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
        $expect = '<link rel="Shortcut Icon" type="image/x-icon" href="http://www.example.com/favicon.ico"/>
<link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.example.com/zengarden.xml"/>';
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
        $source = '<DIV>    end
    </';
        $expect = '<div> end
</';
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

}