<?php
	namespace STEIN197\XMLBuilder;

	class CommentNode extends Node {

		protected $content;

		public function __construct(string $text) {
			$this->content = $text;
		}

		public function stringify(int $output, int $mode): string {
			return $output === Builder::OUTPUT_BEAUTIFIED ? "<!-- {$this->content} -->\n" : '';
		}
	}
