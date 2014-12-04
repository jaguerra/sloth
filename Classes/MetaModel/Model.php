<?php

namespace Icti\Sloth\MetaModel;
use Icti\Sloth\Primitives;

class Model {

		/**
 		 * @var CamelCaseString
 		 */
		protected $modelClassName;

		/**
 		 * @var Array<Field> 
 		 */
		protected $fields;

		/**
 		 * @var Array<Relation>
 		 */
		protected $relations;

		/**
 		 * @var Array<Relation|Field>
 		 */
		protected $orderedFields;

		/**
 		 * @var Array
 		 */
		protected $attributes;

		public function __construct($modelClassName, $attributes = array()) {
				$this->modelClassName = new Primitives\CamelCaseString($modelClassName);
				$this->fields = array();
				$this->relations = array();
				$this->orderedFields = array();
				$this->attributes = $attributes;
		}

		public function addField(Field $field) {
				$this->fields[] = $field;
				$this->orderedFields[] = $field;
		}

		public function addRelation(Relation $relation) {
				$this->relations[] = $relation;
				$this->orderedFields[] = $relation;
		}

		public function getFields() {
				return $this->fields;
		}

		public function getRelations() {
				return $this->relations;
		}

		public function getOrderedFields() {
				return $this->orderedFields;
		}

		public function getModelClassName() {
				return $this->modelClassName;
		}

		public function getTitle() {
				if (isset($this->attributes['sloth\title'][0])) {
						return $this->attributes['sloth\title'][0];
				} else {
						return $this->modelClassName->getLastSegment();
				}
		}

		public function getLabelField() {
				if (isset($this->attributes['sloth\label'][0])) {
						$labelField = $this->attributes['sloth\label'][0];
						try {
								return $this->getFieldByName($labelField);
						} catch (Exception $e) {
								//
						}
				}
				return $this->orderedFields[0];
		}

		/**
 		 *
 		 */
		public function isSortable() {
				if ($this->isAttributeSet('sloth\sortable')) {
						return TRUE;
				} else {
						return FALSE;
				}
		}

		/**
 		 *
 		 */
		public function isSortableOnRelations() {
				if ($this->isAttributeSet('sloth\sortableOnRelations')) {
						return TRUE;
				} else {
						return FALSE;
				}
		}


		/**
 		 * @return Field
 		 */
		public function getFieldByName($name) {
				foreach ($this->orderedFields as $field) {
						if ($field->getName() == $name) {
								return $field;
						}
				}
				throw new \Exception('Field "' . $name . '" not found in model "' . $this->modelClassName . '"');
		}

		/**
 		 *
 		 */
		public function getAttributes() {
				return $this->attributes;
		}

		/**
 		 * @return boolean
 		 */
		public function isAttributeSet($attributeName) {
				return isset($this->attributes[$attributeName]);
		}


}


?>