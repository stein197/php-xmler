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
		public function getBeautified(): string {}

		public function getMinified(int $mode = Builder::MODE_HTML, bool $useSelfClosing = false, bool $parseXMLString = true): string {
			$result = '';
			$attributes = $this->stringifyAttributes();
			if ($this->content) {
				$result .= $attributes ? "<{$this->name} {$attributes}>" : "<{$this->name}>";
				foreach ($this->content as $content) {
					if ($content instanceof self) {
						$result .= $content->getMinified($mode, $useSelfClosing, $parseXMLString);
					} else {
						$result .= $content;
					}
				}
				$result .= "</{$this->name}>";
			} else {
				switch ($mode) {
					case Builder::MODE_HTML:
						if (in_array($this->name, self::$htmlSelfClosingTags)) {
							if ($useSelfClosing)
								$result .= $attributes ? "<{$this->name} {$attributes}/>" : "<{$this->name}/>";
							else
								$result .= $attributes ? "<{$this->name} {$attributes}>" : "<{$this->name}>";
						} else {
							$result = $attributes ? "<{$this->name} {$attributes}></{$this->name}>" : "<{$this->name}></{$this->name}>";
						}
						break;
					case Builder::MODE_XML:
						$result = $attributes ? "<{$this->name} {$attributes}/>" : "<{$this->name}/>";
						break;
				}
			}
			return $result;
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
