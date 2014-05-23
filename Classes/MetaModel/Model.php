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

		public function __construct($modelClassName) {
				$this->modelClassName = new Primitives\CamelCaseString($modelClassName);
				$this->fields = array();
				$this->relations = array();
				$this->orderedFields = array();
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
				return $this->modelClassName->getLastSegment();
		}

		public function getLabelField() {
				return $this->orderedFields[0];
		}

}


?>