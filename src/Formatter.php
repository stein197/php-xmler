<?php
namespace Stein197\Xmler;

use function htmlentities;
use function str_repeat;

final class Formatter {

	// https://www.w3.org/TR/2011/WD-html-markup-20110113/syntax.html#void-element
	private const VOID_ELEMENTS = ['area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
	private const OPTIONS_DEFAULT = [
		'beautify' => true,
		'comments' => true,
		'commentsPadding' => true, // <!-- ... -->, <!--...-->
		'closeVoid' => true, // <img />, <img>
		'emptyAttributes' => 'preserve', // attr, attr="", ''
		'emptyElements' => 'selfClose', // <div/>, <div></div>
		'emptyElementsNl' => false, // <div></div>, <div>\n</div>
		'encode' => true, // &, &amp;
		'encoding' => 'UTF-8',
		'ifComment' => 'indent', // <!--[if]>..., <!--[if]>\n..., <!--[if]>\n\t..., false
		'indent' => "\t",
		'indentAttributes' => false, // <div attr>, <div\nattr>
		'indentCloseBracket' => '', // <div>, <div\n>, <div\n\t>
		'indentScriptContent' => true,
		'indentSelfClosedBracked' => true, // <hr/>, <hr />
		'indentStyleContent' => true,
		'indentTextContent' => false,
		'inlineElements' => ['br', 'hr', 'span'], // text<br/>content
		'leafNodes' => 'close', // selfclose, doubleclose, delete / <div/>, <div></div>, ''
		'mode' => 'html', // html, xhtml, xml
		'nl' => "\n", // \n, \r\n, \r
		'quotes' => '"', // ", ', false
		'singleTextOnNewLine' => false, // <p>text</p>, <p>\n\ttext\n</p>
		'uppercaseAttributeNames' => false, // <div ATTR>
		'uppercaseElementNames' => false, // <DIV attr>
	];

	public function __construct(private readonly array $options) {
		$this->options = $options === self::OPTIONS_DEFAULT ? self::OPTIONS_DEFAULT : [...self::OPTIONS_DEFAULT, ...$options];
	}

	public function encodeEntities(string $text): string {
		return $this->canBeautify() ? htmlentities($text) : $text;
	}

	public function getCommentsPadding(): string {
		return $this->canBeautify() && $this->options['commentsPadding'] ? ' ' : '';
	}

	public function getEncoding(): string {
		return $this->options['encoding'];
	}
	
	public function getIndent(int $depth): string {
		return $this->canBeautify() ? str_repeat($this->options['indent'], $depth) : '';
	}
	
	public function getNl(): string {
		return $this->canBeautify() ? $this->options['nl'] : '';
	}

	public function isNodeTypeEnabled(string $class): bool {
		return match ($class) {
			IfCommentNode::class => !!$this->options['ifComments'],
			CommentNode::class => $this->options['comments'],
			default => true
		};
	}

	private function canBeautify(): bool {
		return $this->options['beautify'];
	}
}
