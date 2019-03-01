<?php
	namespace DaybreakStudios\PrometheusClient\Collector;

	use DaybreakStudios\PrometheusClient\Adapter\AdapterInterface;

	class Counter extends AbstractCollector {
		const TYPE = 'counter';

		/**
		 * Counter constructor.
		 *
		 * @param AdapterInterface $adapter
		 * @param string           $name
		 * @param string           $help
		 * @param string[]         $labelNames
		 */
		public function __construct(AdapterInterface $adapter, $name, $help, array $labelNames = []) {
			parent::__construct($adapter, $name, static::TYPE, $help, $labelNames);
		}

		/**
		 * @param array $labels
		 * @param int   $step
		 *
		 * @return $this
		 */
		public function increment(array $labels = [], $step = 1) {
			$this->assertLabelsAreValid($labels);

			$this->adapter->increment($this->getStorageKey($labels), $step);

			return $this;
		}
	}