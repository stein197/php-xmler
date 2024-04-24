<?php
namespace Stein197\Xmler;

final class ElementNode extends Node {

	public function __construct(public string $name, public array $attributes, private array $children = []) {}

	// TODO
	public function stringify(Formatter $formatter, int $depth): string {
		return '';
	}
}
