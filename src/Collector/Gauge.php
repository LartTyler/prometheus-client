<?php
	namespace DaybreakStudios\PrometheusClient\Collector;

	use DaybreakStudios\PrometheusClient\Adapter\AdapterInterface;

	class Gauge extends AbstractCollector {
		const TYPE = 'gauge';

		/**
		 * Gauge constructor.
		 *
		 * @param AdapterInterface $adapter
		 * @param string           $name
		 * @param string           $help
		 * @param array            $labelNames
		 */
		public function __construct(AdapterInterface $adapter, $name, $help, array $labelNames = []) {
			parent::__construct($adapter, $name, static::TYPE, $help, $labelNames);
		}

		/**
		 * @param int|float $value
		 * @param array     $labels
		 *
		 * @return $this
		 */
		public function set($value, array $labels = []) {
			$this->adapter->set($this->getStorageKey($labels), FloatSupport::encode($value));

			return $this;
		}

		/**
		 * @param array     $labels
		 * @param int|float $step
		 *
		 * @return $this
		 */
		public function increment(array $labels = [], $step = 1) {
			$storageKey = $this->getStorageKey($labels);

			$this->adapter->create($storageKey, 0);
			$this->adapter->modify(
				$storageKey,
				function($old) use ($step) {
					return FloatSupport::encode(FloatSupport::decode($old) + $step);
				}
			);

			return $this;
		}

		/**
		 * @param array $labels
		 * @param int   $step
		 *
		 * @return $this
		 */
		public function decrement(array $labels = [], $step = 1) {
			$storageKey = $this->getStorageKey($labels);

			$this->adapter->create($storageKey, 0);
			$this->adapter->modify(
				$storageKey,
				function($old) use ($step) {
					return FloatSupport::encode(FloatSupport::decode($old) - $step);
				}
			);

			return $this;
		}

		/**
		 * {@inheritdoc}
		 */
		protected function decodeValue($value) {
			return FloatSupport::decode($value);
		}
	}