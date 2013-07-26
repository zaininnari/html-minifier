<?php
/**
 * The Blink HTMLTokenizer ported to PHP.
 *
 * @license MIT
 */

namespace zz\Html;

class HTMLMinify {
    const ENCODING = 'UTF-8';
    const REMOVE_WHITE_SPACE = 1;
    const REMOVE_SPACE_ONLY = 2;

    /**
     * @var null|string
     */
    protected $html = null;
    /**
     * @var array
     */
    protected $options = array();
    /**
     * @var HtmlToken[]
     */
    protected $tokens;

    /**
     * @param string $html
     * @param array $options
     */
    public function __construct($html, $options = array()) {
        $html = ltrim($html);
        $this->html = $html;
        $this->options = $this->options($options);

        $SegmentedString = new SegmentedString($html);
        $HTMLTokenizer = new HTMLTokenizer($SegmentedString, $options);
        $this->tokens = $HTMLTokenizer->tokenizer();
    }

    /**
     * @param array $options
     * @return array
     */
    protected function options(Array $options) {
        $_options = array(
            // '<br />' => '<br/>'
            'startTagBeforeSlash' => static::REMOVE_WHITE_SPACE,
            'comment' => true,
            'deleteDuplicateAttribute' => true,
        );
        return $options + $_options;
    }

    /**
     * @param $html
     * @param array $options
     * @return string
     */
    public static function minify($html, $options = array()) {
        $instance = new self($html, $options);
        return $instance->process();
    }

    /**
     * @return HtmlToken[]
     */
    public function getTokens() {
        return $this->tokens;
    }

    /**
     * @return string
     */
    public function process() {
        $html = '';
        $this->beforeFilter();

        $tokens = $this->tokens;

        for ($i = 0, $len = count($tokens); $i < $len; $i++) {
            $token = $tokens[$i];
            $html .= $this->_buildHtml($token);
        }
        return $html;
    }

    /**
     * @param HTMLToken $token
     * @return string
     */
    protected function _buildHtml(HTMLToken $token) {
        switch ($token->getType()) {
            case HTMLToken::StartTag:
                $selfClosing = $token->hasSelfClosing() ? '/' : '';
                if ($selfClosing) {
                    $selfClosing = ($this->options['startTagBeforeSlash'] === static::REMOVE_WHITE_SPACE ? '' : ' ') . $selfClosing;
                }
                $attributes = $this->_buildAttributes($token);
                $beforeAttributeSpace = '';
                if ($attributes) {
                    $beforeAttributeSpace = ' ';
                }
                $html = sprintf('<%s%s%s%s>', $token->getTagName(), $beforeAttributeSpace, $attributes, $selfClosing);
                break;
            case HTMLToken::EndTag:
                $html = sprintf('</%s>', $token->getTagName());
                break;
            default :
                $html = $token->getData();
                break;
        }
        return $html;
    }

    /**
     * @param HTMLToken $token
     * @return string
     */
    protected function _buildAttributes(HTMLToken $token) {
        $attr = array();
        $format = '%s=%s%s%s';
        foreach ($token->getAttributes() as $attribute) {
            $name = $attribute['name'];
            $value = $attribute['value'];
            switch ($attribute['quoted']) {
                case HTMLToken::DoubleQuoted:
                    $quoted = '"';
                    break;
                case HTMLToken::SingleQuoted:
                    $quoted = '\'';
                    break;
                default:
                    $quoted = '';
                    break;
            }
            if ($quoted === '' && $value === '') {
                $attr[] = $name;
            } else {
                $attr[] = sprintf($format, $name, $quoted, $value, $quoted);
            }
        }
        return join(' ', $attr);
    }

    protected function beforeFilter() {
        $this->removeWhitespaceFromComment();
        $this->removeWhitespaceFromCharacter();

        if ($this->options['deleteDuplicateAttribute']) {
            $this->optimizeStartTagAttributes();
        }
    }

    protected function removeWhitespaceFromComment() {
        $tokens = $this->tokens;

        for ($i = 0, $len = count($tokens); $i < $len; $i++) {
            $token = $tokens[$i];
            $type = $token->getType();
            if ($type === HTMLToken::StartTag) {
                $tagName = $token->getTagName();
                if ($tagName === HTMLNames::scriptTag || $tagName === HTMLNames::styleTag) {
                    $i++;
                    continue;
                }
            } elseif ($this->_isConditionalComment($token)) {
                continue;
            }

            if ($type !== HTMLToken::Comment) {
                continue;
            }

            unset($tokens[$i]);
            $tokens = array_merge($tokens, array());
            $len = count($tokens);
            $i--;
        }

        $tokens = array_merge($tokens, array());

        // combine chars
        for ($i = 1, $len = count($tokens); $i < $len; $i++) {
            $token = $tokens[$i];
            if ($token->getType() !== HTMLToken::Character) {
                continue;
            }
            $token_before = $tokens[$i - 1];
            if ($token_before->getType() !== HTMLToken::Character) {
                continue;
            }
            $tokens[$i]->setData($tokens[$i - 1]->getData() . $tokens[$i]->getData());
            unset($tokens[$i - 1]);
            $len = count($tokens);
            $tokens = array_merge($tokens, array());
            $i--;

        }
        $tokens = array_merge($tokens, array());
        $this->tokens = $tokens;
    }

