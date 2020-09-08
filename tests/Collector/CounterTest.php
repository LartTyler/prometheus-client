<?php
	namespace Tests\DaybreakStudios\PrometheusClient\Collector;

	use DaybreakStudios\PrometheusClient\Adapter\MemoryAdapter;
	use DaybreakStudios\PrometheusClient\Collector\Counter;

	class CounterTest extends \PHPUnit_Framework_TestCase {
		public function testInfoMethods() {
			$counter = new Counter(new MemoryAdapter(), 'testInfoMethods1', 'Help text', ['testInfoMethodsLabel1']);

			$this->assertEquals('counter', $counter->getType());
			$this->assertEquals('testInfoMethods1', $counter->getName());
			$this->assertEquals('Help text', $counter->getHelp());
			$this->assertEquals(['testInfoMethodsLabel1'], $counter->getLabelNames());
		}

		public function testUpdateCollect() {
			$counter = new Counter(new MemoryAdapter(), 'testUpdateCollect1', '', ['testUpdateCollectLabel1']);

			$this->assertEmpty($counter->collect()[0]->getSamples(), 'it initializes without any samples');

			$counter->increment(['testUpdateCollectLabel1' => 0]);
			$this->assertEquals(
				1,
				$counter->collect()[0]->getSamples()[0]->getValue(),
				'it increments by one without a step'
			);

			$counter->increment(['testUpdateCollectLabel1' => 0], 2);
			$this->assertEquals(3, $counter->collect()[0]->getSamples()[0]->getValue(), 'it increments with a step');

			$counter->increment(['testUpdateCollectLabel1' => 3]);
			$metric = $counter->collect()[0];

			$this->assertCount(2, $metric->getSamples(), 'it separates samples by label');

			$this->assertEquals(['testUpdateCollectLabel1' => 0], $metric->getSamples()[0]->getLabels());
			$this->assertEquals(3, $metric->getSamples()[0]->getValue());

			$this->assertEquals(['testUpdateCollectLabel1' => 3], $metric->getSamples()[1]->getLabels());
			$this->assertEquals(1, $metric->getSamples()[1]->getValue());
		}
	}