<?php
	namespace DaybreakStudios\PrometheusClient\Adapter\Filesystem;

	class FilesystemLock {
		/**
		 * @var string
		 */
		protected $path;

		/**
		 * @var resource
		 */
		protected $handle;

		/**
		 * @var bool
		 */
		protected $owned = false;

		/**
		 * FilesystemLock constructor.
		 *
		 * @param string $path   The path to the file this lock belongs to
		 * @param string $suffix The lock file suffix, added to the end of the filename being locked
		 */
		public function __construct($path, $suffix = '.lock') {
			$this->path = $path . $suffix;
			$this->handle = fopen($this->path, 'w');
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
		 * another process.
		 *
		 * @return bool
		 * @see flock()
		 */
		public function acquire() {
			if (!flock($this->handle, LOCK_EX))
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

			flock($this->handle, LOCK_UN);

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
		public function await($timeout = 500, $retryDelay = 10) {
			$start = microtime(true);

			do {
				if ($this->acquire())
					return true;

				usleep($retryDelay * 1000);
			} while ((microtime(true) - $start) * 1000 < $timeout);

			return false;
		}

		/**
		 * @return void
		 */
		public function __destruct() {
			$this->release();

			fclose($this->handle);
		}
	}