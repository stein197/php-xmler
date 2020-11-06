<?php
	namespace STEIN197\XMLBuilder;

	function _(...$arguments): Builder {
		return new Builder(...$arguments);
	}

	// TODO: Callbacks like this?
	_()
	->html()
	->body(function($body) {
		$body
		->header()
		->main(function($main) {
			$main
			->section(function($section) {
				$section
				->div(function($div) {
					$div
					->ul(function($ul) {
						while (true) {
							$ul->li(function($li) {
								$li->a();
							});
						}
					});
					if (true) {
						$div->p();
					}
				});
			});
		})
		->footer('content', [
			'class' => 'dd'
		]);
	});

	_(['version'=>1])
	->svg(function($svg) {
		$svg
		->xlink_linkTag();
	})
