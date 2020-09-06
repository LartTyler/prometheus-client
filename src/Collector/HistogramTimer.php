<?php
	namespace DaybreakStudios\PrometheusClient\Collector;

	use DaybreakStudios\PrometheusClient\Exception\HistogramTimerException;
	use Symfony\Component\Stopwatch\Stopwatch;

	class HistogramTimer {
		/**
		 * Indicates that the timer should observe values with millisecond precision.
		 */
		public const PRECISION_MILLISECONDS = 1;

		/**
		 * Indicates that the timer should observe values with second precision.
		 */
		public const PRECISION_SECONDS = 1000;

		protected const STOPWATCH_NAME = 'histogram_timer';

		/**
		 * @var Histogram
		 */
		protected $histogram;

		/**
		 * @var Stopwatch
		 */
		protected $stopwatch;

		/**
		 * @var int
		 */
		protected $precision;

		/**
		 * @var bool
		 */
		protected $observed = false;

		/**
		 * Timer constructor.
		 *
		 * @param Histogram $histogram
		 * @param int       $precision
		 */
		public function __construct(Histogram $histogram, int $precision = self::PRECISION_MILLISECONDS) {
			$this->histogram = $histogram;
			$this->precision = $precision;

			$this->stopwatch = new Stopwatch();
			$this->stopwatch->start(static::STOPWATCH_NAME);
		}

		/**
		 * @param array $labels
		 * @param bool  $throwOnMultiObserve
		 *
		 * @return $this
		 * @throws HistogramTimerException If the timer is observed more than once (and $throwOnMultiObserve is `false`)
		 */
		public function observe(array $labels = [], bool $throwOnMultiObserve = true) {
			if ($this->observed && $throwOnMultiObserve)
				throw HistogramTimerException::multipleObservations();

			if (!$this->observed) {
				$event = $this->stopwatch->stop(static::STOPWATCH_NAME);
				$this->histogram->observe((int)($event->getDuration() / $this->precision));

				$this->observed = true;
			}

			return $this;
		}

		/**
		 * @return void
		 */
		public function __destruct() {
			if ($this->observed)
				return;

			trigger_error(
				'A histogram timer is being destroyed without being observed. This is probably a mistake.',
				E_USER_WARNING
			);
		}
	}