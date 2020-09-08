<?php
	namespace DaybreakStudios\PrometheusClient\Export;

	interface RendererInterface {
		/**
		 * Reduces the provided metrics down to a string suitable to export to a Prometheus scraper.
		 *
		 * @param MetricInterface[] $metrics
		 *
		 * @return string
		 */
		public function render(array $metrics): string;

		/**
		 * Returns a MIME type appropriate for the renderer.
		 *
		 * @return string
		 */
		public function getMimeType(): string;
	}