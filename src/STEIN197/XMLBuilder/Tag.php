<?php
	namespace STEIN197\XMLBuilder;

	class Tag {

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
		private $content;
		private $attributes;

		public function __construct(string $name, $content = [], array $attributes = []) {
			$this->name = $name;
			$this->content = $content;
			$this->attributes = $attributes;
		}

		public function stringify(int $stringify, int $mode, int $depth = 0): string {
			$attributes = $this->stringifyAttributes();
			$tabulation = $stringify === Builder::OUTPUT_MINIFIED ? '' : str_repeat("\t", $depth);
			$newline = $stringify === Builder::OUTPUT_MINIFIED ? '' : "\n";
			if ($this->content) {
				if ($stringify === Builder::OUTPUT_BEAUTIFIED && sizeof($this->content) === 1 && !($this->content[0] instanceof self)) {
					return $tabulation.($attributes ? "<{$this->name} {$attributes}>" : "<{$this->name}>").$this->content[0]."</{$this->name}>".$newline;
				} else {
					$result = $tabulation.($attributes ? "<{$this->name} {$attributes}>" : "<{$this->name}>").$newline;
					foreach ($this->content as $content) {
						if ($content instanceof self) {
							$result .= $content->stringify($stringify, $mode, $depth + 1);
						} else {
							$result .= $tabulation.$content.$newline;
						}
					}
					return $result.$tabulation."</{$this->name}>".$newline;
				}
			} else {
				switch ($mode) {
					case Builder::MODE_HTML:
						if (in_array($this->name, self::$htmlSelfClosingTags)) {
							$slash = $stringify === Builder::OUTPUT_MINIFIED ? '' : '/';
							return $tabulation.($attributes ? "<{$this->name} {$attributes}{$slash}>" : "<{$this->name}{$slash}>").$newline;
						} else {
							return $tabulation.($attributes ? "<{$this->name} {$attributes}></{$this->name}>" : "<{$this->name}></{$this->name}>").$newline;
						}
					case Builder::MODE_XML:
						return $tabulation.($attributes ? "<{$this->name} {$attributes}/>" : "<{$this->name}/>").$newline;
				}
			}
		}

		private function stringifyAttributes(): string {
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
