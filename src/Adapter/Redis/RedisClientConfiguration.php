<?php
	namespace DaybreakStudios\PrometheusClient\Adapter\Redis;

	class RedisClientConfiguration {
		public const DEFAULT_KEY_PREFIX = 'dbstudios_prom:';

		/**
		 * @var string
		 */
		protected $host;

		/**
		 * @var string
		 */
		protected $keyPrefix;

		/**
		 * @var int
		 */
		protected $port = 6379;

		/**
		 * @var float
		 */
		protected $timeout = 0.0;

		/**
		 * @var int
		 */
		protected $retryInterval = 0;

		/**
		 * @var float
		 */
		protected $retryTimeout = 0.0;

		/**
		 * @var string|null
		 */
		protected $password = null;

		/**
		 * RedisConfiguration constructor.
		 *
		 * @param string $host
		 * @param string $keyPrefix
		 */
		public function __construct(string $host, string $keyPrefix = self::DEFAULT_KEY_PREFIX) {
			$this->host = $host;
			$this->keyPrefix = $keyPrefix;
		}

		/**
		 * @return string
		 */
		public function getHost(): string {
			return $this->host;
		}

		/**
		 * @param string $host
		 *
		 * @return $this
		 */
		public function setHost(string $host) {
			$this->host = $host;

			return $this;
		}

		/**
		 * @return int
		 */
		public function getPort(): int {
			return $this->port;
		}

		/**
		 * @param int $port
		 *
		 * @return $this
		 */
		public function setPort(int $port) {
			$this->port = $port;

			return $this;
		}

		/**
		 * @return float
		 */
		public function getTimeout(): float {
			return $this->timeout;
		}

		/**
		 * @param float $timeout
		 *
		 * @return $this
		 */
		public function setTimeout(float $timeout) {
			$this->timeout = $timeout;

			return $this;
		}

		/**
		 * @return int
		 */
		public function getRetryInterval(): int {
			return $this->retryInterval;
		}

		/**
		 * @param int $retryInterval
		 *
		 * @return $this
		 */
		public function setRetryInterval(int $retryInterval) {
			$this->retryInterval = $retryInterval;

			return $this;
		}

		/**
		 * @return float
		 */
		public function getRetryTimeout(): float {
			return $this->retryTimeout;
		}

		/**
		 * @param float $retryTimeout
		 *
		 * @return $this
		 */
		public function setRetryTimeout(float $retryTimeout) {
			$this->retryTimeout = $retryTimeout;

			return $this;
		}

		/**
		 * @return string
		 */
		public function getKeyPrefix(): string {
			return $this->keyPrefix;
		}

		/**
		 * @return string|null
		 */
		public function getPassword(): ?string {
			return $this->password;
		}

		/**
		 * @param string|null $password
		 *
		 * @return $this
		 */
		public function setPassword(?string $password) {
			$this->password = $password;

			return $this;
		}
	}