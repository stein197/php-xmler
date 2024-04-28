<?php
namespace Stein197\Xmler;

use function htmlentities;
use function str_repeat;

final class Formatter {

	// https://www.w3.org/TR/2011/WD-html-markup-20110113/syntax.html#void-element
	private const VOID_ELEMENTS = ['area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
	private const OPTIONS_DEFAULT = [
		// TODO
		'beautify' => false,
		// TODO
		'comments' => true,
		// TODO
		'commentsPadding' => true, // <!-- ... -->, <!--...-->
		// TODO
		'closeVoid' => true, // <img />, <img>
		// TODO
		'emptyAttributes' => 'preserve', // attr, attr="", ''
		// TODO
		'emptyElements' => 'selfClose', // <div/>, <div></div>
		// TODO
		'emptyElementsNl' => false, // <div></div>, <div>\n</div>
		// TODO
		'encode' => true, // &, &amp;
		// TODO
		'encoding' => 'UTF-8',
		// TODO
		'ifComments' => true,
		// TODO
		'indent' => "\t",
		// TODO
		'indentAttributes' => false, // <div attr>, <div\nattr>
		// TODO
		'indentCloseBracket' => '', // <div>, <div\n>, <div\n\t>
		// TODO
		'indentIfComment' => 'indent', // <!--[if]>..., <!--[if]>\n..., <!--[if]>\n\t..., false
		// TODO
		'indentScriptContent' => true,
		// TODO
		'indentSelfClosedBracked' => true, // <hr/>, <hr />
		// TODO
		'indentStyleContent' => true,
		// TODO
		'indentTextContent' => false,
		// TODO
		'inlineElements' => ['br', 'hr', 'span'], // text<br/>content
		// TODO
		'leafNodes' => 'close', // selfclose, doubleclose, delete / <div/>, <div></div>, ''
		// TODO
		'mode' => 'html', // html, xhtml, xml
		// TODO
		'nl' => "\n", // \n, \r\n, \r
		// TODO
		'padStyle' => true, // a: 1; b: 2, a:1; b:2, a:1,b:2, a: 1,b: 2
		// TODO
		'quotes' => '"', // ", ', false
		// TODO
		'singleTextOnNewLine' => false, // <p>text</p>, <p>\n\ttext\n</p>
		// TODO
		'uppercaseAttributeNames' => false, // <div ATTR>
		// TODO
		'uppercaseElementNames' => false, // <DIV attr>
		// TODO
		'uppercaseIfComments' => false // <!--[IF ...]>, <!--[if ...]>
	];

	private readonly array $options;

	public function __construct(array $options) {
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
			IfCommentNode::class => $this->options['ifComments'],
			CommentNode::class => $this->options['comments'],
			default => true
		};
	}

	private function canBeautify(): bool {
		return $this->options['beautify'];
	}
}
