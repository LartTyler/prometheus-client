<?php
	namespace Tests\DaybreakStudios\PrometheusClient\Export\Render;

	use DaybreakStudios\PrometheusClient\Adapter\MemoryAdapter;
	use DaybreakStudios\PrometheusClient\Collector\Counter;
	use DaybreakStudios\PrometheusClient\Collector\Gauge;
	use DaybreakStudios\PrometheusClient\Collector\Histogram;
	use DaybreakStudios\PrometheusClient\CollectorRegistry;
	use DaybreakStudios\PrometheusClient\Export\Render\TextRenderer;

	class TextRendererTest extends \PHPUnit_Framework_TestCase {
		public function testRender() {
			$renderer = new TextRenderer();

			$this->assertEquals('text/plain; version=0.0.4', $renderer->getMimeType());

			$registry = new CollectorRegistry();
			$adapter = new MemoryAdapter();

			$registry->register($counter = new Counter($adapter, 'counter', 'counter help'));
			$registry->register($counterWithLabels = new Counter($adapter, 'counter_with_labels', 'counter with labels help', ['label1']));
			$registry->register($gauge = new Gauge($adapter, 'gauge', 'gauge help'));
			$registry->register($histogram = new Histogram($adapter, 'histogram', 'histogram help', [1, 3, 5]));

			$counter->increment();

			$counterWithLabels->increment(['label1' => 0]);
			$counterWithLabels->increment(['label1' => 1], 2);

			$gauge->set(5.7);

			$histogram->observe(1);
			$histogram->observe(2);
			$histogram->observe(3);
			$histogram->observe(10);

			$lines = array_filter(explode("\n", $renderer->render($registry->collect())));

			/**
			 * counter             = 3 output lines
			 * counter_with_labels = 4 output lines
			 * gauge               = 3 output lines
			 * histogram           = 8 output lines
			 *                     = 18 output lines total
			 */
			$this->assertCount(18, $lines);
			$this->assertEquals([
				'# HELP counter counter help',
				'# TYPE counter counter',
				'counter 1',
				'# HELP counter_with_labels counter with labels help',
				'# TYPE counter_with_labels counter',
				'counter_with_labels{label1="0"} 1',
				'counter_with_labels{label1="1"} 2',
				'# HELP gauge gauge help',
				'# TYPE gauge gauge',
				'gauge 5.7',
				'# HELP histogram histogram help',
				'# TYPE histogram histogram',
				'histogram_bucket{le="1"} 1',
				'histogram_bucket{le="3"} 3',
				'histogram_bucket{le="5"} 3',
				'histogram_bucket{le="+Inf"} 4',
				'histogram_count 4',
				'histogram_sum 16',
			], $lines);
		}
	}