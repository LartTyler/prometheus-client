<?php
	namespace DaybreakStudios\PrometheusClient\Export\Render;

	use DaybreakStudios\PrometheusClient\Export\MetricInterface;
	use DaybreakStudios\PrometheusClient\Export\RendererInterface;

	class TextRenderer implements RendererInterface {
		const MIME_TYPE = 'text/plain; version=0.0.4';

		/**
		 * @param MetricInterface[] $metrics
		 *
		 * @return string
		 */
		public function render(array $metrics) {
			usort(
				$metrics,
				function(MetricInterface $a, MetricInterface $b) {
					return strcmp($a->getName(), $b->getName());
				}
			);

			$lines = [];

			foreach ($metrics as $metric) {
				if (!$metric->getSamples())
					continue;

				$lines[] = sprintf('# HELP %s %s', $metric->getName(), $this->sanitize($metric->getHelp()));
				$lines[] = sprintf('# TYPE %s %s', $metric->getName(), $metric->getType());

				foreach ($metric->getSamples() as $sample) {
					$line = $sample->getName() ?: $metric->getName();

					if ($labels = $sample->getLabels()) {
						$line .= '{';

						$keys = array_keys($labels);

						foreach ($keys as $i => $key) {
							if ($i !== 0)
								$line .= ',';

							$value = str_replace('"', '\\"', $this->sanitize($labels[$key]));

							$line .= sprintf('%s="%s"', $key, $value);
						}

						$line .= '}';
					}

					$lines[] = $line . ' ' . $sample->getValue();
				}
			}

			return implode("\n", $lines) . "\n";
		}

		/**
		 * @return string
		 */
		public function getMimeType() {
			return static::MIME_TYPE;
		}

		/**
		 * @param string $value
		 *
		 * @return string
		 */
		protected function sanitize($value) {
			$value = str_replace('\\', '\\\\', $value);
			$value = str_replace("\n", '\\n', $value);

			return $value;
		}
	}