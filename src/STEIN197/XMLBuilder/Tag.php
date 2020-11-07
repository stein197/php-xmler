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

		// TODO
		public function getBeautified(int $mode = Builder::MODE_HTML, bool $useSelfClosing = false, bool $parseXMLString = true, int $depth = 0): string {
			$attributes = $this->stringifyAttributes();
			if ($this->content) {
				$result = str_repeat("\t", $depth).($attributes ? "<{$this->name} {$attributes}>\n" : "<{$this->name}>\n");
				foreach ($this->content as $content) {
					if ($content instanceof self) {
						$result .= $content->getBeautified($mode, $useSelfClosing, $parseXMLString, $depth + 1);
					} else {
						$result .= str_repeat("\t", $depth + 1).$content."\n";
					}
				}
				return $result.str_repeat("\t", $depth)."</{$this->name}>\n";
			} else {
				switch ($mode) {
					case Builder::MODE_HTML:
						if (in_array($this->name, self::$htmlSelfClosingTags)) {
							if ($useSelfClosing)
								return str_repeat("\t", $depth).($attributes ? "<{$this->name} {$attributes}/>\n" : "<{$this->name}/>\n");
							else
								return str_repeat("\t", $depth).($attributes ? "<{$this->name} {$attributes}>\n" : "<{$this->name}>\n");
						} else {
							return str_repeat("\t", $depth).($attributes ? "<{$this->name} {$attributes}></{$this->name}>\n" : "<{$this->name}></{$this->name}>\n");
						}
						break;
					case Builder::MODE_XML:
						return str_repeat("\t", $depth).($attributes ? "<{$this->name} {$attributes}/>\n" : "<{$this->name}/>\n");
				}
			}
		}

		public function getMinified(int $mode = Builder::MODE_HTML, bool $useSelfClosing = false, bool $parseXMLString = true): string {
			$attributes = $this->stringifyAttributes();
			if ($this->content) {
				$result = $attributes ? "<{$this->name} {$attributes}>" : "<{$this->name}>";
				foreach ($this->content as $content) {
					if ($content instanceof self) {
						$result .= $content->getMinified($mode, $useSelfClosing, $parseXMLString);
					} else {
						$result .= $content;
					}
				}
				return $result."</{$this->name}>";
			} else {
				switch ($mode) {
					case Builder::MODE_HTML:
						if (in_array($this->name, self::$htmlSelfClosingTags)) {
							if ($useSelfClosing)
								return $attributes ? "<{$this->name} {$attributes}/>" : "<{$this->name}/>";
							else
								return $attributes ? "<{$this->name} {$attributes}>" : "<{$this->name}>";
						} else {
							return $attributes ? "<{$this->name} {$attributes}></{$this->name}>" : "<{$this->name}></{$this->name}>";
						}
					case Builder::MODE_XML:
						return $attributes ? "<{$this->name} {$attributes}/>" : "<{$this->name}/>";
				}
			}
		}

		private function stringifyAttributes(): string {
			$result = [];
			foreach ($this->attributes as $k => $v)
				$result[] = "{$k}=\"{$v}\"";
			return join(' ', $result);
		}

		public static function createTagNameFromMethodName(string $name): string {
			$namespaced = explode('_', $name);
			$kebabCased = array_map(function($v) {
				return strtolower(preg_replace('/[A-Z]/', '-$0', $v));
			}, $namespaced);
			return trim(join(':', $kebabCased), '-');
		}
	}
