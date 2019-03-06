<?php
	namespace DaybreakStudios\PrometheusClient\Adapter\Filesystem;

	use DaybreakStudios\PrometheusClient\Adapter\FilesystemAdapter;

	class FilesystemIterator implements \Iterator {
		/**
		 * @var \ArrayIterator
		 */
		protected $iterator;

		/**
		 * @var FilesystemAdapter
		 */
		protected $adapter;

		/**
		 * FilesystemIterator constructor.
		 *
		 * @param FilesystemAdapter $adapter
		 * @param array             $keys
		 */
		public function __construct(FilesystemAdapter $adapter, array $keys) {
			$this->adapter = $adapter;
			$this->iterator = new \ArrayIterator($keys);
		}

		/**
		 * {@inheritdoc}
		 */
		public function current() {
			return $this->adapter->get($this->iterator->current());
		}

		/**
		 * {@inheritdoc}
		 */
		public function next() {
			$this->iterator->next();
		}

		/**
		 * {@inheritdoc}
		 */
		public function key() {
			return $this->iterator->current();
		}

		/**
		 * {@inheritdoc}
		 */
		public function valid() {
			return $this->iterator->valid();
		}

		/**
		 * {@inheritdoc}
		 */
		public function rewind() {
			$this->iterator->rewind();
		}
	}