<?php
	namespace DaybreakStudios\PrometheusClient\Adapter\Filesystem;

	class FilesystemLock {
		/**
		 * @var string
		 */
		protected $path;

		/**
		 * @var bool
		 */
		protected $owned = false;

		/**
		 * @var int
		 */
		protected $maxAge;

		/**
		 * FilesystemLock constructor.
		 *
		 * @param string $path   The path to the file this lock belongs to
		 * @param int    $maxAge The maximum age of the lock, in seconds (locks older than `$maxAge` will be ignored)
		 */
		public function __construct($path, $maxAge = 5) {
			$this->path = $path . '.lock';
			$this->maxAge = $maxAge;
		}

		/**
		 * Returns `true` if this instance of the file lock owns the lock.
		 *
		 * @return bool
		 */
		public function isOwned() {
			return $this->owned;
		}

		/**
		 * Attempts to acquire the file lock. A file lock can only be acquired if the lock is not already held by
		 * another process, and the lock is not older than the max age specified in the constructor.
		 *
		 * @return bool
		 */
		public function acquire() {
			if (file_exists($this->path) && time() - fileatime($this->path) < $this->maxAge)
				return false;

			touch($this->path);

			return $this->owned = true;
		}

		/**
		 * Releases the file lock, if this instance holds the lock.
		 *
		 * @return bool
		 */
		public function release() {
			if (!$this->isOwned())
				return false;

			unlink($this->path);

			return true;
		}

		/**
		 * Waits up to `$timeout` milliseconds for the lock to become available. Returns `true` if the lock was able
		 * to be acquired before the specified timeout.
		 *
		 * @param int $timeout    The amount of time, in milliseconds, to wait for the lock before timing out
		 * @param int $retryDelay The amount of time, in milliseconds, to wait between attempts to acquire the lock
		 *
		 * @return bool
		 */
		public function await($timeout = 500, $retryDelay = 100) {
			$start = microtime(true);

			do {
				if ($this->acquire())
					return true;

				usleep($retryDelay * 1000);
			} while ((microtime(true) - $start) * 1000 < $timeout);

			return false;
		}
	}