<?php
namespace Icti\Sloth\View;

/**
 * Simple PHP templating support
 */

class Php {

		/**
 		 * @var Icti\Sloth\Cms\Facade
 		 * @inject
 		 */
		protected $cmsFacade;

		protected $templateFile;

		public function render($templateFile, $vars) {
				$this->templateFile = $templateFile;
				return $this->sandbox($vars);
		}

		protected function sandbox($v) {
				ob_start();
				require($this->templateFile);
				return ob_get_clean();
		}

		/**
 		 * Input: Some\\Plugin\\Namespace\\ClassName
 		 * Output: tx_plugin_namespace_class_name
 		 */
		protected function getTableNameFromClassName($className) {
				return $this->cmsFacade->getTableNameFromClassName($className);
		}

		protected function camelCaseToUnderscore($origin) {
				$origin = lcfirst($origin);
				$origin = preg_replace('/([A-Z]{1})/', '_$1', $origin);
				return strtolower($origin);
		}

		protected function getMMTableName($relation) {
				return $this->cmsFacade->getMMTableName($relation);
		}

		/**
 		 * Input: Some\\Plugin\\Namespace\\ClassName
 		 * Output: plugin
 		 */
		protected function getPluginSegmentFromClassName($className) {
				return $this->cmsFacade->getPluginSegmentFromClassName($className);
		}

		/**
 		 * Input: Some\\Plugin\\Namespace\\ClassName or Tx_Plugin_Namespace_ClassName
 		 * Output: class_name
 		 */
		protected function getTableNameSegmentFromClassName($className) {
				return $this->cmsFacade->getTableNameSegmentFromClassName($className);
		}
}

?>