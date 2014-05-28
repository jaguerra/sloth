<?php

namespace Icti\Sloth\Domain\Model;

class Base extends \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject {

		/**
 		 * TEMPORAL
 		 */
		public function setName($value) {
				$this->name = $value;
		}

}

?>