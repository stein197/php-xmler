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
		'commentsPadding' => true,
		'closeVoid' => true,
		'emptyAttributes' => 'preserve', // preserve, remove, keepOnlyName
		'emptyElements' => 'selfClose', // selfClose, pairClose (<div/>, <div></div>)
		'emptyElementsNl' => false, // <div></div>, <div>\n</div>
		'encode' => true,
		'encoding' => 'UTF-8',
		'indent' => "\t",
		'indentAttributes' => false,
		'indentCloseBracket' => '', // false, 'newline-same', 'newline-back-indent'
		'indentTextContent' => false,
		'inlineElements' => ['br', 'hr', 'span'],
		'leafNodes' => 'close', // selfclose, doubleclose, delete
		'mode' => 'html', // html, xhtml, xml
		'nl' => "\n", // \n, \r\n, \r
		'singleTextOnNewLine' => false,
		'spaceBeforeSelfClose' => true,
		'uppercase' => false,
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

	public function hasComments(): bool {
		return $this->options['comments'];
	}

	private function canBeautify(): bool {
		return $this->options['beautify'];
	}
}
