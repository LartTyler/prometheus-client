<?php
	namespace DaybreakStudios\PrometheusClient\Adapter;

	interface AdapterInterface {
		/**
		 * Checks if a key exists in the store.
		 *
		 * @param string $key
		 *
		 * @return bool
		 */
		public function exists($key);

		/**
		 * Sets the given key's value. This method does not have to be executed synchronously with other processes.
		 *
		 * @param string $key
		 * @param mixed  $value
		 *
		 * @return bool
		 */
		public function set($key, $value);

		/**
		 * Retrieves a value from the store.
		 *
		 * @param string $key
		 *
		 * @return mixed
		 */
		public function get($key);

		/**
		 * Adds a new item to the store only if the given key does not already exist.
		 *
		 * @param string $key
		 * @param mixed  $value
		 *
		 * @return bool
		 */
		public function create($key, $value);

		/**
		 * Increments the value of a key in the store. This method MUST be executed such that simultaneous increments
		 * from different processes (threads) properly increment the value.
		 *
		 * @param string $key
		 * @param int    $step
		 * @param int    $initialValue
		 *
		 * @return bool
		 */
		public function increment($key, $step = 1, $initialValue = 0);

		/**
		 * Decrements the value of a key in the store. This method MUST be executed such that simultaneous decrements
		 * from different processes (threads) properly decrement the value.
		 *
		 * @param string $key
		 * @param int    $step
		 * @param int    $initialValue
		 *
		 * @return bool
		 */
		public function decrement($key, $step = 1, $initialValue = 0);

		/**
		 * Removes a key from the store.
		 *
		 * @param string $key
		 *
		 * @return bool
		 */
		public function delete($key);

		/**
		 * Changes the value of a key using the return value of `$mutator`. This method MUST be executed such that
		 * simultaneous calls from different processes (threads) operate in a defined way. For example, if two threads
		 * were to call modify to multiply the value of `$key` by two, the mutators must resolve synchronously, in the
		 * order they were invoked.
		 *
		 * The `$mutator` callable must accept one argument, containing the current value of the key. It MUST return
		 * the new value of the key.
		 *
		 * @param string   $key
		 * @param callable $mutator
		 * @param int      $timeout
		 *
		 * @return bool
		 */
		public function modify($key, callable $mutator, $timeout = 500);

		/**
		 * Searches the store for a given prefix, and returns an iterator that provides the key and value of the items
		 * in the store that matched the prefix.
		 *
		 * @param string $prefix
		 *
		 * @return \Iterator
		 */
		public function search($prefix);

		/**
		 * Deletes all items from the store.
		 *
		 * @return bool
		 */
		public function clear();
	}