<?php
	namespace Tests\DaybreakStudios\PrometheusClient\Adapter;

	use DaybreakStudios\PrometheusClient\Adapter\Redis\RedisClientConfiguration;
	use DaybreakStudios\PrometheusClient\Adapter\RedisAdapter;

	class RedisAdapterTest extends \PHPUnit_Framework_TestCase {
		/**
		 * @var RedisAdapter
		 */
		protected $adapter;

		/**
		 * @var \Redis;
		 */
		protected $redis;

		public function testExists() {
			$this->assertFalse($this->adapter->exists('testExists1'), 'it does not find non-existent keys');

			$this->adapter->set('testExists2', 1);
			$this->assertTrue($this->adapter->exists('testExists2'), 'it finds existent keys');
		}

		public function testGetSet() {
			$this->adapter->set('testGetSet1', 1);

			$this->assertEquals(1, $this->adapter->get('testGetSet1'), 'it sets and retrieves values');
		}

		public function testKeyPrefixesAreUsed() {
			$this->adapter->set('testKeyPrefixesAreUsed1', 1);

			$this->assertEquals(
				1,
				$this->redis->get(RedisClientConfiguration::DEFAULT_KEY_PREFIX . 'testKeyPrefixesAreUsed1'),
				'it creates items with prefixed keys'
			);
		}

		public function testCreate() {
			$this->adapter->create('testCreate1', 1);
			$this->assertEquals(1, $this->adapter->get('testCreate1'), 'it creates new items');

			$this->adapter->create('testCreate1', 10);
			$this->assertEquals(1, $this->adapter->get('testCreate1'), 'it does not overwrite existing items');
		}

		public function testIncrDecr() {
			$this->adapter->set('testIncrDecr1', 1);

			$this->adapter->increment('testIncrDecr1');
			$this->assertEquals(2, $this->adapter->get('testIncrDecr1'), 'it increments values');

			$this->adapter->decrement('testIncrDecr1');
			$this->assertEquals(1, $this->adapter->get('testIncrDecr1'), 'it decrements values');

			$this->adapter->increment('testIncrDecr1', 3);
			$this->assertEquals(4, $this->adapter->get('testIncrDecr1'), 'it increments values with a step');

			$this->adapter->decrement('testIncrDecr1', 2);
			$this->assertEquals(2, $this->adapter->get('testIncrDecr1'), 'it decrements values with a step');

			$this->adapter->increment('testIncrDecr2', 1, 5);
			$this->assertEquals(6, $this->adapter->get('testIncrDecr2'), 'it increments values with an initial value');

			$this->adapter->decrement('testIncrDecr3', 1, 5);
			$this->assertEquals(4, $this->adapter->get('testIncrDecr3'), 'it decrements values with an initial value');
		}

		public function testDelete() {
			$this->adapter->set('testDelete1', 1);
			$this->adapter->delete('testDelete1');

			$this->assertFalse($this->adapter->exists('testDelete1'), 'it deletes items');
		}

		public function testModify() {
			$this->adapter->set('testModify1', 1);
			$this->adapter->modify('testModify1', function(int $value) {
				return $value + 3;
			});

			$this->assertEquals(4, $this->adapter->get('testModify1'), 'it modifies keys');
		}

		public function testSearch() {
			$this->adapter->set('testSearch1', 1);
			$this->adapter->set('testSearch2', 2);
			$this->redis->set('testSearch3', 3);

			$found = [];

			foreach ($this->adapter->search('testSearch') as $item)
				$found[$item[0]] = $item[1];

			$this->assertArrayHasKey('testSearch1', $found);
			$this->assertEquals(1, $found['testSearch1']);

			$this->assertArrayHasKey('testSearch2', $found);
			$this->assertEquals(2, $found['testSearch2']);

			$this->assertArrayNotHasKey('testSearch3', $found);
		}

		public function testClear() {
			$this->adapter->set('testClear1', 1);
			$this->adapter->set('testClear2', 2);
			$this->adapter->set('testClear3', 3);
			$this->redis->set('testClear4', 4);

			$this->adapter->clear();

			$remainingCount = 0;

			foreach ($this->adapter->search('') as $_)
				++$remainingCount;

			$this->assertEquals(0, $remainingCount, 'it deletes all of the "owned" keys');
			$this->assertEquals(4, $this->redis->get('testClear4'), 'it does not delete non-prefixed keys');
		}

		/**
		 * {@inheritdoc}
		 */
		protected function setUp() {
			$config = new RedisClientConfiguration(getenv('DBSTUDIOS_PROM_REDIS_HOST') ?: 'localhost');

			if ($port = getenv('DBSTUDIOS_PROM_REDIS_PORT'))
				$config->setPort($port);

			if ($password = getenv('DBSTUDIOS_PROM_REDIS_PASSWORD'))
				$config->setPassword($password);

			$this->adapter = new RedisAdapter($config);

			$this->redis = new \Redis();
			$this->redis->connect(
				$config->getHost(),
				$config->getPort(),
				$config->getTimeout(),
				null,
				$config->getRetryInterval(),
				$config->getRetryTimeout()
			);

			if ($config->getPassword() !== null)
				$this->redis->auth($config->getPassword());
		}

		protected function tearDown() {
			$cursor = null;

			while (($keys = $this->redis->scan($cursor)) !== false)
				$this->redis->del($keys);
		}
	}