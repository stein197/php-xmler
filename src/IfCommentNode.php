<?php
namespace Stein197\Xmler;

final class IfCommentNode extends Node {

	public function __construct(private readonly string $condition, private readonly array $content) {}

	// TODO
	public function stringify(Formatter $formatter, int $depth): string {
		if (!$formatter->isNodeTypeEnabled(static::class))
			return '';
		return '';
	}
}