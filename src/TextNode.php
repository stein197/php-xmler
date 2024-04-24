<?php
namespace Stein197\Xmler;

final class TextNode extends Node {

	public function __construct(public string $data) {}

	public function stringify(Formatter $formatter, int $depth): string {
		return $formatter->getIndent($depth) . $formatter->encodeEntities($this->data) . $formatter->getNl();
	}
}
