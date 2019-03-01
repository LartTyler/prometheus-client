<?php
	namespace DaybreakStudios\PrometheusClient\Export;

	class Metric implements MetricInterface {
		/**
		 * @var string
		 */
		protected $name;

		/**
		 * @var string
		 */
		protected $type;

		/**
		 * @var string
		 */
		protected $help;

		/**
		 * @var SampleInterface[]
		 */
		protected $samples;

		/**
		 * Metric constructor.
		 *
		 * @param string            $name
		 * @param string            $type
		 * @param string            $help
		 * @param SampleInterface[] $samples
		 */
		public function __construct($name, $type, $help, array $samples) {
			$this->name = $name;
			$this->type = $type;
			$this->help = $help;
			$this->samples = $samples;
		}

		/**
		 * @return string
		 */
		public function getName() {
			return $this->name;
		}

		/**
		 * @return string
		 */
		public function getType() {
			return $this->type;
		}

		/**
		 * @return string
		 */
		public function getHelp() {
			return $this->help;
		}

		/**
		 * @return SampleInterface[]
		 */
		public function getSamples() {
			return $this->samples;
		}
	}