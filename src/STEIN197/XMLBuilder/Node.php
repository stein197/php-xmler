<?php
	namespace STEIN197\XMLBuilder;

	abstract class Node {

		protected $content;

		abstract public function stringify(int $output, int $mode): string;
	}
