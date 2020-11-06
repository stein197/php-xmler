<?php
	namespace STEIN197\XMLBuilder;

	function _(...$arguments): Builder {
		return new Builder(...$arguments);
	}
