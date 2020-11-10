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
					XMLNode::createTagNameFromMethodName('tag')
				],
				[
					'a',
					XMLNode::createTagNameFromMethodName('a')
				],
				[
					'kebab-cased',
					XMLNode::createTagNameFromMethodName('kebabCased')
				],
				[
					'name:spaced',
					XMLNode::createTagNameFromMethodName('name_spaced')
				],
				[
					'name:spaced-kebabed',
					XMLNode::createTagNameFromMethodName('name_spacedKebabed')
				],
				[
					'under_scored',
					XMLNode::createTagNameFromMethodName('under__scored')
				]
			];
		}
	}
