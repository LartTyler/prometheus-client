<?php
	namespace DaybreakStudios\PrometheusClient\Collector;

	use DaybreakStudios\PrometheusClient\Adapter\AdapterInterface;
	use DaybreakStudios\PrometheusClient\Export\Metric;
	use DaybreakStudios\PrometheusClient\Export\Sample;

	abstract class AbstractCollector implements CollectorInterface {
		/**
		 * @var AdapterInterface
		 */
		protected $adapter;

		/**
		 * @var string
		 */
		protected $name;

		/**
		 * @var string
		 */
		protected $type;

		/**
		 * @var string
		 */
		protected $help;

		/**
		 * @var array|string[]
		 */
		protected $labelNames;

		/**
		 * @var string
		 */
		protected $storageKeyPrefix = 'prom';

		/**
		 * AbstractCollector constructor.
		 *
		 * @param AdapterInterface $adapter
		 * @param string           $name
		 * @param string           $type
		 * @param string           $help
		 * @param string[]         $labelNames
		 */
		public function __construct(AdapterInterface $adapter, $name, $type, $help, array $labelNames = []) {
			$this->adapter = $adapter;
			$this->name = $name;
			$this->type = $type;
			$this->help = $help;
			$this->labelNames = $labelNames;
		}

		/**
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}

		/**
		 * @return string
		 */
		public function getType() {
			return $this->type;
		}

		/**
		 * @return string
		 */
		public function getHelp() {
			return $this->help;
		}

		/**
		 * @return array|string[]
		 */
		public function getLabelNames() {
			return $this->labelNames;
		}

		/**
		 * @return string
		 */
		public function getStorageKeyPrefix() {
			return $this->storageKeyPrefix;
		}

		/**
		 * @param string $storageKeyPrefix
		 *
		 * @return $this
		 */
		public function setStorageKeyPrefix($storageKeyPrefix) {
			$this->storageKeyPrefix = $storageKeyPrefix;

			return $this;
		}

		/**
		 * {@inheritdoc}
		 */
		public function collect() {
			$prefix = $this->getStorageSearchPrefix();
			$samples = [];

			foreach ($this->adapter->search($prefix) as $key => $value) {
				$labels = $this->decodeLabels(substr($key, strrpos($key, ':') + 1));

				$samples[] = new Sample($value, $labels);
			}

			usort(
				$samples,
				function(Sample $a, Sample $b) {
					return strcmp(
						implode('', array_values($a->getLabels())),
						implode('', array_values($b->getLabels()))
					);
				}
			);

			return [
				new Metric($this->getName(), $this->getType(), $this->getHelp(), $samples),
			];
		}

		/**
		 * @param array $labels
		 *
		 * @return string
		 */
		protected function getStorageKey(array $labels) {
			return implode(
				':',
				array_merge(
					$this->getStorageKeyParts(),
					[
						$this->encodeLabels($labels),
					]
				)
			);
		}

		/**
		 * @return string
		 */
		protected function getStorageSearchPrefix() {
			return implode(':', $this->getStorageKeyParts()) . ':';
		}

		/**
		 * @return array
		 */
		protected function getStorageKeyParts() {
			return [
				$this->getStorageKeyPrefix(),
				$this->getName(),
			];
		}

		/**
		 * @param array $labels
		 *
		 * @return string
		 */
		protected function encodeLabels(array $labels) {
			ksort($labels);

			return base64_encode(json_encode($labels));
		}

		/**
		 * @param string $encodedLabels
		 *
		 * @return array
		 */
		protected function decodeLabels($encodedLabels) {
			return json_decode(base64_decode($encodedLabels), true);
		}

		/**
		 * @param array $labels
		 *
		 * @return void
		 */
		protected function assertLabelsAreValid(array $labels) {
			if (sizeof($labels) !== sizeof($this->getLabelNames())) {
				throw new \InvalidArgumentException(
					'Label count does not match the number of names defined for this collector'
				);
			}

			foreach ($labels as $key => $value) {
				if (!in_array($key, $this->getLabelNames()))
					throw new \InvalidArgumentException('This collector does not support the ' . $key . ' label');
			}
		}
	}