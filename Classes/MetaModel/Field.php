<?php

namespace Icti\Sloth\MetaModel;
use Icti\Sloth\Primitives;

class Field extends BaseProperty {

		const Types = 'String|Text|RTE|Integer|Check|Files|Images';

		/**
 		 * @var string
 		 */
		protected $type;

		/**
 		 * 
 		 */
		public function __construct(
				Model &$model,
				$name,
				$type,
				$attributes
		) {
				parent::__construct($model, $name, $attributes);
				$this->setType($type);
		}

		/**
 		 *
 		 */
		protected function setType($type) {
				$type = trim($type);
				$pattern = '/^(' . self::Types . ')$/';
				if (preg_match($pattern, $type) !== 1) {
						throw new InvalidFieldTypeException($this->model, $this->name, $type);
				}
				$this->type = $type;
		}

		/**
 		 *
 		 */
		public function getType () {
				return $this->type;
		}

}

?>