<?php
namespace Stein197\Xmler;

final class CommentNode extends Node {

	public function __construct(public string $data) {}

	public function stringify(Formatter $formatter, int $depth): string {
		if (!$formatter->hasComments())
			return '';
		$padding = $formatter->getCommentsPadding();
		return $formatter->getIndent($depth) . '<!--' . $padding . $formatter->encodeEntities($this->data) . $padding . '-->' . $formatter->getNl();
	}
}
