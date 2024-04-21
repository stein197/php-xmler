<?php
namespace Stein197\Xmler;

use Closure;
use InvalidArgumentException;
use Stringable;
use function is_array;

class Xmler extends Stringable {

	public const TRAVERSE_DEPTH_LTR = 1;
	public const TRAVERSE_DEPTH_RTL = 2;
	public const TRAVERSE_BREADTH_LTR = 3;
	public const TRAVERSE_BREADTH_RTL = 4;

	// https://www.w3.org/TR/2011/WD-html-markup-20110113/syntax.html#void-element
	private const VOID_ELEMENTS = ['area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr'];
	private const OPTIONS_DEFAULT = [
		'beautify' => true,
		'comments' => true,
		'commentsPadding' => true,
		'cdata' => true,
		'cdataNewLine' => true,
		'closeVoid' => true,
		'emptyAttributes' => 'preserve', // preserve, remove, keepOnlyName
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

	/** @var Node[] */
	private array $children = [];

	private function __construct(public readonly array $data) {}

	public function __call(string $name, array $args): void {}

	public function __clone(): void {
		foreach ($this->children as &$child)
			$child = clone $child;
	}

	public function __invoke(string | self | Node ...$args): void {
		foreach ($args as $arg)
			array_push($this->children, match (true) {
				is_string($arg) => new TextNode($arg),
				$arg instanceof self => (clone $arg)->children,
				$arg instanceof Node => $arg
			});
	}

	public function __toString(): string {
		return self::stringify($this, self::OPTIONS_DEFAULT, 0);
	}

	public static function cdata(string $text): CDataNode {
		return new CDataNode($text);
	}

	public static function comment(string $text): CommentNode {
		return new CommentNode($text);
	}

	public static function new(array | callable $a, ?callable $b = null): self {
		[$data, $f] = is_array($a) ? [$a, $b] : [[], $a];
		if (!$f)
			throw new InvalidArgumentException("No builder callback was provided");
		$xmler = new self($data);
		Closure::fromCallable($f)->bindTo($xmler)($xmler);
		return $xmler;
	}

	// public static function parse(string $source): self {} ?

	public static function stringify(self $xmler, array $options = self::OPTIONS_DEFAULT, int $depth = 0): string {}

	public static function traverse(int $direction, callable $f): void {}
}
