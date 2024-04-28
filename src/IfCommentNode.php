<?php
namespace Stein197\Xmler;

final class IfCommentNode extends Node {

	public function __construct(private readonly string $condition, protected array $children) {}

	public function stringify(Formatter $formatter, int $depth): string {
		if (!$formatter->isNodeTypeEnabled(static::class))
			return '';
		$result = "<!--[if {$this->condition}]>";
		$depthNext = $depth + 1;
		foreach ($this->children as $node)
			$result .= $node->stringify($formatter, $depthNext);
		$result .= '<![endif]-->';
		return $result;
	}
}