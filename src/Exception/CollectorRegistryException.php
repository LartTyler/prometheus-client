<?php
	namespace DaybreakStudios\PrometheusClient\Exception;

	class CollectorRegistryException extends \Exception {
		/**
		 * @param string $name
		 *
		 * @return static
		 */
		public static function collectorAlreadyRegistered($name) {
			return new static('A collector named ' . $name . ' has already been registered to this registry');
		}

		/**
		 * @param string $name
		 *
		 * @return static
		 */
		public static function collectorNotFound($name) {
			return new static('This registry has no collectors named ' . $name);
		}
	}