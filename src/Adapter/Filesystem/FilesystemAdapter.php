<?php
	namespace DaybreakStudios\PrometheusClient\Adapter\Filesystem;

	use DaybreakStudios\PrometheusClient\Adapter\AdapterInterface;

	class FilesystemAdapter implements AdapterInterface {
		/**
		 * @var string
		 */
		protected $basePath;

		/**
		 * @var string
		 */
		protected $lockSuffix;

		/**
		 * @var string[]
		 */
		protected $keyCache = [];

		/**
		 * FilesystemAdapter constructor.
		 *
		 * @param string $basePath
		 * @param string $lockSuffix
		 *
		 * @throws \RuntimeException if $basePath is not readable or writable by PHP
		 */
		public function __construct(string $basePath, string $lockSuffix = '.lock') {
			$this->basePath = rtrim($basePath, '\\/');
			$this->lockSuffix = $lockSuffix;

			if (!is_readable($this->basePath) || !is_writable($this->basePath))
				throw new \RuntimeException($this->basePath . ' must be readable and writable by PHP');
		}

		/**
		 * {@inheritdoc}
		 */
		public function exists(string $key): bool {
			return file_exists($this->getPath($key));
		}

		/**
		 * {@inheritdoc}
		 */
		public function set(string $key, $value): bool {
			return file_put_contents($this->getPath($key), $this->serialize($value)) !== false;
		}

		/**
		 * {@inheritdoc}
		 */
		public function get(string $key, $def = null) {
			if (!$this->exists($key))
				return $def;

			return $this->unserialize(file_get_contents($this->getPath($key)));
		}

		/**
		 * {@inheritdoc}
		 */
		public function create(string $key, $value): bool {
			if ($this->exists($key))
				return false;

			$this->set($key, $value);

			return true;
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
			if (!$this->exists($key))
				return false;

			unlink($this->getPath($key));

			if (file_exists($lockPath = $this->getPath($key) . $this->lockSuffix))
				unlink($lockPath);

			return true;
		}

		/**
		 * {@inheritdoc}
		 */
		public function modify(string $key, callable $mutator, int $timeout = 500): bool {
			if (!$this->exists($key))
				return false;

			$path = $this->getPath($key);
			$lock = new FilesystemLock($path, $this->lockSuffix);

			if (!$lock->await($timeout))
				return false;

			$success = $this->set($key, call_user_func($mutator, $this->get($key)));

			$lock->release();

			return $success;
		}

		/**
		 * {@inheritdoc}
		 */
		public function search(string $prefix): \Generator {
			foreach (scandir($this->basePath) as $item) {
				// Ignore any dotfiles in the directory (to support things like .gitkeep / .gitignore)
				if (strpos($item, '.') === 0)
					continue;
				else if (strrpos($item, $this->lockSuffix) === strlen($item) - strlen($this->lockSuffix))
					continue;

				$key = $this->decodeFilename($item);

				if (strpos($key, $prefix) === 0)
					yield [$key, $this->get($key)];
			}
		}

		/**
		 * {@inheritdoc}
		 */
		public function clear(): bool {
			foreach (scandir($this->basePath) as $item) {
				$path = $this->basePath . DIRECTORY_SEPARATOR . $item;

				if (is_dir($path) || strpos($item, '.') === 0)
					continue;

				unlink($path);
			}

			return true;
		}

		/**
		 * Converts a key into an absolute file path.
		 *
		 * @param string $key The un-encoded key for the item
		 *
		 * @return string
		 */
		protected function getPath(string $key): string {
			return $this->basePath . DIRECTORY_SEPARATOR . $this->encodeFilename($key);
		}

		/**
		 * Encodes a key so that it can be safely used as a file name.
		 *
		 * @param string $key
		 *
		 * @return string
		 */
		protected function encodeFilename(string $key): string {
			if (!isset($this->keyCache[$key]))
				$this->keyCache[$key] = strtr(base64_encode($key), '=+/', '-_.');

			return $this->keyCache[$key];
		}

		/**
		 * Decodes a key encoded as a file name.
		 *
		 * @param string $encodedFilename
		 *
		 * @return string
		 */
		protected function decodeFilename(string $encodedFilename): string {
			return base64_decode(strtr($encodedFilename, '-_.', '=+/'));
		}

		/**
		 * Serializes a value for storage.
		 *
		 * @param mixed $value
		 *
		 * @return string
		 */
		protected function serialize($value): string {
			return serialize($value);
		}

		/**
		 * De-serializes a stored value.
		 *
		 * @param string $data
		 *
		 * @return mixed
		 */
		protected function unserialize(string $data) {
			return unserialize($data);
		}
	}