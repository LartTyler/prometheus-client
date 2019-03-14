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
			$this->assertLabelsAreValid($labels);

			$this->adapter->set($this->getStorageKey($labels), $value);

			return $this;
		}

		/**
		 * @param array     $labels
		 * @param int|float $step
		 *
		 * @return $this
		 */
		public function increment(array $labels = [], $step = 1) {
			$this->assertLabelsAreValid($labels);

			$this->adapter->increment($this->getStorageKey($labels), $step);

			return $this;
		}

		/**
		 * @param array $labels
		 * @param int   $step
		 *
		 * @return $this
		 */
		public function decrement(array $labels = [], $step = 1) {
			$this->assertLabelsAreValid($labels);

			$this->adapter->decrement($this->getStorageKey($labels), $step);

			return $this;
		}
	}