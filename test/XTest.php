<?php
namespace Stein197\Xmler;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

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

	#[Test]
	public function __call_with_a_custom_name(): void {
		$this->assertEquals('<custom:tag></custom:tag>', (string) X::new(function ($b) {
			$b->{'custom:tag'}();
		}));
	}

	#endregion

	#region __clone() TODO

	#endregion

	#region __get()

	#[Test]
	public function __get_should_return_null_when_data_does_not_exist(): void {
		$this->assertEquals('<html></html>', (string) X::new(function ($b) {
			$b->html($b->a);
		}));
	}

	#[Test]
	public function __get_should_return_a_value_by_a_key(): void {
		$this->assertEquals('<html>1</html>', (string) X::new(['a' => 1], function ($b) {
			$b->html($b->a);
		}));
	}

	#[Test]
	public function __get_should_pass_data_further(): void {
		$this->assertEquals('<html><body>1</body></html>', (string) X::new(['a' => 1], function ($b) {
			$b->html(function ($b) {
				$b->body($b->a);
			});
		}));
	}

	#endregion

	#region __invoke()

	#[Test]
	public function __invoke_should_append_a_string(): void {
		$this->assertEquals('<html>Text</html>', (string) X::new(function ($b) {
			$b->html(function ($b) {
				$b('Text');
			});
		}));
	}

	#[Test]
	public function __invoke_should_append_a_node(): void {
		$this->assertEquals('<html>Text</html>', (string) X::new(function ($b) {
			$b->html(function ($b) {
				$b(new TextNode('Text'));
			});
		}));
	}

	#[Test]
	public function __invoke_should_append_another_X(): void {
		$this->assertEquals('<html><body></body></html>', (string) X::new(function ($b) {
			$b->html(function ($b) {
				$x = X::new(function ($b) {
					$b->body();
				});
				$b($x);
			});
		}));
	}

	#[Test]
	public function __invoke_should_throw_an_exception_when_invalid_type(): void {
		$this->expectException(TypeError::class);
		X::new(function ($b) {
			$b([]);
		});
	}

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
