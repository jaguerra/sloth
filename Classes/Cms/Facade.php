<?php

namespace Icti\Sloth\Cms;

class Facade {

		/**
 		 * @var TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
 		 * @inject
 		 */
		protected $dataMapper;

		/**
 		 * Input: Some\\Plugin\\Namespace\\ClassName
 		 * Output: tx_plugin_namespace_class_name
 		 */
		public function getTableNameFromClassName($className) {

				return $this->dataMapper->convertClassNameToTableName((string)$className);

				/*
				$tokens = preg_split('/\\\\+/', $className);
				$tokens = array_filter($tokens, 'trim');
				array_shift($tokens);

				if (count($tokens) > 0) {
						$tokens = array_map( function ($x) {
								$className = new \Icti\Sloth\Primitives\CamelCaseString($x);
								return (string)$className->toUnderscore();
						}, $tokens);
						return 'tx_' . implode('_', $tokens);
				} else {
						return $this->dataMapper->convertClassNameToTableName((string)$className);
				}
				 */
		}

		/**
 		 *
 		 */
		public function getFieldNameFromPropertyName($propertyName) {
				$property = new \Icti\Sloth\Primitives\CamelCaseString($propertyName);
				return (string)$property->toUnderscore();
		}

		/**
 		 *
 		 */
		public function getMMTableName(\Icti\Sloth\MetaModel\Relation $relation) {
				$leftClassName = $relation->getModel()->getModelClassName();
				return 'tx_' . $this->getPluginSegmentFromClassName($leftClassName) . '_' .
						$this->getTableNameSegmentFromClassName($leftClassName) . '_' .
						$relation->getName()->toUnderscore();
		}

		/**
 		 *
 		 */
		public function getInverseMMTableName(\Icti\Sloth\MetaModel\Relation $relation) {
				$leftClassName = $relation->getSource();
				return 'tx_' . $this->getPluginSegmentFromClassName($leftClassName) . '_' .
						$this->getTableNameSegmentFromClassName($leftClassName) . '_' .
						$relation->getInverseOf()->toUnderscore();
		}
		

		/**
 		 * Input: Some\\Plugin\\Namespace\\ClassName
 		 * Output: plugin
 		 */
		public function getPluginSegmentFromClassName($className) {
				$tokens = preg_split('/\\\\+/', $className);
				$tokens = array_filter($tokens, 'trim');
				array_shift($tokens);
				$segment = new \Icti\Sloth\Primitives\CamelCaseString(array_shift($tokens));
				return $segment->toUnderscore();
		}

		/**
 		 * Input: Some\\Plugin\\Namespace\\ClassName or Tx_Plugin_Namespace_ClassName
 		 * Output: class_name
 		 */
		public function getTableNameSegmentFromClassName($className) {
				$tokens = preg_split('/[\\\\_]+/', $className);
				$tokens = array_filter($tokens, 'trim');
				$segment = new \Icti\Sloth\Primitives\CamelCaseString(array_pop($tokens));
				return $segment->toUnderscore();
		}

		/**
 		 *
 		 */
		public function getTcaTableOrderField($tableName) {
				global $TCA;

				if ($TCA[$tableName]['ctrl']['sortby']) {
						return $TCA[$tableName]['ctrl']['sortby'];
				} elseif ($TCA[$tableName]['ctrl']['default_sortby']) {
						$matches = array();
						if (preg_match('/(?:order\s+by)? (?:[a-z_]+\.)?([a-z_]+){1}/i', $TCA[$tableName]['ctrl']['default_sortby'], $matches) === 1) {
								return $matches[1];
						} else {
								return 'uid';
						}
				} else {
						return 'uid';
				}
		}

		/**
 		 *
 		 */
		public function getInlineRelationForeignFieldName(\Icti\Sloth\MetaModel\Relation $relation) {
			return $this->getTableNameFromClassName($relation->getModel()->getModelClassName()) . '_' . $relation->getName()->toUnderscore();
		}


}

?>