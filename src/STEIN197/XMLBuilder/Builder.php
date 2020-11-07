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

		public function __call(string $method, array $arguments): self {
			return $this;
		}

		public function getBeautified(bool $useSelfClosing = true): string {}
		public function getMinified(bool $useSelfClosing = false): string {}
	}
