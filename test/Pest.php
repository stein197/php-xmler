<?php

use PHPUnit\Framework\TestCase;

pest()->extend(TestCase::class);

function array_map_keys(array $array, callable $f): array {
	$result = [];
	foreach ($array as $k => $v) {
		[$kNew, $vNew] = $f($k, $v);
		$result[$kNew] = $vNew;
	}
	return $result;
}
