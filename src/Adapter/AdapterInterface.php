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
		public function exists(string $key): bool;

		/**
		 * Sets the given key's value. This method does not have to be executed synchronously with other processes.
		 *
		 * @param string    $key
		 * @param int|float $value
		 *
		 * @return bool
		 */
		public function set(string $key, $value): bool;

		/**
		 * Retrieves a value from the store.
		 *
		 * @param string $key
		 *
		 * @return int|float
		 */
		public function get(string $key);

		/**
		 * Adds a new item to the store only if the given key does not already exist.
		 *
		 * @param string    $key
		 * @param int|float $value
		 *
		 * @return bool
		 */
		public function create(string $key, $value): bool;

		/**
		 * Increments the value of a key in the store. This method MUST be executed such that simultaneous increments
		 * from different processes (threads) properly increment the value.
		 *
		 * @param string    $key
		 * @param int|float $step
		 * @param int|float $initialValue
		 *
		 * @return bool
		 */
		public function increment(string $key, $step = 1, $initialValue = 0): bool;

		/**
		 * Decrements the value of a key in the store. This method MUST be executed such that simultaneous decrements
		 * from different processes (threads) properly decrement the value.
		 *
		 * @param string    $key
		 * @param int|float $step
		 * @param int|float $initialValue
		 *
		 * @return bool
		 */
		public function decrement(string $key, $step = 1, $initialValue = 0): bool;

		/**
		 * Removes a key from the store.
		 *
		 * @param string $key
		 *
		 * @return bool
		 */
		public function delete(string $key): bool;

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
		public function modify(string $key, callable $mutator, int $timeout = 500): bool;

		/**
		 * Searches the store for a given prefix, and returns a generator that provides the key and value of the items
		 * in the store that matched the prefix.
		 *
		 * Each step of the generator will return an array containing two elements, the key and it's value, in that
		 * order.
		 *
		 * @param string $prefix
		 *
		 * @return \Generator|array
		 */
		public function search(string $prefix): \Generator;

		/**
		 * Deletes all items from the store.
		 *
		 * @return bool
		 */
		public function clear(): bool;
	}