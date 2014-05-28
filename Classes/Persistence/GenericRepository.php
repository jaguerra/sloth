<?php

namespace Icti\Sloth\Persistence;

class GenericRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

		/**
 		 *
 		 */
		public function setModelClassName($modelClassName) {
				$this->objectType = $modelClassName;
		}
}

?>