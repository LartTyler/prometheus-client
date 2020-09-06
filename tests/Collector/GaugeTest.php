<?php
	namespace Tests\DaybreakStudios\PrometheusClient\Collector;

	use DaybreakStudios\PrometheusClient\Adapter\MemoryAdapter;
	use DaybreakStudios\PrometheusClient\Collector\Gauge;

	class GaugeTest extends \PHPUnit_Framework_TestCase {
		public function testInfoMethods() {
			$gauge = new Gauge(new MemoryAdapter(), 'testInfoMethods1', 'help text', ['testInfoMethodsLabel1']);

			$this->assertEquals('gauge', $gauge->getType());
			$this->assertEquals('testInfoMethods1', $gauge->getName());
			$this->assertEquals('help text', $gauge->getHelp());
			$this->assertEquals(['testInfoMethodsLabel1'], $gauge->getLabelNames());
		}

		public function testUpdateCollect() {
			$gauge = new Gauge(new MemoryAdapter(), 'testUpdateCollect1', '', ['testUpdateCollectLabel1']);

			$this->assertEmpty($gauge->collect()[0]->getSamples(), 'it initializes without any samples');

			$gauge->increment(['testUpdateCollectLabel1' => 0]);
			$this->assertEquals(1, $gauge->collect()[0]->getSamples()[0]->getValue(), 'it increments without a step');

			$gauge->increment(['testUpdateCollectLabel1' => 0], 2);
			$this->assertEquals(3, $gauge->collect()[0]->getSamples()[0]->getValue(), 'it increments with a step');

			$gauge->decrement(['testUpdateCollectLabel1' => 0]);
			$this->assertEquals(2, $gauge->collect()[0]->getSamples()[0]->getValue(), 'it decrements without a step');

			$gauge->decrement(['testUpdateCollectLabel1' => 0], 2);
			$this->assertEquals(0, $gauge->collect()[0]->getSamples()[0]->getValue(), 'it decrements with a step');

			$gauge->set(5, ['testUpdateCollectLabel1' => 0]);
			$this->assertEquals(5, $gauge->collect()[0]->getSamples()[0]->getValue(), 'it sets');

			$gauge->set(10, ['testUpdateCollectLabel1' => 1]);
			$metric = $gauge->collect()[0];

			$this->assertCount(2, $metric->getSamples(), 'it separates samples by label');

			$this->assertEquals(['testUpdateCollectLabel1' => 0], $metric->getSamples()[0]->getLabels());
			$this->assertEquals(5, $metric->getSamples()[0]->getValue());

			$this->assertEquals(['testUpdateCollectLabel1' => 1], $metric->getSamples()[1]->getLabels());
			$this->assertEquals(10, $metric->getSamples()[1]->getValue());
		}
	}