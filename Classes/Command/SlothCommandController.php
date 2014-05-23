<?php
namespace Icti\Sloth\Command;

class SlothCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

		/**
 		 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
 		 * @inject
 		 **/
		protected $objectManager;


		/**
 		 * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
 		 * @inject
 		 **/
		protected $reflectionService;

		public function testCommand() {
				error_reporting(E_ALL);
				ini_set('display_errors', 'STDERR');

				$this->outputLine('TEST OK');

				$models = array();
				foreach($this->getClassesList() as $modelClass) {
						$builder = $this->objectManager->get('Icti\\Sloth\\MetaModel\\Builder');
						$models[] = $builder->get($modelClass);
				}

				$this->generateSQLSchema($models);
				$this->generateTCA($models);

		}

		protected function generateSQLSchema($models) {
				$view = $this->objectManager->get('Icti\\Sloth\\View\\Php');
				$fileData = $view->render($this->getTemplatePath('ext_tables.sql.php'), $models);
				file_put_contents( $this->getGeneratedFilePath('ext_tables.sql'), $fileData);
				$this->outputLine('Generated ext_tables.sql');
		}

		protected function generateTCA($models) {
				foreach ($models as $model) {
						$builder = new \Icti\Sloth\Tca\Builder($model);
						var_dump( $builder->get() );
				}
		}

		protected function getTemplatePath($fileName) {
				return 'typo3conf/ext/sloth/Resources/Private/Templates/' . $fileName;
		}

		protected function getGeneratedFilePath($fileName) {
				return 'typo3conf/ext/' . $this->getExtensionKey() . '/' . $fileName;
		}

		protected function getClassesList() {
				return array(
						'\\Icti\\Itemas\\Domain\\Model\\Estado'
				);
		}

		protected function getExtensionKey() {
				return 'itemas';
		}

}

?>