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
		 * FilesystemLock constructor.
		 *
		 * @param string $path
		 */
		public function __construct($path) {
			$this->path = $path . '.lock';
		}

		/**
		 * @return bool
		 */
		public function isOwned() {
			return $this->owned;
		}

		/**
		 * @return bool
		 */
		public function aquire() {
			if (file_exists($this->path))
				return false;

			touch($this->path);

			return $this->owned = true;
		}

		/**
		 * @return bool
		 */
		public function release() {
			if (!$this->isOwned())
				return false;

			unlink($this->path);

			return true;
		}

		/**
		 * @param int $timeout
		 * @param int $retryDelay
		 *
		 * @return bool
		 */
		public function await($timeout = 500, $retryDelay = 100) {
			$start = microtime(true);

			do {
				if ($this->aquire())
					return true;

				usleep($retryDelay * 1000);
			} while ((microtime(true) - $start) * 1000 < $timeout);

			return false;
		}
	}