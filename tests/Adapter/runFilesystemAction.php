#!/usr/bin/env php
<?php
	use DaybreakStudios\PrometheusClient\Adapter\FilesystemAdapter;

	require __DIR__ . '/../../vendor/autoload.php';

	if ($argc !== 5)
		usage(1);

	$action = $argv[1];

	$adapter = new FilesystemAdapter($argv[2]);

	if (!method_exists($adapter, $action))
		usage(1);

	$key = $argv[3];

	for ($i = 0, $ii = (int)$argv[4]; $i < $ii; $i++) {
		call_user_func([$adapter, $action], $key);

		usleep(rand(1, 100000));
	}

	$adapter->increment($key . '_finished');

	/**
	 * @param int $exit
	 *
	 * @return void
	 */
	function usage($exit = 0) {
		printf('Usage: %s increment|decrement <directory> <key> <iterations>' . PHP_EOL, __FILE__);

		exit($exit);
	}