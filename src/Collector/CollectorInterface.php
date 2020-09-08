<?php
	namespace DaybreakStudios\PrometheusClient\Collector;

	use DaybreakStudios\PrometheusClient\Export\MetricInterface;

	interface CollectorInterface {
		/**
		 * Gets the collector's name.
		 *
		 * @return string
		 */
		public function getName(): string;

		/**
		 * Gets collector's type name.
		 *
		 * @return string
		 */
		public function getType(): string;

		/**
		 * Gets the collector's help text.
		 *
		 * @return string
		 */
		public function getHelp(): string;

		/**
		 * Gets the names of the labels used by the collector.
		 *
		 * @return string[]
		 */
		public function getLabelNames(): array;

		/**
		 * Collects all the metrics held by the collector.
		 *
		 * @return MetricInterface[]
		 */
		public function collect(): array;
	}