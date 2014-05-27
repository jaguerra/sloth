<?php

namespace Icti\Sloth\MetaModel;
use Icti\Sloth\Primitives;

class Relation extends BaseProperty {

		const Types = 'HasOne|HasMany|HasAndBelongsToMany';

		/**
 		 * @var string
 		 */
		protected $type;

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
				parent::__construct($model, $name, $attributes);
				$this->setType($type);
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

		public function getType () {
				return $this->type;
		}

		/**
		 * Gets the value of source
		 *
		 * @return
		 */
		public function getSource() {
				return $this->source;
		}

}

?>