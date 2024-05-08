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
// TODO: ability to shorten tag names with reflection (function (X $x, $div, $p) {$x->div();$div();$p();})
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

	/**
	 * Create a dynamic XML structure. Each method call creates a new element in the structure. The name of the method
	 * is the name of an element, which means that the method name is case-sensetive. This also means, that it is
	 * possible to create elements with special characters in names (i.e. colons). Each method accepts zero, one or
	 * two arguments. If there are no arguments, an empty element is created. If the amount of arguments is 1, it could
	 * be an array (representing attributes) or a string, a function, an instance of `X` or an instance of `Node` (
	 * representing content). If two arguments are passed, then the first one must represent arguments and the second
	 * one must represent content. Otherwise an exception is thrown.
	 * 
	 * ```php
	 * $b->html();                         // <html />
	 * $b->html(['lang' => 'en']);         // <html lang="en" />
	 * $b->html('Text');                   // <html>Text</html>
	 * $b->html(['lang' => 'en'], 'Text'); // <html lang="en">Text</html>
	 * $b->html(new stdClass);             // An exception
	 * ```
	 * 
	 * To create attributes, pass an array, where each key is a name of an attribute. Each value must be a string.
	 * The special case is entries for attributes class, style, data and aria. Instead of string, it's possible to pass
	 * another array as a value.
	 * 
	 * ```php
	 * $b->html(['lang' => 'en']);                         // <html lang="en" />
	 * $b->html(['class' => ['a', 'b']]);                  // <html class="a b" />
	 * $b->html(['class' => ['a' => true, 'b' => false]]); // <html class="a" />
	 * $b->html(['style' => ['font-size' => '12px']]);     // <html style="font-size: 12px" />
	 * $b->html(['data' => ['count' => 10]]);              // <html data-count="10" />
	 * $b->html(['aria' => ['hidden' => 'true']]);         // <html aria-hidden="true" />
	 * ```
	 * 
	 * To create a content, next types are allowed
	 * 
	 * ```php
	 * $b->html('Text');               // <html>Text</html>, primitives
	 * $b->html(new TextNode('Text')); // <html>Text</html>, instances of the class `Node`
	 * $b->html(X::new(function ($b) {
	 * 	$b->body();
	 * }));                            // <html><body></body></html>, instances of the class `X`
	 * $b->html(function ($b) {
	 * 	$b->body();
	 * });                             // <html><body></body></html>, functions - the most common value
	 * ```
	 * 
	 * It's also possible to use `$this` instead of the passed argument
	 * 
	 * ```php
	 * X::new(function ($b) {
	 * 	$this->html(); // The same as calling $b->html()
	 * });
	 * ```
	 * @param string $name The name of an element.
	 * @param array $args Attributes and content for the element.
	 */
	public function __call(string $name, array $args): void {
		[$attributes, $content] = self::getAttributesAndContent(...$args);
		$attributes = self::processAttributes($attributes);
		$content = self::processContent($this->data, $content);
		$this->content[] = new ElementNode($name, $attributes, $content);
	}

	/**
	 * Perform a complete deep cloning.
	 */
	public function __clone(): void {
		foreach ($this->content as &$child)
			$child = clone $child;
	}

	/**
	 * Return a value from the array passed to the contructor through the method `new` or `null` if the value not found.
	 * ```php
	 * X::new(['a' => 1, 'b' => 2], function ($b) {
	 * 	$b->html(function ($b) {
	 * 		$b->body($b->a); // <body>1</body>
	 * 	});
	 * });
	 * ```
	 * @param string $name Name of a key of the passed array.
	 * @return mixed The value of the key or `null` if there is no value with the associated key.
	 */
	public function __get(string $name): mixed {
		return @$this->data[$name];
	}

	/**
	 * Add a content directory into the current node. This doesn't deepen the structure, instead it adds an argument
	 * into the current layer.
	 * ```php
	 * X::new(function () {
	 * 	$this->html(function () {
	 * 		$this('Text');          // Add a string 'Text'
	 * 		$this(X::cdata('abc')); // Add a CDATA node <![CDATA[abc]]>
	 * 	});
	 * });
	 * ```
	 */
	public function __invoke(string | self | Node ...$args): void {
		foreach ($args as $arg)
			array_push($this->content, ...match (true) {
				is_string($arg) => [new TextNode($arg)],
				$arg instanceof self => (clone $arg)->content,
				$arg instanceof Node => [$arg]
			});
	}

	/**
	 * Stringify the current structure. Use options by default.
	 * @see \Stein197\Xmler\X::stringify()
	 */
	public function __toString(): string {
		return self::stringify($this);
	}

	/**
	 * Add a CDATA section.
	 * @param string $text Text inside the CDATA.
	 * @return CDataNode CDATA node.
	 * ```php
	 * X::new(function ($b) {
	 * 	$b->html(function ($b) {
	 * 		$b(X::cdata('Text'));
	 * 	});
	 * }); // <html><![CDATA[Text]]></html>
	 * ```
	 */
	public static function cdata(string $text): CDataNode {
		return new CDataNode($text);
	}

	/**
	 * Add a comment to the XML output.
	 * @param string $text Comment string.
	 * @return CommentNode Comment node.
	 * ```php
	 * X::new(function ($b) {
	 * 	$b->html(function ($b) {
	 * 		$b(X::comment('Comment'));
	 * 	});
	 * }); // <html><!--Comment--></html>
	 * ```
	 */
	public static function comment(string $text): CommentNode {
		return new CommentNode($text);
	}

	/**
	 * Add a conditional comment.
	 * @param string $condition Condition to be used in the conditional comment.
	 * @param string|self|Node|callable $content Content to be used inside the comment.
	 * @return IfCommentNode If comment node.
	 * ```php
	 * X::new(function ($b) {
	 * 	$b->html(function ($b) {
	 * 		$b(X::if('lt IE 9', function ($b) {
	 * 			$b->body();
	 * 		}));
	 * 	});
	 * }); // <html><!--[if lt IE 9]><body></body><![endif]--></html>
	 * ```
	 */
	public static function if(string $condition, string | self | Node | callable $content): IfCommentNode {
		return new IfCommentNode($condition, self::processContent([], $content));
	}

	/**
	 * The main function to create an XML structure. The method has two signature:
	 * - new(array $data, callable $f)
	 * - new(callable $f)
	 * 
	 * The first signature uses the first argument as an array of variables that can be used later deeply in the
	 * structure. The second one uses no variables.
	 * @param array|callable $a An array of variables or a builder function.
	 * @param null|callable $b An builder function or `null` if 
	 * @return X An XML structure.
	 * @throws InvalidArgumentException If no builder function was provided.
	 * ```php
	 * // Use the first argument as an array of variables
	 * X::new(['a' => 1], function ($b) {
	 * 	$b->html($b->a); // <html>1</html>
	 * });
	 * 
	 * // Use only the builder function
	 * X::new(function ($b) {
	 * 	$b->html($b->a); // <html />
	 * });
	 * ```
	 */
	public static function new(array | callable $a, ?callable $b = null): self {
		[$data, $f] = is_array($a) ? [$a, $b] : [[], $a];
		if (!$f)
			throw new InvalidArgumentException('No builder callback was provided', self::ERR_NO_FUNCTION);
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
	private static function processContent(array $data, mixed $content): array {
		if ($content === null)
			return [];
		if ($content instanceof self)
			return (clone $content)->content;
		if ($content instanceof Node)
			return [$content];
		if (is_callable($content))
			return self::new($data, $content)->content;
		if (is_string($content) || is_numeric($content))
			return [new TextNode((string) $content)];
		throw new InvalidArgumentException("Invalid content type. Allowed types are only functions, builders, nodes and stringables", self::ERR_ARGS_MISMATCH);
	}
}
