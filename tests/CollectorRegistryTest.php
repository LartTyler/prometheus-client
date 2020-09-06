<?php
	namespace Tests\DaybreakStudios\PrometheusClient;

	use DaybreakStudios\PrometheusClient\Adapter\MemoryAdapter;
	use DaybreakStudios\PrometheusClient\Collector\Counter;
	use DaybreakStudios\PrometheusClient\Collector\Gauge;
	use DaybreakStudios\PrometheusClient\CollectorRegistry;

	class CollectorRegistryTest extends \PHPUnit_Framework_TestCase {
		public function testRegisterUnregister() {
			$registry = new CollectorRegistry();
			$adapter = new MemoryAdapter();

			$registry->register(new Counter($adapter, 'testRegisterUnregister1', ''));
			$this->assertTrue($registry->has('testRegisterUnregister1', Counter::class), 'it registers collectors');

			$registry->unregister('testRegisterUnregister1');
			$this->assertFalse($registry->has('testRegisterUnregister1'), 'it unregisters collectors by name');

			$registry->register($inst = new Counter($adapter, 'testRegisterUnregister2', ''));
			$registry->unregister($inst);
			$this->assertFalse($registry->has('testRegisterUnregister2'), 'it unregisters collectors by instance');
		}

		public function testHas() {
			$registry = new CollectorRegistry();
			$adapter = new MemoryAdapter();

			$registry->register(new Counter($adapter, 'testHas1', ''));
			$this->assertTrue($registry->has('testHas1'), 'it finds collectors by name');
			$this->assertTrue($registry->has('testHas1', Counter::class), 'it finds collectors by name and class');

			$registry->register(new Counter($adapter, 'testHas2', ''));
			$this->assertFalse(
				$registry->has('testHas2', Gauge::class),
				'it does not match collects if class does not match'
			);

			$this->assertFalse($registry->has('testHas3'), 'it does not find non-existent collectors');
		}

		public function testGet() {
			$registry = new CollectorRegistry();
			$adapter = new MemoryAdapter();

			$registry->register(new Counter($adapter, 'testGet1', ''));
			$this->assertNotNull($registry->get('testGet1'), 'it retrieves collectors by name');

			$this->assertNull($registry->get('testGet2', false), 'it does not retrieve non-existent collectors');

			$registry->register(new Counter($adapter, 'testGet3', ''));
			$this->assertNotNull($registry->getCounter('testGet3'), 'it retrieves counters');

			$this->assertNull(
				$registry->getGauge('testGet3', false),
				'it does not retrieve collectors if types do not match'
			);
		}

		public function testGetMissingThrow() {
			$registry = new CollectorRegistry();

			$this->expectExceptionMessage('This registry has no collectors named testGetMissingThrow1');
			$registry->get('testGetMissingThrow1');

			$registry->register(new Counter(new MemoryAdapter(), 'testGetMissingThrow2', ''));

			$this->expectExceptionMessage(
				sprintf('Expected testGetMissingThrow2 to be a %s, but got a %s', Gauge::class, Counter::class)
			);
			$registry->getGauge('testGetMissingThrow2');
		}

		public function testCollect() {
			$registry = new CollectorRegistry();
			$adapter = new MemoryAdapter();

			$registry->register($counter = new Counter($adapter, 'testCollect1', ''));
			$registry->register($gauge = new Gauge($adapter, 'testCollect2', '', ['testLabel1']));

			$counter->increment([], 3);
			$gauge->set(5.5, ['testLabel1' => 1]);

			$metrics = $registry->collect();

			$this->assertCount(2, $metrics);

			$this->assertEquals('testCollect1', $metrics[0]->getName());
			$this->assertEquals(Counter::TYPE, $metrics[0]->getType());
			$this->assertCount(1, $metrics[0]->getSamples());
			$this->assertEquals(3, $metrics[0]->getSamples()[0]->getValue());
			$this->assertCount(0, $metrics[0]->getSamples()[0]->getLabels());

			$this->assertEquals('testCollect2', $metrics[1]->getName());
			$this->assertEquals(Gauge::TYPE, $metrics[1]->getType());
			$this->assertCount(1, $metrics[1]->getSamples());
			$this->assertEquals(5.5, $metrics[1]->getSamples()[0]->getValue());
			$this->assertEquals(['testLabel1' => 1], $metrics[1]->getSamples()[0]->getLabels());
		}
	}