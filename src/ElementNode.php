<?php
namespace Stein197\Xmler;

final class ElementNode extends Node {

	public function __construct(public string $name, public array $attributes) {}
}
