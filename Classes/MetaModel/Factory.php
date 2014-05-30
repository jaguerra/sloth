<?php

namespace Icti\Sloth\MetaModel;

class Factory implements \TYPO3\CMS\Core\SingletonInterface {

		/**
 		 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
 		 * @inject
 		 **/
		protected $objectManager;

		/**
 		 * @var array<Model>
 		 */
		protected $models;

		/**
 		 *
 		 */
		public function get($className) {
				if (isset($this->models[$className])) {
						return $this->models[$className];
				} else {
						$builder = $this->objectManager->get('Icti\\Sloth\\MetaModel\\Builder');
						$this->models[$className] = $builder->get($className);
						return $this->models[$className];
				}
		}

}

?>