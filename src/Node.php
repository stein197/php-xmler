<?php
namespace Stein197\Xmler;

abstract class Node {

	protected ?self $parent;

	public function parent(): ?self {
		return $this->parent;
	}
}
