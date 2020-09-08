<?php
	namespace Tests\DaybreakStudios\PrometheusClient\Adapter;

	use DaybreakStudios\PrometheusClient\Adapter\Filesystem\FilesystemAdapter;

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
		public function testKeyExists() {
			$key = uniqid('', true);

			$this->assertFalse($this->adapter->exists($key));

			$this->adapter->create($key, 0);

			$this->assertTrue($this->adapter->exists($key));
		}

		/**
		 * @return void
		 */
		public function testCreateInitializesValues() {
			$key = uniqid('', true);

			$this->adapter->create($key, 1);

			$this->assertEquals(1, $this->adapter->get($key));
		}

		/**
		 * @return void
		 */
		public function testCreateDoesNotOverwriteExistingKeys() {
			$key = uniqid('', true);

			$this->adapter->create($key, 5);
			$this->adapter->create($key, 10);

			$this->assertEquals(5, $this->adapter->get($key));
		}

		/**
		 * @return void
		 */
		public function testGetReturnsNullOnMissingKeys() {
			$key = uniqid('', true);

			$this->assertNull($this->adapter->get($key));
		}

		/**
		 * @return void
		 */
		public function testGetReturnsCorrectValues() {
			$key = uniqid('', true);

			$this->adapter->set($key, 10);

			$this->assertEquals(10, $this->adapter->get($key));
		}

		/**
		 * @return void
		 */
		public function testSetCreatesNewValues() {
			$key = uniqid('', true);

			$this->adapter->set($key, 5);

			$this->assertEquals(5, $this->adapter->get($key));
		}

		/**
		 * @return void
		 */
		public function testSetUpdatesExistingValues() {
			$key = uniqid('', true);

			$this->adapter->set($key, 5);

			$this->assertEquals(5, $this->adapter->get($key));

			$this->adapter->set($key, 10);

			$this->assertEquals(10, $this->adapter->get($key));
		}

		/**
		 * @return void
		 */
		public function testDeleteRemovesKeys() {
			$key = uniqid('', true);

			$this->adapter->set($key, 10);
			$this->adapter->delete($key);

			$this->assertFalse($this->adapter->exists($key));
		}

		/**
		 * @return void
		 */
		public function testParallelIncrement() {
			$this->runParallelFilesystemAction('increment', 'test_parallel_increment');
		}

		/**
		 * @return void
		 */
		public function testParallelDecrement() {
			$this->runParallelFilesystemAction('decrement', 'test_parallel_decrement');
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

		/**
		 * @param string $action
		 * @param string $key
		 *
		 * @return void
		 */
		protected function runParallelFilesystemAction($action, $key) {
			$processCount = 50;
			$perProcessIterations = 100;

			for ($i = 0; $i < $processCount; $i++) {
				exec(
					sprintf(
						'%s %s %s %s %d > /dev/null &',
						escapeshellarg(__DIR__ . '/runFilesystemAction.php'),
						escapeshellarg($action),
						escapeshellarg($this->directory),
						escapeshellarg($key),
						$perProcessIterations
					)
				);
			}

			while ($this->adapter->get($key . '_finished') !== $processCount)
				usleep(500000);

			$this->assertEquals(
				($action === 'decrement' ? -1 : 1) * $processCount * $perProcessIterations,
				$this->adapter->get($key),
				sprintf(
					'Running %d %ss in %d parallel processes produces deterministic results',
					$perProcessIterations,
					$action,
					$processCount
				)
			);
		}
	}
