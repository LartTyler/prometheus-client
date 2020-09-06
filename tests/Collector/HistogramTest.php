<?php
	namespace Tests\DaybreakStudios\PrometheusClient\Collector;

	use DaybreakStudios\PrometheusClient\Adapter\MemoryAdapter;
	use DaybreakStudios\PrometheusClient\Collector\Histogram;

	class HistogramTest extends \PHPUnit_Framework_TestCase {
		public function testInfoMethods() {
			$histogram = new Histogram(
				new MemoryAdapter(),
				'testInfoMethods1',
				'help text',
				[1, 5, 10],
				['testInfoMethodsLabel1']
			);

			$this->assertEquals('histogram', $histogram->getType());
			$this->assertEquals('testInfoMethods1', $histogram->getName());
			$this->assertEquals('help text', $histogram->getHelp());
			$this->assertEquals(['testInfoMethodsLabel1'], $histogram->getLabelNames());

			$prop = new \ReflectionProperty(Histogram::class, 'buckets');
			$prop->setAccessible(true);

			$this->assertEquals([1, 5, 10], $prop->getValue($histogram));
		}

		public function testUpdateCollect() {
			$histogram = new Histogram(
				new MemoryAdapter(),
				'testUpdateCollect1',
				'',
				[1, 5, 10],
				['testUpdateCollectLabel1']
			);

			$this->assertEmpty($histogram->collect()[0]->getSamples(), 'it initializes without any samples');

			$histogram->observe(1, ['testUpdateCollectLabel1' => 0]);

			$metric = $histogram->collect()[0];
			[$le1, $le5, $le10, $leInf, $count, $sum] = $metric->getSamples();

			$this->assertCount(
				6,
				$metric->getSamples(),
				'it records one sample per bucket (and +Inf), plus sum and count'
			);

			$this->assertEquals('testUpdateCollect1_bucket', $le1->getName());
			$this->assertArraySubset(['le' => 1], $le1->getLabels());
			$this->assertEquals(1, $le1->getValue());

			$this->assertEquals('testUpdateCollect1_bucket', $le5->getName());
			$this->assertArraySubset(['le' => 5], $le5->getLabels());
			$this->assertEquals(1, $le1->getValue());

			$this->assertEquals('testUpdateCollect1_bucket', $le1->getName());
			$this->assertArraySubset(['le' => 10], $le10->getLabels());
			$this->assertEquals(1, $le10->getValue());

			$this->assertEquals('testUpdateCollect1_bucket', $leInf->getName());
			$this->assertArraySubset(['le' => '+Inf'], $leInf->getLabels());
			$this->assertEquals(1, $leInf->getValue());

			$this->assertEquals('testUpdateCollect1_count', $count->getName());
			$this->assertEquals(1, $count->getValue());

			$this->assertEquals('testUpdateCollect1_sum', $sum->getName());
			$this->assertEquals(1, $sum->getValue());

			$histogram->observe(3, ['testUpdateCollectLabel1' => 0]);

			$metric = $histogram->collect()[0];
			[$le1, $le5, $le10, $leInf, $count, $sum] = $metric->getSamples();

			$this->assertEquals(1, $le1->getValue(), 'it places values into the correct bucket');
			$this->assertEquals(2, $le5->getValue(), 'it places values into the correct bucket');
			$this->assertEquals(2, $le10->getValue(), 'it places values into the correct bucket');
			$this->assertEquals(2, $leInf->getValue(), 'it places values into the correct bucket');
			$this->assertEquals(2, $count->getValue(), 'it increments the count for each observation');
			$this->assertEquals(4, $sum->getValue(), 'it properly sums all observations');

			$histogram->observe(3, ['testUpdateCollectLabel1' => 1]);

			$metric = $histogram->collect()[0];
			[$_, $_, $_, $_, $_, $_, $otherLe1, $otherLe5, $otherLe10, $otherLeInf, $otherCount, $otherSum] = $metric->getSamples(); // yikes

			$this->assertCount(12, $metric->getSamples(), 'it separates samples by label');

			$this->assertEquals(1, $le1->getValue(), 'it does not update buckets belonging to other labels');
			$this->assertEquals(2, $le5->getValue(), 'it does not update buckets belonging to other labels');
			$this->assertEquals(2, $le10->getValue(), 'it does not update buckets belonging to other labels');
			$this->assertEquals(2, $leInf->getValue(), 'it does not update buckets belonging to other labels');
			$this->assertEquals(2, $count->getValue(), 'it does not update buckets belonging to other labels');
			$this->assertEquals(4, $sum->getValue(), 'it does not update buckets belonging to other labels');

			$this->assertArraySubset(['testUpdateCollectLabel1' => 1], $otherLe1->getLabels(), 'it updates buckets belonging to the specified label');
			$this->assertEquals(0, $otherLe1->getValue(), 'it updates buckets belonging to the specified label');
			$this->assertEquals(1, $otherLe5->getValue(), 'it updates buckets belonging to the specified label');
			$this->assertEquals(1, $otherLe10->getValue(), 'it updates buckets belonging to the specified label');
			$this->assertEquals(1, $otherLeInf->getValue(), 'it updates buckets belonging to the specified label');
			$this->assertEquals(1, $otherCount->getValue(), 'it updates buckets belonging to the specified label');
			$this->assertEquals(3, $otherSum->getValue(), 'it updates buckets belonging to the specified label');
		}
	}