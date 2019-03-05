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

		/**
		 * @param string $name
		 * @param string $expectedClass
		 * @param string $actualClass
		 *
		 * @return CollectorRegistryException
		 */
		public static function collectorClassMismatch($name, $expectedClass, $actualClass) {
			return new static(sprintf('Expected %s to be a %s, but got a %s', $name, $expectedClass, $actualClass));
		}
	}