<?php
namespace Icti\Sloth\MetaModel;

/**
 * Builds meta Model information from model classes
 */
class Builder {

		/**
 		 * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
 		 * @inject
 		 **/
		protected $reflectionService;

		/**
 	 	 * @return Model
 	 	 */
		public function get($className) {

				$classReflection = new \TYPO3\CMS\Extbase\Reflection\ClassReflection($className);
				$tags = $classReflection->getTagsValues();

				$model = new Model($className, $tags);

				$properties = $this->reflectionService->getClassPropertyNames($className);
				foreach ($properties as $property) {
						if ($this->reflectionService->isPropertyTaggedWith($className, $property, 'sloth\field')) {
							$this->processField($model, $property);
						} else if ($this->reflectionService->isPropertyTaggedWith($className, $property, 'sloth\relation')) {
							$this->processRelation($model, $property);
						}
				}

				return $model;
		}

		public function processField(&$model, $name) {
				$field = new Field(
						$model,
						$name,
						$this->getFieldType($model->getModelClassName(), $name),
						$this->getPropertyAttributes($model->getModelClassName(), $name)
				);
				$model->addField($field);
		}

		public function processRelation(&$model, $name) {
				$relation = new Relation(
						$model,
						$name,
						$this->getRelationType($model->getModelClassName(), $name),
						$this->getRelationSource($model->getModelClassName(), $name),
						$this->getPropertyAttributes($model->getModelClassName(), $name)
				);
				$model->addRelation($relation);
		}

		protected function getPropertyAttributes($className, $property) {
				return $this->reflectionService->getPropertyTagsValues((string)$className, (string)$property);

		}

		protected function getFieldType($className, $property) {
				$tags = $this->reflectionService->getPropertyTagsValues((string)$className, (string)$property);
				$type = $tags['var'][0];
				if (isset($tags['sloth\type'][0])) {
						return $tags['sloth\type'][0];
				} else {
						switch($type) {
						case 'string':
								return 'String';
						case 'integer':
								return 'Integer';
						case 'boolean':
								return 'Check';
						}
						return 'DUMMY';
				}
		}

		protected function getRelationType($className, $property) {
				$tags = $this->reflectionService->getPropertyTagsValues((string)$className, (string)$property);
				$type = $tags['var'][0];
				$subType = isset($tags['sloth\type'])?$tags['sloth\type'][0]:FALSE;
				if ($subType) {
						return $subType;
				} else {
						if(preg_match('/ObjectStorage<[\w\\\\]+>/', $type) === 1) {
								return 'HasAndBelongsToMany';	
						} else {
								return 'HasOne';
						}
				}
		}

		protected function getRelationSource($className, $property) {

				$vars = $this->reflectionService->getPropertyTagValues((string)$className, (string)$property, 'var');
				$varValue = array_shift($vars);
				$matches = array();
				if(preg_match('/ObjectStorage<([\w\\\\]+)>/', $varValue, $matches) === 1) {
						return $matches[1];	
				} else {
						return $varValue;
				}
		}

}

?>