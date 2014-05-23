<?php

namespace Icti\Sloth\MetaModel;
use Icti\Sloth\Primitives;

class Relation {

		const Types = 'HasOne|HasMany';

		protected $model;

		/**
 		 * @var Primitives\CamelCaseString
 		 */
		protected $name;
		protected $type;
		protected $attributes;

		/**
 		 * @var Primitives\CamelCaseString
 		 */
		protected $source;

		public function __construct(
				Model &$model,
				$name,
				$type,
				$source,
				$attributes
		) {
				$this->model = $model;
				$this->name = new Primitives\CamelCaseString($name);
				$this->setType($type);
				$this->attributes = $attributes;
				$this->source = new Primitives\CamelCaseString($source);
		}

		protected function setType($type) {
				$type = trim($type);
				$pattern = '/^(' . self::Types . ')$/';
				if (preg_match($pattern, $type) !== 1) {
						throw new InvalidRelationTypeException($this->model, $this->name, $type);
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

		public function getSource() {
				return $this->source;
		}

		public function getTitle() {
				return (string)$this->name;
		}


}

?>