<?php
	namespace DaybreakStudios\PrometheusClient\Export;

	interface MetricInterface {
		/**
		 * Gets the name of the samples group.
		 *
		 * @return string
		 */
		public function getName(): string;

		/**
		 * Gets the type name of the samples in the group.
		 *
		 * @return string
		 */
		public function getType(): string;

		/**
		 * Gets the help text for the samples in the group.
		 *
		 * @return string
		 */
		public function getHelp(): string;

		/**
		 * @return SampleInterface[]
		 */
		public function getSamples(): array;
	}