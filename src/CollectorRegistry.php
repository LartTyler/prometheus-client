<?php
	namespace DaybreakStudios\PrometheusClient;

	use DaybreakStudios\PrometheusClient\Collector\CollectorInterface;
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
	}