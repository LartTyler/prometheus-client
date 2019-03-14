<?php /** @noinspection PhpComposerExtensionStubsInspection */
	namespace DaybreakStudios\PrometheusClient\Adapter;

	use DaybreakStudios\PrometheusClient\Adapter\Exceptions\AdapterException;
	use DaybreakStudios\PrometheusClient\Collector\FloatSupport;

	class ApcuAdapter implements AdapterInterface {
		/**
		 * {@inheritdoc}
		 */
		public function exists($key) {
			return apcu_exists($key);
		}

		/**
		 * {@inheritdoc}
		 */
		public function set($key, $value) {
			return apcu_store($key, $this->encode($value));
		}

		/**
		 * {@inheritdoc}
		 */
		public function get($key) {
			return $this->decode(apcu_fetch($key));
		}

		/**
		 * {@inheritdoc}
		 */
		public function create($key, $value) {
			return apcu_add($key, $this->encode($value));
		}

		/**
		 * {@inheritdoc}
		 */
		public function increment($key, $step = 1, $initialValue = 0) {
			$this->create($key, $initialValue);

			return $this->modify(
				$key,
				function($old) use ($step) {
					return $old + $step;
				}
			);
		}

		/**
		 * {@inheritdoc}
		 */
		public function decrement($key, $step = 1, $initialValue = 0) {
			$this->create($key, $initialValue);

			return $this->modify(
				$key,
				function($old) use ($step) {
					return $old - $step;
				}
			);
		}

		/**
		 * {@inheritdoc}
		 */
		public function delete($key) {
			return apcu_delete($key);
		}

		/**
		 * {@inheritdoc}
		 */
		public function modify($key, callable $mutator, $timeout = 500) {
			$startTime = microtime(true);
			$done = false;

			while (!$done) {
				$old = $this->get($key);

				$done = apcu_cas($key, $this->encode($old), call_user_func($mutator, $old));

				if ((microtime(true) - $startTime) * 1000 >= $timeout)
					throw AdapterException::compareAndSwapTimeout($timeout);
			}

			return true;
		}

		/**
		 * {@inheritdoc}
		 */
		public function search($prefix) {
			return new ApcuIteratorWrapper(new \APCUIterator('/' . str_replace('/^', '\\/', $prefix) . '/'));
		}

		/**
		 * {@inheritdoc}
		 */
		public function clear() {
			return apcu_clear_cache();
		}

		/**
		 * Encodes a value so that it can be safely used by an APCu function.
		 *
		 * @param int|float $value
		 *
		 * @return int
		 */
		protected function encode($value) {
			return FloatSupport::encode($value);
		}

		/**
		 * Decodes a stored value for general use.
		 *
		 * @param int $value
		 *
		 * @return int|float
		 */
		protected function decode($value) {
			return FloatSupport::decode($value);
		}
	}