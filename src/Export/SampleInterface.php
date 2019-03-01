<?php
	namespace DaybreakStudios\PrometheusClient\Export;

	interface SampleInterface {
		/**
		 * Gets the sample's name. If `null`, the parent {@see MetricInterface}'s name should be used.
		 *
		 * @return string|null
		 */
		public function getName();

		/**
		 * Gets the sample's labels.
		 *
		 * @return array
		 */
		public function getLabels();

		/**
		 * Gets the value of the sample.
		 *
		 * @return int|float
		 */
		public function getValue();
	}