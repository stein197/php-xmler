<?php
namespace Stein197\Xmler;

final class ElementNode extends Node {

	public function __construct(public string $name, public array $attributes, protected array $children = []) {}

	public function stringify(Formatter $formatter, int $depth): string {
		$result = "<{$this->name}" . $this->stringifyAttributes($formatter, $depth) . '>';
		$depthNext = $depth + 1;
		foreach ($this->children as $child)
			$result .= $child->stringify($formatter, $depthNext);
		$result .= "</{$this->name}>";
		return $result;
	}

	private function stringifyAttributes(Formatter $formatter, int $depth): string {
		$result = '';
		foreach ($this->attributes as $k => $v)
			$result .= " {$k}={$v}";
		return $result;
	}
}
