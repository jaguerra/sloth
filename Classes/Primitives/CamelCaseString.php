<?php

namespace Icti\Sloth\Primitives;

class CamelCaseString extends String {

		public function toUnderscore() {
				return new UnderscoreString($this->camelCaseToUnderscore($this->value));
		}

		/**
 		 * Input: Some\\Plugin\\Namespace\\ClassName or Tx_Plugin_Namespace_ClassName
 		 * Output: ClassName
 		 */
		public function getLastSegment() {
				$tokens = preg_split('/[\\\\_]+/', $this->value);
				$tokens = array_filter($tokens, 'trim');
				$segment = array_pop($tokens);
				return $segment;
		}


		/**
 		 * Input: Some\\Plugin\\Namespace\\ClassName
 		 * Output: tx_plugin_namespace_class_name
 		 */
		public function getTableName() {
				$tokens = preg_split('/\\\\+/', $this->value);
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


}



?>