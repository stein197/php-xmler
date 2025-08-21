<?php

use PHPUnit\Framework\TestCase;
use Stein197\Xmler\Node;

describe('name', function (): void {
	test('should throw exception when name is not valid', function (string $name): void {
		/** @var TestCase $this */
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("The value '{$name}' is not valid XML element name");
		new Node($name);
	})->with([
		'empty' => [''],
		'spaces' => ['first second'],
		'punctuation' => ['$-2'],
	]);

	test('should set and get', function (string $name): void {
		/** @var TestCase $this */
		$node = new Node($name);
		$this->assertEquals($name, $node->name);
	})->with(array_map_keys([
		['div'],
		['x-field.checkbox'],
		['f:if'],
		['a_b'],
	], fn ($k, $v) => [$v[0], $v]));
});

test('getAttribute()', function (?string $expected, string $key): void {
	/** @var TestCase $this */
	$this->assertEquals($expected, new Node('div', [
		'key1' => 'value',
		'key2' => '',
	])->getAttribute($key));
})->with([
	'string' => ['value', 'key1'],
	'empty string' => ['', 'key2'],
	'null' => [null, 'key3'],
]);

test('setAttribute()', function (?string $value): void {
	/** @var TestCase $this */
	$node = new Node('div');
	$node->setAttribute('key', $value);
	$this->assertEquals($value, $node->getAttribute('key'));
})->with([
	'string' => ['value'],
	'empty string' => [''],
	'null' => [null],
]);

test('getAttributes()', function (array $data): void {
	/** @var TestCase $this */
	$this->assertEquals($data, new Node('div', $data)->getAttributes());
})->with([
	'empty' => [[]],
	'list' => [[1, 2]],
	'map' => [['a' => 'first', 'b' => 'second']],
]);

test('setAttributes()', function (array $data): void {
	/** @var TestCase $this */
	$node = new Node('div');
	$node->setAttributes($data);
	$this->assertEquals($data, $node->getAttributes());
})->with([
	'empty' => [[]],
	'list' => [[1, 2]],
	'map' => [['a' => 'first', 'b' => 'second']],
]);

test('mergeAttributes()', function (array $merge, array $expected): void {
	/** @var TestCase $this */
	$node = new Node('div', [
		'a' => 'first',
		'b' => '2',
	]);
	$node->mergeAttributes($merge);
	$this->assertEquals($expected, $node->getAttributes());
})->with([
	'empty' => [[], ['a' => 'first', 'b' => '2']],
	'override' => [['b' => 'second'], ['a' => 'first', 'b' => 'second']],
	'extra' => [['c' => 'third'], ['a' => 'first', 'b' => '2', 'c' => 'third']],
]);
