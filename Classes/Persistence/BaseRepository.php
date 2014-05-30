<?php

namespace Icti\Sloth\Persistence;

class BaseRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

		/**
 		 * @var \Icti\Sloth\MetaModel\Factory
 		 * @inject
 		 */
		protected $metaModelFactory;

		/**
 	 	 * @param array Filters
 	 	 */
		public function findFiltered($filters = array()) {
				$query = $this->createQuery();
				$constraintArray = array();

				foreach ($filters as $filterName => $filterValue) {
						$constraint = $this->getConstraint($query, $filterName, $filterValue);
						if ($contraint !== FALSE) {
								$constraintArray[] = $constraint;
						}
				}

				if (count($constraintArray) > 0) {
						$query->matching($query->logicalAnd($constraintArray));
				}

				return $query->execute();
		}

		/**
 		 *
 		 */
		protected function getConstraint(&$query, $name, $value) {

				$methodName = 'getConstraintFor'.ucfirst($name);
				if (method_exists(get_class($this), $methodName)) {
						return $this->$methodName($query, $name, $value);
				}

				$metaField = $this->metaModelFactory->get($this->objectType)->getFieldByName($name);

				if ($metaField instanceof \Icti\Sloth\MetaModel\Field) {
						if ($metaField->getType() == 'Text' || $metaField->getType() == 'RTE') {
								return $query->like($name, '%' . $value . '%');
						} else if ($metaField->getType() == 'Check') {
								$boolValue = TRUE;
								if ($value === FALSE || $value === '' || $value === '0') {
										$boolValue = FALSE;
								}
								return $query->equals($name, $boolValue);
						} else {
								return $query->equals($name, $value);
						}
				} else {
						if ($metaField->getType() == 'HasOne') {
								return $query->equals($name, $value);
						} else {
								return $query->contains($name, $value);
						}
				}

				return FALSE;

		}
}

?>