<?php

namespace Icti\Sloth\MetaModel;

class Field {

		const Types = 'String|Text|RTE|Integer|Check|Files|Images';

		protected $model;
		protected $name;
		protected $type;
		protected $attributes;

		public function __construct(
				Model &$model,
				$name,
				$type,
				$attributes
		) {
				$this->model = $model;
				$this->name = $name;
				$this->setType($type);
				$this->attributes = $attributes;
		}

		protected function setType($type) {
				$type = trim($type);
				$pattern = '/^(' . self::Types . ')$/';
				if (preg_match($pattern, $type) !== 1) {
						throw new InvalidFieldTypeException($this->model, $this->name, $type);
				}
				$this->type = $type;
		}

		public function getModel() {
				return $this->model;
		}

		public function getName() {
				return $this->name;
		}

		public function getType () {
				return $this->type;
		}

		public function getAttributes() {
				return $this->attributes;
		}

}

?>