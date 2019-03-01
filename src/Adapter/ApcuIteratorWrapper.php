<?php /** @noinspection PhpComposerExtensionStubsInspection */
	namespace DaybreakStudios\PrometheusClient\Adapter;

	class ApcuIteratorWrapper implements \Iterator {
		/**
		 * @var \APCUIterator
		 */
		protected $iterator;

		/**
		 * ApcuIteratorWrapper constructor.
		 *
		 * @param \APCUIterator $iterator
		 */
		public function __construct(\APCUIterator $iterator) {
			$this->iterator = $iterator;
		}

		/**
		 * {@inheritdoc}
		 */
		public function current() {
			return $this->iterator->current()['value'];
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
			return $this->iterator->current()['key'];
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