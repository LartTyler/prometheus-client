<?php
	namespace DaybreakStudios\PrometheusClient\Adapter\Exceptions;

	class AdapterException extends \Exception {
		/**
		 * @param int $timeout
		 *
		 * @return static
		 */
		public static function compareAndSwapTimeout($timeout) {
			return new static(
				'A compare and swap operation has exceeded it\'s configured timeout of ' . $timeout . 'ms'
			);
		}
	}