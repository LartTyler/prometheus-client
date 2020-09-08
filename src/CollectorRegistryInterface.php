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
		public function get(string $name, bool $throwOnMissing = true): ?CollectorInterface;

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
		 * @throws CollectorRegistryException If a collector was found of a type of than {@see Counter}
		 */
		public function getCounter(string $name, bool $throwOnMissing = true): ?Counter;

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
		 * @throws CollectorRegistryException If a collector was found of a type of than {@see Gauge}
		 */
		public function getGauge(string $name, bool $throwOnMissing = true): ?Gauge;

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
		 * @throws CollectorRegistryException If a collector was found of a type of than {@see Histogram}
		 */
		public function getHistogram(string $name, bool $throwOnMissing = true): ?Histogram;

		/**
		 * Returns `true` if a collector exists with the given name.
		 *
		 * @param string      $name
		 * @param string|null $class
		 *
		 * @return bool
		 */
		public function has(string $name, ?string $class = null): bool;

		/**
		 * Returns an array of data samples for all registered collectors.
		 *
		 * @return MetricInterface[]
		 */
		public function collect(): array;
	}