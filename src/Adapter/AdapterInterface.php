<?php
	namespace DaybreakStudios\PrometheusClient\Adapter;

	interface AdapterInterface {
		/**
		 * @param string $key
		 *
		 * @return bool
		 */
		public function exists($key);

		/**
		 * @param string $key
		 * @param mixed  $value
		 *
		 * @return bool
		 */
		public function set($key, $value);

		/**
		 * @param string $key
		 *
		 * @return mixed
		 */
		public function get($key);

		/**
		 * @param string $key
		 * @param mixed  $value
		 *
		 * @return bool
		 */
		public function create($key, $value);

		/**
		 * @param string $key
		 * @param int    $step
		 * @param int    $initialValue
		 *
		 * @return bool
		 */
		public function increment($key, $step = 1, $initialValue = 0);

		/**
		 * @param string $key
		 * @param int    $step
		 * @param int    $initialValue
		 *
		 * @return bool
		 */
		public function decrement($key, $step = 1, $initialValue = 0);

		/**
		 * @param string $key
		 *
		 * @return bool
		 */
		public function delete($key);

		/**
		 * @param string   $key
		 * @param callable $mutator
		 * @param int      $timeout
		 *
		 * @return bool
		 */
		public function compareAndSwap($key, callable $mutator, $timeout = 500);

		/**
		 * @param string $prefix
		 *
		 * @return \Iterator
		 */
		public function search($prefix);

		/**
		 * @return bool
		 */
		public function clear();
	}