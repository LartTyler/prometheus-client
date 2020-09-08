<?php
	namespace DaybreakStudios\PrometheusClient\Adapter;

	/**
	 * Stores metrics in-memory. Probably has limited uses outside of testing and debugging, as values will be discarded
	 * at the end of every session, and cannot be shared between sessions.
	 *
	 * @package DaybreakStudios\PrometheusClient\Adapter
	 */
	class MemoryAdapter implements AdapterInterface {
		protected $data = [];

		/**
		 * {@inheritdoc}
		 */
		public function exists(string $key): bool {
			return isset($this->data[$key]);
		}

		/**
		 * {@inheritdoc}
		 */
		public function set(string $key, $value): bool {
			$this->data[$key] = $value;

			return true;
		}

		/**
		 * {@inheritdoc}
		 */
		public function get(string $key) {
			return $this->data[$key] ?? 0;
		}

		/**
		 * {@inheritdoc}
		 */
		public function create(string $key, $value): bool {
			if (!$this->exists($key)) {
				$this->data[$key] = $value;

				return true;
			}

			return false;
		}

		/**
		 * {@inheritdoc}
		 */
		public function increment(string $key, $step = 1, $initialValue = 0): bool {
			$this->create($key, $initialValue);
			$this->set($key, $this->get($key) + $step);

			return true;
		}

		/**
		 * {@inheritdoc}
		 */
		public function decrement(string $key, $step = 1, $initialValue = 0): bool {
			$this->create($key, $initialValue);
			$this->set($key, $this->get($key) - $step);

			return true;
		}

		/**
		 * {@inheritdoc}
		 */
		public function delete(string $key): bool {
			$exists = $this->exists($key);

			unset($this->data[$key]);

			return $exists;
		}

		/**
		 * {@inheritdoc}
		 */
		public function modify(string $key, callable $mutator, int $timeout = 500): bool {
			$this->set($key, call_user_func($mutator, $this->get($key)));

			return true;
		}

		/**
		 * {@inheritdoc}
		 */
		public function search(string $prefix): \Generator {
			foreach ($this->data as $key => $value) {
				if (strpos($key, $prefix) === 0)
					yield [$key, $value];
			}
		}

		/**
		 * {@inheritdoc}
		 */
		public function clear(): bool {
			$this->data = [];

			return true;
		}
	}