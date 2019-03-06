<?php
	namespace DaybreakStudios\PrometheusClient\Adapter;

	use DaybreakStudios\PrometheusClient\Adapter\Filesystem\FilesystemIterator;
	use DaybreakStudios\PrometheusClient\Adapter\Filesystem\FilesystemLock;

	class FilesystemAdapter implements AdapterInterface {
		/**
		 * @var string
		 */
		protected $basePath;

		/**
		 * @var string[]
		 */
		protected $keyCache = [];

		/**
		 * FilesystemAdapter constructor.
		 *
		 * @param string $basePath
		 * @throws \RuntimeException if $basePath is not readable or writable by PHP
		 */
		public function __construct($basePath) {
			$this->basePath = rtrim($basePath, '\\/');

			if (!is_readable($this->basePath) || !is_writable($this->basePath))
				throw new \RuntimeException($this->basePath . ' must be readable and writable by PHP');
		}

		/**
		 * {@inheritdoc}
		 */
		public function exists($key) {
			return file_exists($this->getPath($key));
		}

		/**
		 * {@inheritdoc}
		 */
		public function set($key, $value) {
			return file_put_contents($this->getPath($key), $this->serialize($value)) !== false;
		}

		/**
		 * {@inheritdoc}
		 */
		public function get($key, $def = null) {
			if (!$this->exists($key))
				return $def;

			return $this->unserialize(file_get_contents($this->getPath($key)));
		}

		/**
		 * {@inheritdoc}
		 */
		public function create($key, $value) {
			if ($this->exists($key))
				return false;

			$this->set($key, $value);

			return true;
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
			if (!$this->exists($key))
				return false;

			unlink($this->getPath($key));

			return true;
		}

		/**
		 * {@inheritdoc}
		 */
		public function modify($key, callable $mutator, $timeout = 500) {
			if (!$this->exists($key))
				return false;

			$path = $this->getPath($key);
			$lock = new FilesystemLock($path);

			if (!$lock->await($timeout))
				return false;

			$success = $this->set($key, call_user_func($mutator, $this->get($key)));

			$lock->release();

			return $success;
		}

		/**
		 * {@inheritdoc}
		 */
		public function search($prefix) {
			$keys = [];

			foreach (scandir($this->basePath) as $item) {
				// Ignore any dotfiles in the directory (to support things like .gitkeep / .gitignore)
				if (strpos($item, '.') === 0)
					continue;

				$key = $this->decodeFilename($item);

				if (strpos($key, $prefix) === 0)
					$keys[] = $key;
			}

			return new FilesystemIterator($this, $keys);
		}

		/**
		 * {@inheritdoc}
		 */
		public function clear() {
			foreach (scandir($this->basePath) as $item) {
				$path = $this->basePath . DIRECTORY_SEPARATOR . $item;

				if (is_dir($path))
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
		protected function getPath($key) {
			return $this->basePath . DIRECTORY_SEPARATOR . $this->encodeFilename($key);
		}

		/**
		 * Encodes a key so that it can be safely used as a file name.
		 *
		 * @param string $key
		 *
		 * @return string
		 */
		protected function encodeFilename($key) {
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
		protected function decodeFilename($encodedFilename) {
			return base64_decode(strtr($encodedFilename, '-_.', '=+/'));
		}

		/**
		 * Serializes a value for storage.
		 *
		 * @param mixed $value
		 *
		 * @return string
		 */
		protected function serialize($value) {
			return serialize($value);
		}

		/**
		 * De-serializes a stored value.
		 *
		 * @param string $data
		 *
		 * @return mixed
		 */
		protected function unserialize($data) {
			return unserialize($data);
		}
	}