<?php
	namespace DaybreakStudios\PrometheusClient\Exception;

	class HistogramTimerException extends \Exception {
		/**
		 * @return HistogramTimerException
		 */
		public static function multipleObservations() {
			return new static(
				'Observing the same timer more than once will cause duplicate results to be added to your histogram'
			);
		}
	}