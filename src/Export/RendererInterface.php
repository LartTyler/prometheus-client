<?php
	namespace DaybreakStudios\PrometheusClient\Export;

	interface RendererInterface {
		/**
		 * @param MetricInterface[] $metrics
		 *
		 * @return string
		 */
		public function render(array $metrics);

		/**
		 * @return string
		 */
		public function getMimeType();
	}