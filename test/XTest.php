<?php
namespace Stein197\Xmler;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class XTest extends TestCase {

	#region __call()

	#[Test]
	public function __call_when_no_arguments(): void {
		$this->assertEquals('<html></html>', (string) X::new(function ($b) {
			$b->html();
		}));
	}

	#[Test]
	public function __call_when_only_attributes(): void {
		$this->assertEquals('<html lang=en></html>', (string) X::new(function ($b) {
			$b->html(['lang' => 'en']);
		}));
	}

	#[Test]
	public function __call_when_the_first_argument_is_a_primitive(): void {
		$this->assertEquals('<html>12</html>', (string) X::new(function ($b) {
			$b->html('12');
		}));
	}

	#[Test]
	public function __call_when_the_first_argument_is_a_function(): void {
		$this->assertEquals('<html><body></body></html>', (string) X::new(function ($b) {
			$b->html(function ($b) {
				$b->body();
			});
		}));
	}

	#[Test]
	public function __call_when_the_first_argument_is_a_function_and_returns_a_value(): void {
		$this->assertEquals('<html>12</html>', (string) X::new(function ($b) {
			$b->html(function ($b) {
				$b->body();
				return '12';
			});
		}));
	}

	#[Test]
	public function __call_when_the_first_argument_is_another_x(): void {
		$this->assertEquals('<html><body></body></html>', (string) X::new(function ($b) {
			$x = X::new(function ($b) {
				$b->body();
			});
			$b->html($x);
		}));
	}

	#[Test]
	public function __call_when_the_first_argument_is_a_node(): void {
		$this->assertEquals('<html>Text</html>', (string) X::new(function ($b) {
			$b->html(new TextNode('Text'));
		}));
	}

	#[Test]
	public function __call_when_the_first_argument_is_attributes_and_the_second_is_a_primitive(): void {
		$this->assertEquals('<html>12</html>', (string) X::new(function ($b) {
			$b->html([], '12');
		}));
	}

	#[Test]
	public function __call_when_the_first_argument_is_attributes_and_the_second_is_a_function(): void {
		$this->assertEquals('<html><body></body></html>', (string) X::new(function ($b) {
			$b->html([], function ($b) {
				$b->body();
			});
		}));
	}

	#[Test]
	public function __call_when_the_first_argument_is_attributes_and_the_second_is_a_function_and_returns_a_value(): void {
		$this->assertEquals('<html>12</html>', (string) X::new(function ($b) {
			$b->html([], function ($b) {
				$b->body();
				return '12';
			});
		}));
	}

	#[Test]
	public function __call_when_the_first_argument_is_attributes_and_the_second_is_another_x(): void {
		$this->assertEquals('<html><body></body></html>', (string) X::new(function ($b) {
			$x = X::new(function ($b) {
				$b->body();
			});
			$b->html([], $x);
		}));
	}

	#[Test]
	public function __call_when_the_first_argument_is_attributes_and_the_second_is_a_node(): void {
		$this->assertEquals('<html>Text</html>', (string) X::new(function ($b) {
			$b->html([], new TextNode('Text'));
		}));
	}

	#[Test]
	public function __call_should_throw_an_exception_when_a_method_accepts_invalid_arguments(): void {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode(X::ERR_ARGS_MISMATCH);
		X::new(function ($b) {
			$b->html(null, true);
		});
	}

	#endregion

	#region __clone() TODO

	#endregion

	#region __get() TODO

	#endregion

	#region __invoke() TODO

	#endregion

	#region __toString() TODO

	#endregion

	#region cdata() TODO

	#endregion

	#region comment() TODO

	#endregion

	#region if() TODO

	#endregion

	#region new() TODO

	#endregion

	#region stringify() TODO

	#endregion

	#region map() TODO

	#endregion

	#region $this

	#endregion

	#region Formatter

	#endregion
}
