<?php
namespace Icti\Sloth\View;

/**
 * Simple PHP templating support
 */

class Php {

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
				$tokens = preg_split('/\\\\+/', $className);
				$tokens = array_filter($tokens, 'trim');
				array_shift($tokens);

				$tokens = array_map( function ($x) {
						return $this->camelCaseToUnderscore($x);
				}, $tokens);
				return 'tx_' . implode('_', $tokens);
		}

		protected function camelCaseToUnderscore($origin) {
				$origin = lcfirst($origin);
				$origin = preg_replace('/([A-Z]{1})/', '_$1', $origin);
				return strtolower($origin);
		}

		protected function getMMTableName($relation) {
				$leftClassName = $relation->getModel()->getModelClassName();
				return 'tx_' . $this->getPluginSegmentFromClassName($leftClassName) . '_' .
						$this->getTableNameSegmentFromClassName($leftClassName) . '_' .
						$this->camelCaseToUnderscore($relation->getName()) . '_' .
						$this->getTableNameSegmentFromClassName($relation->getSource());
		}

		/**
 		 * Input: Some\\Plugin\\Namespace\\ClassName
 		 * Output: plugin
 		 */
		protected function getPluginSegmentFromClassName($className) {
				$tokens = preg_split('/\\\\+/', $className);
				$tokens = array_filter($tokens, 'trim');
				array_shift($tokens);
				$segment = array_shift($tokens);
				return $this->camelCaseToUnderscore($segment);
		}

		/**
 		 * Input: Some\\Plugin\\Namespace\\ClassName or Tx_Plugin_Namespace_ClassName
 		 * Output: class_name
 		 */
		protected function getTableNameSegmentFromClassName($className) {
				$tokens = preg_split('/[\\\\_]+/', $className);
				$tokens = array_filter($tokens, 'trim');
				$segment = array_pop($tokens);
				return $this->camelCaseToUnderscore($segment);
		}
}

?>