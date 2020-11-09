<?php
	namespace STEIN197\XMLBuilder;

	use PHPUnit\Framework\TestCase;

	class TagTest extends TestCase {

		/**
		 * @dataProvider data_createTagNameFromMethodName
		 */
		public function test_createTagNameFromMethodName(string $expected, string $actual): void {
			$this->assertEquals($expected, $actual);
		}

		public function data_createTagNameFromMethodName(): array {
			return [
				[
					'tag',
					Tag::createTagNameFromMethodName('tag')
				],
				[
					'a',
					Tag::createTagNameFromMethodName('a')
				],
				[
					'kebab-cased',
					Tag::createTagNameFromMethodName('kebabCased')
				],
				[
					'name:spaced',
					Tag::createTagNameFromMethodName('name_spaced')
				],
				[
					'name:spaced-kebabed',
					Tag::createTagNameFromMethodName('name_spacedKebabed')
				],
				[
					'under_scored',
					Tag::createTagNameFromMethodName('under__scored')
				]
			];
		}
	}
