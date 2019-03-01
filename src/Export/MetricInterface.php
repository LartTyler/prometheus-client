<?php
	namespace DaybreakStudios\PrometheusClient\Export;

	interface MetricInterface {
		/**
		 * Gets the name of the samples group.
		 *
		 * @return string
		 */
		public function getName();

		/**
		 * Gets the type name of the samples in the group.
		 *
		 * @return string
		 */
		public function getType();

		/**
		 * Gets the help text for the samples in the group.
		 *
		 * @return string
		 */
		public function getHelp();

		/**
		 * @return SampleInterface[]
		 */
		public function getSamples();
	}