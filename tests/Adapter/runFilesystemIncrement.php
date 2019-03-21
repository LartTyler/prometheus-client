#!/usr/bin/env php
<?php
	use DaybreakStudios\PrometheusClient\Adapter\FilesystemAdapter;

	require __DIR__ . '/../../vendor/autoload.php';

	if ($argc !== 4) {
		printf('Usage: %s <directory> <key> <iterations>' . PHP_EOL, __FILE__);

		exit(1);
	}

	$adapter = new FilesystemAdapter($argv[1]);
	$key = $argv[2];

	for ($i = 0, $ii = (int)$argv[3]; $i < $ii; $i++) {
		$adapter->increment($key);

		usleep(rand(1, 100000));
	}

	$adapter->increment($key . '_finished');