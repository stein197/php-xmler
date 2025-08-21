<?php

namespace Stein197\Xmler;

use InvalidArgumentException;
use function preg_match;

final class Node {

	private const string NAME_REGEX = '/^[0-9a-z\\.\\-_:]+$/i';

	public string $name {
		get => $this->name;
		set {
			if (!preg_match(static::NAME_REGEX, $value))
				throw new InvalidArgumentException("The value '{$value}' is not valid XML element name");
			$this->name = $value;
		}
	}

	/**
	 * @param string $name
	 * @param array<string,string> $attributes
	 */
	public function __construct(
		string $name,
		protected array $attributes = [],
	) {
		$this->name = $name;
	}

	public function getAttribute(string $name): ?string {
		return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
	}

	public function setAttribute(string $name, ?string $value): void {
		if ($value === null)
			unset($this->attributes[$name]);
		else
			$this->attributes[$name] = $value;
	}

	/**
	 * @return array<string,string>
	 */
	public function getAttributes(): array {
		return $this->attributes;
	}

	/**
	 * @param array<string,string> $attributes
	 * @return void
	 */
	public function setAttributes(array $attributes): void {
		$this->attributes = $attributes;
	}

	/**
	 * @param array<string,string> $attributes
	 * @return void
	 */
	public function mergeAttributes(array $attributes): void {
		$this->attributes = [...$this->attributes, ...$attributes];
	}
}
