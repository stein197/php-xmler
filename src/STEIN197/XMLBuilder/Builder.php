<?php
	namespace STEIN197\XMLBuilder;

	// TODO: parse DOMDocument::loadHTML?
	// TODO: attributes on new line?
	// TODO: Add CDATA?
	// TODO: Add comments support?
	class Builder {

		public const MODE_XML = 1;
		public const MODE_HTML = 2;

		public const OUTPUT_MINIFIED = 1;
		public const OUTPUT_BEAUTIFIED = 2;

		private $data = [];

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
			return $this->stringify(self::OUTPUT_MINIFIED, self::MODE_HTML);
		}

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

		public function stringify(int $stringify, int $mode): string {
			$result = '';
			foreach ($this->data as $content) {
				if ($content instanceof Tag)
					$result .= $content->stringify($stringify, $mode);
				else
					$result .= $content;
				if ($stringify === self::OUTPUT_BEAUTIFIED)
					$result .= "\n";
			}
			return $result;
		}
	}
