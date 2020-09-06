<?php /** @noinspection PhpComposerExtensionStubsInspection */
	namespace DaybreakStudios\PrometheusClient\Adapter\Apcu;

	use DaybreakStudios\PrometheusClient\Adapter\AdapterInterface;
	use DaybreakStudios\PrometheusClient\Adapter\Exceptions\AdapterException;

	class ApcuAdapter implements AdapterInterface {
		/**
		 * {@inheritdoc}
		 */
		public function exists(string $key): bool {
			return apcu_exists($key);
		}

		/**
		 * {@inheritdoc}
		 */
		public function set(string $key, $value): bool {
			return apcu_store($key, $this->encode($value));
		}

		/**
		 * {@inheritdoc}
		 */
		public function get(string $key) {
			return $this->decode(apcu_fetch($key));
		}

		/**
		 * {@inheritdoc}
		 */
		public function create(string $key, $value): bool {
			return apcu_add($key, $this->encode($value));
		}

		/**
		 * {@inheritdoc}
		 */
		public function increment(string $key, $step = 1, $initialValue = 0): bool {
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
		public function decrement(string $key, $step = 1, $initialValue = 0): bool {
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
		public function delete(string $key): bool {
			return apcu_delete($key);
		}

		/**
		 * {@inheritdoc}
		 */
		public function modify(string $key, callable $mutator, $timeout = 500): bool {
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
		public function search(string $prefix): \Generator {
			foreach (new \APCUIterator('/' . str_replace('/^', '\\/', $prefix) . '/') as $key => $value)
				yield [$key, $value];
		}

		/**
		 * {@inheritdoc}
		 */
		public function clear(): bool {
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
		 * @param int|float $value
		 *
		 * @return int
		 */
		protected function decode($value) {
			return FloatSupport::decode($value);
		}
	}