<?php
	namespace DaybreakStudios\PrometheusClient\Export;

	class Sample implements SampleInterface {
		/**
		 * @var float|int
		 */
		protected $value;

		/**
		 * @var array
		 */
		protected $labels;

		/**
		 * @var string|null
		 */
		protected $name;

		/**
		 * Sample constructor.
		 *
		 * @param int|float   $value
		 * @param array       $labels
		 * @param string|null $name
		 */
		public function __construct($value, array $labels, ?string $name = null) {
			$this->value = $value;
			$this->labels = $labels;
			$this->name = $name;
		}

		/**
		 * @return string|null
		 */
		public function getName(): ?string {
			return $this->name;
		}

		/**
		 * @return array
		 */
		public function getLabels(): array {
			return $this->labels;
		}

		/**
		 * @return float|int
		 */
		public function getValue() {
			return $this->value;
		}
	}