    protected function removeWhitespaceFromCharacter() {
        $tokens = $this->tokens;
        $skip = false;

        for ($i = 0, $len = count($tokens); $i < $len; $i++) {
            $token = $tokens[$i];
            if ($token->getType() !== HTMLToken::Character) {
                continue;
            }

            $characters = $token->getData();
            if ($i === 0) {
                $token_before = new HTMLToken();
            } else {
                $token_before = $tokens[$i - 1];
            }
            // add check `src` attr
            if ($token_before->getType() === HTMLToken::StartTag) {
                switch ($token_before->getTagName()) {
                    case HTMLNames::scriptTag:
                    case HTMLNames::styleTag:
                    case HTMLNames::textareaTag:
                    case HTMLNames::preTag:
                        continue 2;
                        break;
                    default:
                        $characters = $this->_removeWhitespaceFromCharacter($characters);
                        $skip = false;
                        break;
                }
            } elseif ($token_before->getType() === HTMLToken::EndTag) {
                $skip = false;
            }

            if ($skip === false) {
                $characters = $this->_removeWhitespaceFromCharacter($characters);
                if ($i === ($len - 1)) {
                    $characters = rtrim($characters);
                }
            }
            $tokens[$i]->setData($characters);
        }
        $this->tokens = $tokens;
    }

    /**
     * @param string $characters
     * @return string
     */
    protected function _removeWhitespaceFromCharacter($characters) {
        $compactCharacters = '';
        $hasWhiteSpace = false;

        for ($i = 0, $len = mb_strlen($characters, static::ENCODING); $i < $len; $i++) {
            $char = mb_substr($characters, $i, 1, static::ENCODING);
            if ($char === "\x0A") {
                // remove before whitespace char
                if ($hasWhiteSpace) {
                    $compactCharacters = mb_substr($compactCharacters, 0, -1, static::ENCODING);
                }
                $compactCharacters .= $char;
                $hasWhiteSpace = true;
            } else if ($char === ' ' || $char === "\x09" || $char === "\x0C") {
                if (!$hasWhiteSpace) {
                    $compactCharacters .= ' ';
                    $hasWhiteSpace = true;
                }
            } else {
                $hasWhiteSpace = false;
                $compactCharacters .= $char;
            }
        }

        return $compactCharacters;
    }

    // a,abbr,acronym,address,applet,area
    // b,base,basefont,bdo,bgsound,big,blink,blockquote,body,br,button
    // caption,center,cite,code,col,colgroup,comment
    // dd,dir,div,dl,dt
    // fieldset,form,frame,frameset
    // h1,h2,h3,h4,h5,h6,head,hr,html
    // legend,li,link
    // map,menu,meta
    // object,ol,optgroup,option
    // p,param,
    // table,tbody,thead,td,th,tr,tfoot,title
    // ul


    protected function optimizeStartTagAttributes() {
        $tokens = $this->tokens;
        for ($i = 0, $len = count($tokens); $i < $len; $i++) {
            $token = $tokens[$i];
            if ($token->getType() !== HTMLToken::StartTag) {
                continue;
            }

            $attributes_old = $token->getAttributes();
            $attributes_new = array();
            $attributes_name = array();

            foreach ($attributes_old as $attribute) {
                if (!isset($attributes_name[$attribute['name']])) {
                    $attributes_name[$attribute['name']] = true;
                    $attributes_new[] = $attribute;
                }
            }
            if ($attributes_old !== $attributes_new) {
                $token->setAttributes($attributes_new);
            }
        }
        $this->tokens = $tokens;
    }

    /**
     * @param HTMLToken $token
     * @return bool
     */
    protected function _isConditionalComment(HTMLToken $token) {
        if ($token->getType() !== HTMLToken::Comment) {
            return false;
        }

        $comment = $this->_buildHtml($token);
        $pattern = '/\A<!(?:--)?\[if [^\]]+\]>/s';
        if (preg_match($pattern, $comment)) {
            return true;
        }
        $pattern = '/<!\[endif\](?:--)?>\Z/s';
        if (preg_match($pattern, $comment)) {
            return true;
        }
        return false;
    }

}