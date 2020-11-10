<?php
	namespace STEIN197\XMLBuilder;

	class CDataNode extends Node {

		protected $content;

		public function __construct(string $text) {
			$this->content = $text;
		}

		public function stringify(int $output, int $mode): string {
			return "<![CDATA[{$this->content}]]>".($output === Builder::OUTPUT_BEAUTIFIED ? "\n" : '');
		}
	}
