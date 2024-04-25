<?php
namespace Stein197\Xmler;

use Closure;
use InvalidArgumentException;
use Stringable;
use function array_map;
use function array_push;
use function is_array;
use function is_callable;
use function is_numeric;
use function is_string;
use function join;
use function mb_convert_encoding;

// The creation is static now
class X extends Stringable {

	public const TRAVERSE_DEPTH_LTR = 1;
	public const TRAVERSE_DEPTH_RTL = 2;
	public const TRAVERSE_BREADTH_LTR = 3;
	public const TRAVERSE_BREADTH_RTL = 4;

	// TODO: Replace with RootNode?
	/** @var Node[] */
	private array $content = [];

	private function __construct(public readonly array $data) {}

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
		// return new IfCommentNode($condition, self::processContent($content));
	}

	public static function new(array | callable $a, ?callable $b = null): self {
		[$data, $f] = is_array($a) ? [$a, $b] : [[], $a];
		if (!$f)
			throw new InvalidArgumentException("No builder callback was provided");
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
	public static function map(int $direction, callable $f): self {}

	private static function getAttributesAndContent(mixed ...$args): array {
		return match (true) {
			isset($args[0]) && is_array($args[0]) && isset($args[1]) && self::isContent($args[1]) => [$args[0], $args[1]],
			isset($args[0]) && self::isContent($args[1]) => [[], $args[0]],
			default => throw new InvalidArgumentException("The first argument should be either an array of a function, the second argument should be a function or be omitted")
		};
	}

	private static function isContent(mixed $arg): bool {
		return is_callable($arg) || $arg instanceof self;
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
	private static function processContent(self $self, mixed $content): array {
		if (is_callable($content))
			return self::new($self->data, $content)->content;
		if ($content instanceof self)
			return (clone $content)->content;
		if ($content instanceof Node)
			return [$content];
		if (is_string($content) || is_numeric($content))
			return [new TextNode((string) $content)];
		if (is_array($content))
			return array_map(fn ($item): mixed => self::processContent($self, $item), $content);
		throw new InvalidArgumentException("Invalid content type. Allowed types are only functions, builders, nodes, arrays and stringables");
	}
}
