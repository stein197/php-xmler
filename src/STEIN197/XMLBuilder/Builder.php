<?php
	namespace STEIN197\XMLBuilder;

	class Builder {

		private const TOKEN_TAG_SELF_CLOSING = 0;
		private const TOKEN_TAG_OPEN = 1;
		private const TOKEN_TAG_CLOSE = 2;
		private const TOKEN_TAG_XML = 3;
		private const TOKEN_BUILDER = 4;
		private const TOKEN_CONTENT = 5;

		private const MODE_XML = 1;
		private const MODE_HTML = 2;

		private $data = [];
		private $mode;
		private $useSelfClosing = true;

		public function __construct(int $mode = self::MODE_HTML, array $xmlAttributes = []) {
			$this->mode = $mode;
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
