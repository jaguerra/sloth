<?php

namespace Icti\Sloth\Domain\Model;

class Base extends \TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject {

		/**
 		 * @var \Icti\Sloth\MetaModel\Factory
 		 * @inject
 		 */
		protected $metaModelFactory;

		/**
	 	 * Dispatches magic methods ((get|set)[Property]())
	 	 *
	 	 * @param string $methodName The name of the magic method
	 	 * @param string $arguments The arguments of the magic method
	 	 * @return mixed
	 	 * @api
	 	 */
		public function __call($methodName, $arguments) {
				if (substr($methodName, 0, 3) === 'get' && strlen($methodName) > 4) {
						$propertyName = lcfirst(substr($methodName, 3));
						return $this->_getProperty($propertyName);
				} elseif (substr($methodName, 0, 3) === 'set' && strlen($methodName) > 4) {
						$propertyName = lcfirst(substr($methodName, 3));
						return $this->_setProperty($propertyName, $arguments[0]);
				}

				throw new \Exception('The method "' . $methodName . '" is not supported by the model.', 1233180485);
		}

		/**
 		 *
 		 */
		public function _getProperty($propertyName) {

				if (preg_match('/^_/', $propertyName)) {
						return parent::_getProperty($propertyName);
				}

				$metaField = $this->metaModelFactory->get(get_class($this))->getFieldByName($propertyName);
				if ($metaField instanceof \Icti\Sloth\MetaModel\Field) {
						if ($metaField->getType() == 'Images') {
								$value = parent::_getProperty($propertyName);
								if ($value) {
										return new \Tx_Ictiextbase_Domain_Model_CsvMediaItem($value, 'uploads/tx_itemas/');
								} else {
										return FALSE;
								}
						}
				}
				return parent::_getProperty($propertyName);
		}

		/**
 		 *
 		 */
		public function getClassName() {
				return get_class($this);
		}

}

?>