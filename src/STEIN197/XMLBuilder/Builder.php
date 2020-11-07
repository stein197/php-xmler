<?php
	namespace STEIN197\XMLBuilder;

	// TODO: parse DOMDocument::loadHTML
	// TODO: Escape quotes
	// TODO: attributes on new line?
	class Builder {

		public const MODE_XML = 1;
		public const MODE_HTML = 2;

		// TODO: private
		var $data = [];
		var $depth;

		public function __construct(array $xmlAttributes = []) {
			if ($xmlAttributes) {
				$attributes = [];
				foreach ($xmlAttributes as $k => $v)
					$attributes[] = "{$k}=\"{$v}\"";
				$attributes = join(' ', $attributes);
				$this->data[] = "<?xml {$attributes}?>";
			}
		}

		public function __toString(): string {
			return $this->getMinified();
		}

		// ->div(function(Builder): null|string)
		// ->div(Builder)
		// ->div(mixed)
		public function __call(string $method, array $arguments): self {
			$tagName = Tag::createTagNameFromMethodName($method);
			$content = $attributes = [];
			foreach ($arguments as $arg) {
				if (is_array($arg)) {
					$attributes = $arg;
				} elseif (is_callable($arg)) {
					$builder = new self;
					$result = $arg($builder);
					if ($result === null)
						$content = array_merge($content, $builder->data);
					else
						$content[] = $result;
				} elseif ($arg instanceof self) {
					$content = array_merge($content, $arg->data);
				} else {
					$content[] = $arg;
				}
			}
			$tag = new Tag($tagName, $content, $attributes);
			$this->data[] = $tag;
			return $this;
		}

		// TODO
		public function getBeautified(int $mode = self::MODE_HTML, bool $useSelfClosing = true, bool $parseXMLString = true): string {
			$result = '';
			foreach ($this->data as $content) {
				if ($content instanceof Tag) {
					$result .= $content->getBeautified($mode, $useSelfClosing, $parseXMLString);
				} else {
					$result .= $content;
				}
				$result .= "\n";
			}
			return $result;
		}
		
		public function getMinified(int $mode = self::MODE_HTML, bool $useSelfClosing = false, bool $parseXMLString = true, bool $attrsOnNewLine = false): string {
			$result = '';
			foreach ($this->data as $content) {
				if ($content instanceof Tag) {
					$result .= $content->getMinified($mode, $useSelfClosing, $parseXMLString);
				} else {
					$result .= $content;
				}
			}
			return $result;
		}
	}
