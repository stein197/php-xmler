<?php
	namespace STEIN197\XMLBuilder;

	class Comment {

		private $content;

		public function __construct(string $content) {
			$this->content = $content;
		}

		public function stringify(): string {
			return "<!-- {$this->content} -->";
		}
	}
