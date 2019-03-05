<?php
	namespace DaybreakStudios\PrometheusClient;

	use DaybreakStudios\PrometheusClient\Collector\CollectorInterface;
	use DaybreakStudios\PrometheusClient\Collector\Counter;
	use DaybreakStudios\PrometheusClient\Collector\Gauge;
	use DaybreakStudios\PrometheusClient\Collector\Histogram;
	use DaybreakStudios\PrometheusClient\Exception\CollectorRegistryException;
	use DaybreakStudios\PrometheusClient\Export\MetricInterface;

	interface CollectorRegistryInterface {
		/**
		 * Registers a new collector.
		 *
		 * @param CollectorInterface $collector
		 *
		 * @return $this
		 * @throws CollectorRegistryException If another collector is already registered under the collector's name
		 */
		public function register(CollectorInterface $collector);

		/**
		 * Removes a collector from this registry.
		 *
		 * @param CollectorInterface|string $collector
		 *
		 * @return $this
		 */
		public function unregister($collector);

		/**
		 * Retrieves a collector from the registry. If `$throwOnMissing` is `false`, `null` will be returned if a
		 * collector is not found.
		 *
		 * @param string $name
		 * @param bool   $throwOnMissing
		 *
		 * @return CollectorInterface|null
		 * @throws CollectorRegistryException If `$throwOnMissing` is `true` and no collector was found
		 */
		public function get($name, $throwOnMissing = true);

		/**
		 * Retrieves a collector from the registry. If `$throwOnMissing` is false, `null` will be returned if a
		 * collector is not found.
		 *
		 * Additionally, if a collector is found but is not an instance of {@see Counter}, an exception will be thrown.
		 *
		 * @param string $name
		 * @param bool   $throwOnMissing
		 *
		 * @return Counter|null
		 * @throws CollectorRegistryException If `$throwOnMissing` is `true` and no collector was found
		 * @throws CollectorRegistryException If a collector was found of a type of than {@see Counter}
		 */
		public function getCounter($name, $throwOnMissing = true);

		/**
		 * Retrieves a collector from the registry. If `$throwOnMissing` is false, `null` will be returned if a
		 * collector is not found.
		 *
		 * Additionally, if a collector is found but is not an instance of {@see Gauge}, an exception will be thrown.
		 *
		 * @param string $name
		 * @param bool   $throwOnMissing
		 *
		 * @return Gauge|null
		 * @throws CollectorRegistryException If `$throwOnMissing` is `true` and no collector was found
		 * @throws CollectorRegistryException If a collector was found of a type of than {@see Gauge}
		 */
		public function getGauge($name, $throwOnMissing = true);

		/**
		 * Retrieves a collector from the registry. If `$throwOnMissing` is false, `null` will be returned if a
		 * collector is not found.
		 *
		 * Additionally, if a collector is found but is not an instance of {@see Histogram}, an exception will be
		 * thrown.
		 *
		 * @param string $name
		 * @param bool   $throwOnMissing
		 *
		 * @return Gauge|null
		 * @throws CollectorRegistryException If `$throwOnMissing` is `true` and no collector was found
		 * @throws CollectorRegistryException If a collector was found of a type of than {@see Histogram}
		 */
		public function getHistogram($name, $throwOnMissing = true);

		/**
		 * Returns `true` if a collector exists with the given name.
		 *
		 * @param string $name
		 *
		 * @return bool
		 */
		public function has($name);

		/**
		 * Returns an array of data samples for all registered collectors.
		 *
		 * @return MetricInterface[]
		 */
		public function collect();
	}