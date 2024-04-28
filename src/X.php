<?php
namespace Stein197\Xmler;

use Closure;
use InvalidArgumentException;
use Stringable;
use function array_push;
use function is_array;
use function is_callable;
use function is_numeric;
use function is_string;
use function join;
use function json_encode;
use function mb_convert_encoding;
use function sizeof;

// No dynamic methods because of the builder method names
// $this
// Parsing
// Modificiation
class X implements Stringable {

	/**
	 * An error code for an exception, when wrong arguments were passed to dynamic X methods. The first argument is
	 * either an array or a content, the second one is either a content or omitted, or both arguments could be omitted
	 * (an empty element).
	 * ```php
	 * X::new(function ($b) {
	 * 	$b->html(1, 2); // An exception
	 * });
	 * ```
	 */
	public const ERR_ARGS_MISMATCH = 1;

	/**
	 * An error code for an exception, when no builder function was passed to `X::new()` method.
	 * ```php
	 * X::new(); // An exception
	 * ```
	 */
	public const ERR_NO_FUNCTION = 2; // TODO: Test
	public const TRAVERSE_DEPTH_LTR = 1;
	public const TRAVERSE_DEPTH_RTL = 2;
	public const TRAVERSE_BREADTH_LTR = 3;
	public const TRAVERSE_BREADTH_RTL = 4;

	// TODO: Replace with RootNode?
	/** @var Node[] */
	private array $content = [];

	private function __construct(private readonly array $data) {}

	public function __call(string $name, array $args): void {
		[$attributes, $content] = self::getAttributesAndContent(...$args);
		$attributes = self::processAttributes($attributes);
		$content = self::processContent($this, $content);
		$this->content[] = new ElementNode($name, $attributes, $content);
	}

	public function __clone(): void {
		foreach ($this->content as &$child)
			$child = clone $child;
	}

	public function __get(string $name): mixed {
		return @$this->data[$name];
	}

	public function __invoke(string | self | Node ...$args): void {
		foreach ($args as $arg)
			array_push($this->content, ...match (true) {
				is_string($arg) => [new TextNode($arg)],
				$arg instanceof self => (clone $arg)->content,
				$arg instanceof Node => [$arg]
			});
	}

	public function __toString(): string {
		return self::stringify($this);
	}

	public static function cdata(string $text): CDataNode {
		return new CDataNode($text);
	}

	public static function comment(string $text): CommentNode {
		return new CommentNode($text);
	}

	// TODO
	public static function if(string $condition, string | self | Node | callable $content): IfCommentNode {
		return new IfCommentNode($condition, self::processContent($content));
	}

	public static function new(array | callable $a, ?callable $b = null): self {
		[$data, $f] = is_array($a) ? [$a, $b] : [[], $a];
		if (!$f)
			throw new InvalidArgumentException("No builder callback was provided", self::ERR_NO_FUNCTION);
		$x = new self($data);
		$result = Closure::fromCallable($f)->bindTo($x)($x);
		if (is_string($result))
			$x->content = [new TextNode($result)];
		return $x;
	}

	public static function stringify(self $x, array $options = [], int $depth = 0): string {
		$formatter = new Formatter($options);
		$result = '';
		foreach ($x->content as $node)
			$result .= $node->stringify($formatter, $depth);
		return mb_convert_encoding($result, $formatter->getEncoding());
	}

	// TODO
	public static function map(self $x, int $direction, callable $f): self {
		return $x;
	}

	private static function getAttributesAndContent(mixed ...$args): array {
		return match (true) {
			sizeof($args) === 0 => [[], null],
			sizeof($args) === 1 && self::isContent(@$args[0]) => [[], @$args[0]],
			sizeof($args) === 1 && is_array(@$args[0]) => [$args[0], null],
			sizeof($args) === 2 && is_array(@$args[0]) && self::isContent(@$args[1]) => [@$args[0], @$args[1]],
			default => throw new InvalidArgumentException("Invalid arguments. Allowed signatures for the method are:\n\t(),\n\t(array),\n\t(string | callable | X | Node),\n\t(array, string | callable | X | Node).\nProvided arguments: " . json_encode($args), self::ERR_ARGS_MISMATCH)
		};
	}

	// This method is related to processContent()
	private static function isContent(mixed $arg): bool {
		return $arg === null || $arg instanceof self || $arg instanceof Node || is_callable($arg) || is_string($arg) || is_numeric($arg);
	}

	private static function processAttributes(array $attributes): array {
		if (isset($attributes['style']) && is_array($attributes['style'])) {
			$result = [];
			foreach ($attributes['style'] as $k => $v)
				$result[] = is_string($k) ? "{$k}: {$v}" : $k;
			$attributes['style'] = join('; ', $result);
		}
		if (isset($attributes['class']) && is_array($attributes['class'])) {
			$result = [];
			foreach ($attributes['class'] as $k => $v)
				if (is_string($k) && $v)
					$result[] = $k;
				elseif (!is_string($k))
					$result[] = $v;
			$attributes['class'] = join(' ', $result);
		}
		$attributes = self::processComplexAttributes($attributes, 'data');
		$attributes = self::processComplexAttributes($attributes, 'aria');
		return $attributes;
	}

	private static function processComplexAttributes(array $attributes, string $name): array {
		if (!is_array(@$attributes[$name]))
			return $attributes;
		foreach ($attributes[$name] as $k => $v)
			$attributes["{$name}-{$k}"] = $v;
		unset($attributes[$name]);
		return $attributes;
	}

	/**
	 * @return Node[]
	 */
	// This method is related to isContent()
	private static function processContent(self $self, mixed $content): array {
		if ($content === null)
			return [];
		if ($content instanceof self)
			return (clone $content)->content;
		if ($content instanceof Node)
			return [$content];
		if (is_callable($content))
			return self::new($self->data, $content)->content;
		if (is_string($content) || is_numeric($content))
			return [new TextNode((string) $content)];
		throw new InvalidArgumentException("Invalid content type. Allowed types are only functions, builders, nodes, arrays and stringables", self::ERR_CONTENT_TYPE_MISMATCH);
	}
}
