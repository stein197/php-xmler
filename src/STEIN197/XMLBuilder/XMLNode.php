<?php
	namespace STEIN197\XMLBuilder;

	class XMLNode extends Node {

		private static $htmlSelfClosingTags = [
			'area',
			'base',
			'br',
			'col',
			'embed',
			'hr',
			'img',
			'input',
			'link',
			'meta',
			'param',
			'source',
			'track',
			'wbr',
			'command',
			'keygen',
			'menuitem',
		];

		private $name;
		protected $content;
		private $attributes;

		public function __construct(string $name, $content = [], array $attributes = []) {
			$this->name = $name;
			$this->content = $content;
			$this->attributes = $attributes;
		}

		public function stringify(int $output, int $mode, int $depth = 0): string {
			$attributes = $this->stringifyAttributes($output, $depth + 1);
			$isMinified = $output === Builder::OUTPUT_MINIFIED;
			if ($attributes)
				$attributes = ' '.$attributes;
			$tabulation = $isMinified ? '' : str_repeat("\t", $depth);
			$newline = $isMinified ? '' : "\n";
			if ($this->content) {
				if (sizeof($this->content) === 1 && !($this->content[0] instanceof self)) {
					return $tabulation."<{$this->name}{$attributes}>".$this->content[0]->stringify($output, $mode)."</{$this->name}>".$newline;
				}
				$result = $tabulation."<{$this->name}{$attributes}>".$newline;
				foreach ($this->content as $content) {
					$result .= ($content instanceof self || $isMinified ? '' : $tabulation."\t").$content->stringify($output, $mode, $depth + 1);
				}
				return $result.$tabulation."</{$this->name}>".$newline;
			} else {
				switch ($mode) {
					case Builder::MODE_HTML:
						if (in_array($this->name, self::$htmlSelfClosingTags)) {
							$slash = $isMinified ? '' : '/';
							return $tabulation."<{$this->name}{$attributes}{$slash}>".$newline;
						} else {
							return $tabulation."<{$this->name}{$attributes}></{$this->name}>".$newline;
						}
					case Builder::MODE_XML:
						return $tabulation."<{$this->name}{$attributes}/>".$newline;
				}
			}
		}

		private function stringifyAttributes(int $output, int $depth): string {
			$result = [];
			foreach ($this->attributes as $k => $v)
				$result[] = $k.'="'.htmlspecialchars($v).'"';
			return join(' ', $result);
		}

		public static function createTagNameFromMethodName(string $name): string {
			$namespaced = preg_split('/(?<=[[:alnum:]])_(?=[[:alnum:]])/', $name);
			$cased = array_map(function($v) {
				$v = str_replace('__', '_', $v);
				return strtolower(preg_replace('/[A-Z]/', '-$0', $v));
			}, $namespaced);
			return trim(join(':', $cased), '-');
		}
	}
