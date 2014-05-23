<?php 

namespace Icti\Sloth\Primitives;

class String {

		protected $value;

		public function __construct($value) {
				$this->value = (string)$value;
		}

		public function __toString() {
				return $this->get();
		}

		public function get() {
				return $this->value;
		}
}

?>