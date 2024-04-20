<?php
namespace Stein197\Xmler;

abstract class Node {

	protected ?self $parent;

	public function depth(): int {
		$i = 0;
		$obj = $this->parent;
		while ($obj !== null) {
			$i++;
			$obj = $obj->parent;
		}
		return $i;
	}

	public function parent(): ?self {
		return $this->parent;
	}
}
