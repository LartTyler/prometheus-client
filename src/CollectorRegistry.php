<?php
	namespace DaybreakStudios\PrometheusClient;

	use DaybreakStudios\PrometheusClient\Collector\CollectorInterface;
	use DaybreakStudios\PrometheusClient\Collector\Counter;
	use DaybreakStudios\PrometheusClient\Collector\Gauge;
	use DaybreakStudios\PrometheusClient\Collector\Histogram;
	use DaybreakStudios\PrometheusClient\Exception\CollectorRegistryException;

	class CollectorRegistry implements CollectorRegistryInterface {
		/**
		 * @var CollectorInterface[]
		 */
		protected $collectors = [];

		/**
		 * {@inheritdoc}
		 */
		public function register(CollectorInterface $collector) {
			if ($this->has($collector->getName()))
				throw CollectorRegistryException::collectorAlreadyRegistered($collector->getName());

			$this->collectors[$collector->getName()] = $collector;

			return $this;
		}

		/**
		 * {@inheritdoc}
		 */
		public function unregister($collector) {
			if ($collector instanceof CollectorInterface)
				$collector = $collector->getName();
			else if (!is_string($collector)) {
				throw new \InvalidArgumentException(
					'$collector must be a string or an instance of ' . CollectorInterface::class
				);
			}

			unset($this->collectors[$collector]);

			return $this;
		}

		/**
		 * {@inheritdoc}
		 */
		public function get($name, $throwOnMissing = true) {
			if (!$this->has($name)) {
				if ($throwOnMissing)
					throw CollectorRegistryException::collectorNotFound($name);

				return null;
			}

			return $this->collectors[$name];
		}

		/**
		 * {@inheritdoc}
		 */
		public function getCounter($name, $throwOnMissing = true) {
			return $this->getOfType($name, Counter::class, $throwOnMissing);
		}

		/**
		 * {@inheritdoc}
		 */
		public function getGauge($name, $throwOnMissing = true) {
			return $this->getOfType($name, Gauge::class, $throwOnMissing);
		}

		/**
		 * {@inheritdoc}
		 */
		public function getHistogram($name, $throwOnMissing = true) {
			return $this->getOfType($name, Histogram::class, $throwOnMissing);
		}

		/**
		 * {@inheritdoc}
		 */
		public function has($name) {
			return isset($this->collectors[$name]);
		}

		/**
		 * {@inheritdoc}
		 */
		public function collect() {
			$metrics = [];

			foreach ($this->collectors as $collector)
				$metrics = array_merge($metrics, $collector->collect());

			return $metrics;
		}

		/**
		 * Retrieves a collector from the registry, and enforces that the collector is a child of a given class.
		 *
		 * @param string $name
		 * @param string $class
		 * @param bool   $throwOnMissing
		 *
		 * @return CollectorInterface|null
		 * @throws CollectorRegistryException If `$throwOnMissing` is `true` and no collector was found
		 * @throws CollectorRegistryException If a collector was found of a type of than the type specified by `$class`
		 */
		protected function getOfType($name, $class, $throwOnMissing = true) {
			$collector = $this->get($name, $throwOnMissing);

			if ($collector && !is_a($collector, $class))
				throw CollectorRegistryException::collectorClassMismatch($name, $class, get_class($collector));

			return $collector;
		}
	}