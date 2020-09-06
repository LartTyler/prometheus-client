<?php
	namespace DaybreakStudios\PrometheusClient\Collector;

	use DaybreakStudios\PrometheusClient\Adapter\AdapterInterface;
	use DaybreakStudios\PrometheusClient\Export\Metric;
	use DaybreakStudios\PrometheusClient\Export\MetricInterface;
	use DaybreakStudios\PrometheusClient\Export\Sample;

	class Histogram extends AbstractCollector {
		const TYPE = 'histogram';

		/**
		 * @var array
		 */
		protected $buckets;

		/**
		 * Histogram constructor.
		 *
		 * @param AdapterInterface $adapter
		 * @param string           $name
		 * @param string           $help
		 * @param array            $buckets
		 * @param array            $labelNames
		 */
		public function __construct(
			AdapterInterface $adapter,
			string $name,
			string $help,
			array $buckets,
			array $labelNames = []
		) {
			if (in_array('le', $labelNames))
				throw new \InvalidArgumentException('Histograms cannot have a label named "le"');

			parent::__construct($adapter, $name, static::TYPE, $help, $labelNames);

			$this->buckets = $buckets;
		}

		/**
		 * @param int|float $value
		 * @param array     $labels
		 *
		 * @return $this
		 */
		public function observe($value, array $labels = []) {
			$this->assertLabelsAreValid($labels);

			$storageKey = $this->getStorageKey($labels);
			$sumKey = $storageKey . ':sum';

			$this->adapter->create($sumKey, 0);
			$this->adapter->modify(
				$sumKey,
				function($old) use ($value) {
					return $old + $value;
				}
			);

			$targetBucket = '+Inf';

			foreach ($this->buckets as $bucket) {
				if ($value <= $bucket) {
					$targetBucket = $bucket;

					break;
				}
			}

			$this->adapter->increment($storageKey . ':' . $targetBucket);

			return $this;
		}

		/**
		 * @return MetricInterface[]
		 */
		public function collect(): array {
			$prefix = $this->getStorageSearchPrefix();
			$bucketValues = [];

			foreach ($this->adapter->search($prefix) as [$key, $value]) {
				$parts = explode(':', $key);
				$count = sizeof($parts);

				$suffix = $parts[$count - 1];
				$labels = $parts[$count - 2];

				$bucketValues[$labels][$suffix] = $value;
			}

			$bucketKeys = array_keys($bucketValues);
			sort($bucketKeys);

			$buckets = $this->buckets;
			$buckets[] = '+Inf';

			$samples = [];

			foreach ($bucketKeys as $key) {
				$count = 0;
				$labels = $this->decodeLabels($key);

				foreach ($buckets as $bucket) {
					$index = (string)$bucket;

					if (isset($bucketValues[$key][$index]))
						$count += $bucketValues[$key][$index];

					$samples[] = new Sample(
						$count,
						$labels + [
							'le' => $bucket,
						],
						$this->getName() . '_bucket'
					);
				}

				$samples[] = new Sample($count, $labels, $this->getName() . '_count');
				$samples[] = new Sample($bucketValues[$key]['sum'], $labels, $this->getName() . '_sum');
			}

			return [
				new Metric($this->getName(), $this->getType(), $this->getHelp(), $samples),
			];
		}

		/**
		 * @param array $labels
		 *
		 * @return void
		 */
		protected function assertLabelsAreValid(array $labels): void {
			if (isset($labels['le']))
				throw new \InvalidArgumentException('Histograms cannot have a label named "le"');

			parent::assertLabelsAreValid($labels);
		}
	}