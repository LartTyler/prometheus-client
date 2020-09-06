<?php
	namespace DaybreakStudios\PrometheusClient\Adapter\Redis;

	use DaybreakStudios\PrometheusClient\Adapter\AdapterInterface;
	use DaybreakStudios\PrometheusClient\Adapter\Exceptions\AdapterException;

	class RedisAdapter implements AdapterInterface {
		/**
		 * @var RedisClientConfiguration
		 */
		protected $config;

		/**
		 * @var \Redis|null
		 */
		protected $redis = null;

		/**
		 * RedisAdapter constructor.
		 *
		 * @param RedisClientConfiguration $config
		 */
		public function __construct(RedisClientConfiguration $config) {
			$this->config = $config;
		}

		/**
		 * {@inheritdoc}
		 */
		public function exists(string $key): bool {
			return $this->getClient()->exists($this->toKey($key));
		}

		/**
		 * {@inheritdoc}
		 */
		public function set(string $key, $value): bool {
			return $this->getClient()->set($this->toKey($key), $value);
		}

		/**
		 * {@inheritdoc}
		 */
		public function get(string $key) {
			return $this->getClient()->get($this->toKey($key));
		}

		/**
		 * {@inheritdoc}
		 */
		public function create(string $key, $value): bool {
			return $this->getClient()->setnx($this->toKey($key), $value);
		}

		/**
		 * {@inheritdoc}
		 */
		public function increment(string $key, $step = 1, $initialValue = 0): bool {
			$this->create($key, $initialValue);

			return $this->getClient()->incrByFloat($this->toKey($key), $step);
		}

		/**
		 * {@inheritdoc}
		 */
		public function decrement(string $key, $step = 1, $initialValue = 0): bool {
			$this->create($key, $initialValue);

			// Redis doesn't seem to implement a decrByFloat method, for some strange reason. So I guess we need to
			// increment by the negated value of step instead?
			return $this->getClient()->incrByFloat($this->toKey($key), -$step);
		}

		/**
		 * {@inheritdoc}
		 */
		public function delete(string $key): bool {
			return $this->getClient()->del($this->toKey($key)) >= 1;
		}

		/**
		 * {@inheritdoc}
		 */
		public function modify(string $key, callable $mutator, $timeout = 500): bool {
			$client = $this->getClient();
			$startTime = microtime(true);

			$prefixedKey = $this->toKey($key);

			while (true) {
				$client->watch($prefixedKey);

				$value = call_user_func($mutator, $this->get($key));

				$client->multi();
				$client->set($prefixedKey, $value);

				if ($client->exec()[0] ?? false)
					break;
				else if ((microtime(true) - $startTime) * 1000 >= $timeout)
					throw AdapterException::compareAndSwapTimeout($timeout);
			}

			return true;
		}

		/**
		 * {@inheritdoc}
		 */
		public function search(string $prefix): \Generator {
			$cursor = null;

			while (($keys = $this->getClient()->scan($cursor, $this->toKey($prefix) . '*')) !== false) {
				foreach ($keys as $key) {
					$key = $this->fromKey($key);

					yield [
						$key,
						$this->get($key)
					];
				}
			}
		}

		/**
		 * {@inheritdoc}
		 */
		public function clear(): bool {
			foreach ($this->search('') as $item)
				$this->delete($item[0]);

			return true;
		}

		/**
		 * @return \Redis
		 */
		protected function getClient(): \Redis {
			if ($this->redis === null) {
				$this->redis = new \Redis();
				$this->redis->connect(
					$this->config->getHost(),
					$this->config->getPort(),
					$this->config->getTimeout(),
					null,
					$this->config->getRetryInterval(),
					$this->config->getRetryTimeout()
				);

				if ($this->config->getPassword() !== null)
					$this->redis->auth($this->config->getPassword());
			}

			return $this->redis;
		}

		/**
		 * Creates a prefixed key from an input string.
		 *
		 * @param string $input
		 *
		 * @return string
		 */
		protected function toKey(string $input): string {
			return $this->config->getKeyPrefix() . $input;
		}

		/**
		 * Strips the configured prefix from an input key.
		 *
		 * @param string $key
		 *
		 * @return string
		 */
		protected function fromKey(string $key): string {
			return substr($key, strlen($this->config->getKeyPrefix()));
		}
	}