<?php

namespace Icti\Sloth\MetaModel;
use Icti\Sloth\Primitives;

class Relation extends BaseProperty {

		const Types = 'HasOne|HasMany|HasAndBelongsToMany|BelongsTo';

		/**
 		 * @var string
 		 */
		protected $type;

		/**
 		 * @var Primitives\CamelCaseString
 		 */
		protected $source;

		/**
 		 * @var Primitives\CamelCaseString
 		 */
		protected $inverseOf;


		/**
 		 * @var \Icti\Sloth\MetaModel\Factory
 		 * @inject
 		 */
		protected $factory;


		public function __construct(
				Model &$model,
				$name,
				$type,
				$source,
				$attributes
		) {
				parent::__construct($model, $name, $attributes);
				$this->inverseOf = new Primitives\CamelCaseString($this->getAttribute('sloth\inverseOf'));
				$this->setType($type);
				$this->source = new Primitives\CamelCaseString($source);
		}

		protected function setType($type) {
				$type = trim($type);
				$pattern = '/^(' . self::Types . ')$/';
				if (preg_match($pattern, $type) !== 1) {
						throw new InvalidRelationTypeException($this->model, $this->name, $type);
				}

				if ($this->isInverseOf() && $type == 'HasOne') {
						throw new InvalidRelationTypeException($this->model, $this->name, 'inverseOf '.$type);
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

		/**
 		 * @return boolean
 		 */
		public function isInverseOf() {
				return $this->isAttributeSet('sloth\inverseOf');
		}

		/**
		 * @return
		 */
		public function getInverseOf() {
				return $this->inverseOf;
		}

		/**
 		 *
 		 */
		public function getSourceModel() {
				$className = (string)$this->getSource();
				return $this->factory->get($className);
		}

}

?>