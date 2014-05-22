<?php

namespace Icti\Sloth\MetaModel;

class Model {

		/**
 		 *
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

		public function __construct($modelClassName) {
				$this->modelClassName = $modelClassName;
				$this->fields = array();
				$this->relations = array();
		}

		public function addField(Field $field) {
				$this->fields[] = $field;
		}

		public function addRelation(Relation $relation) {
				$this->relations[] = $relation;
		}

		public function getFields() {
				return $this->fields;
		}

		public function getRelations() {
				return $this->relations;
		}

		public function getModelClassName() {
				return $this->modelClassName;
		}

}


?>