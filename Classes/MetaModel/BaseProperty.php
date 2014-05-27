<?php

namespace Icti\Sloth\MetaModel;
use Icti\Sloth\Primitives;

abstract class BaseProperty {

		/**
 		 * @var Model
 		 */
		protected $model;

		/**
 		 * @var Primitives\CamelCaseString
 		 */
		protected $name;

		/**
 		 * @var Array
 		 */
		protected $attributes;

		/**
 		 *
 		 */
		public function __construct(
				Model &$model,
				$name,
				$attributes
		) {
				$this->model = $model;
				$this->name = new Primitives\CamelCaseString($name);
				$this->attributes = $attributes;
		}

		/**
 		 *
 		 */
		public function getModel() {
				return $this->model;
		}

		/**
 		 *
 		 */
		public function getName() {
				return $this->name;
		}

		/**
 		 *
 		 */
		public function getAttributes() {
				return $this->attributes;
		}

		/**
 		 *
 		 */
		public function getTitle() {
				if (isset($this->attributes['sloth\title'][0])) {
						return $this->attributes['sloth\title'][0];
				} else {
					return (string)$this->name;
				}
		}

}

?>