<?php
namespace Stein197\Xmler;

final class CDataNode extends Node {

	public function __construct(public string $data) {}

	public function stringify(Formatter $formatter, int $depth): string {
		return $formatter->getIndent($depth) . '<![CDATA[' . $this->data . ']]>' . $formatter->getNl();
	}
}
