<?php
	namespace Tests\DaybreakStudios\PrometheusClient\Adapter;

	use DaybreakStudios\PrometheusClient\Adapter\FilesystemAdapter;

	class FilesystemAdapterTest extends \PHPUnit_Framework_TestCase {
		/**
		 * @var string
		 */
		protected $directory;

		/**
		 * @var FilesystemAdapter
		 */
		protected $adapter;

		/**
		 * @return void
		 */
		public function testParallelIncrement() {
			$key = 'test_parallel_increment';

			$processCount = 500;
			$perProcessIterations = 100;

			for ($i = 0; $i < $processCount; $i++) {
				exec(
					sprintf(
						'%s %s %s %d > /dev/null &',
						escapeshellarg(__DIR__ . '/runFilesystemIncrement.php'),
						$this->directory,
						$key,
						$perProcessIterations
					)
				);
			}

			while ($this->adapter->get($key . '_finished') !== $processCount)
				usleep(500000);

			$this->assertEquals($processCount * $perProcessIterations, $this->adapter->get($key));
		}

		/**
		 * {@inheritdoc}
		 */
		protected function setUp() {
			mkdir($this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid());

			$this->adapter = new FilesystemAdapter($this->directory);
		}

		/**
		 * {@inheritdoc}
		 */
		protected function tearDown() {
			$this->adapter->clear();
		}
	}
