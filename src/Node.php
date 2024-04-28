<?php
namespace Stein197\Xmler;

abstract class Node {

	/** @var self[] */
	protected array $children = [];
	protected ?self $parent;

	public function __clone(): void {
		$this->parent = null;
		foreach ($this->children as &$child)
			$child = clone $child;
	}

	public function depth(): int {
		$i = 0;
		$obj = $this->parent;
		while ($obj !== null) {
			$i++;
			$obj = $obj->parent;
		}
		return $i;
	}

	public function leaf(): bool {
		return !$this->children;
	}

	public function parent(): ?self {
		return $this->parent;
	}

	public function root(): self {
		$obj = $this;
		while ($obj->parent !== null)
			$obj = $obj->parent;
		return $obj;
	}

	public abstract function stringify(Formatter $formatter, int $depth): string;
}
