<?php
	namespace DaybreakStudios\PrometheusClient\Adapter\Apcu;

	final class FloatSupport {
		/**
		 * FloatSupport constructor.
		 */
		private function __construct() {
		}

		/**
		 * Converts integer and float values to a binary representation to enable compatibility for compare and swap
		 * operations on some systems.
		 *
		 * @param int|float $value
		 *
		 * @return int
		 */
		public static function encode($value) {
			return unpack('Q', pack('d', $value))[1];
		}

		/**
		 * Converts a binary representation of integer and float values back to their true value.
		 *
		 * @param int|float $value
		 *
		 * @return int
		 */
		public static function decode($value) {
			return unpack('d', pack('Q', $value))[1];
		}
	}