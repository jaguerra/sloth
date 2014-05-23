<?php

namespace Icti\Sloth\Tca;

use Icti\Sloth\MetaModel;

class Builder {

		protected $model;
		protected $tca;

		public function __construct(MetaModel\Model $model) {
				$this->model = $model;
				$this->tca = array();
		}

		/**
 		 * Generates TCA array for model
 		 * @return array
 		 */
		public function get() {

				$this->init();
				$this->buildColumns();

				return $this->tca;
		}

		protected function buildColumns() {
				foreach ($this->model->getOrderedFields() as $field) {
						$typeName = (get_class($field) === 'Icti\\Sloth\\MetaModel\\Field')?'Field':'Relation';
						$func = 'buildColumn' . $typeName . $field->getType();
						$column = $this->$func($field);
						$this->tca['columns'][(string)$field->getName()->toUnderscore()] = $column;
				}
		}

		protected function buildBaseColumn($field) {
				return array(
						'exclude' => 1,
						'label' => $field->getTitle(),
						'config' => array()
				);
		}

		protected function buildColumnRelationHAsMany($field) {
				$column = $this->buildBaseColumn($field);
				return $column;
		}

		protected function buildColumnFieldRTE($field) {
				$column = $this->buildBaseColumn($field);
				$column['config'] = array(
						'type' => 'text',
						'cols' => 40,
						'rows' => 15,
						'eval' => 'trim',
						'wizards' => array(
								'RTE' => array(
										'icon' => 'wizard_rte2.gif',
										'notNewRecords'=> 1,
										'RTEonly' => 1,
										'script' => 'wizard_rte.php',
										'title' => 'LLL:EXT:cms/locallang_ttc.xml:bodytext.W.RTE',
										'type' => 'script'
								)
						)

				);
				return $column;
		}



		protected function getSearchFields() {
				return (string)$this->model->getLabelField()->getName()->toUnderscore();
		}

		protected function getShowItems() {
				$fields = array();
				foreach($this->model->getOrderedFields() as $orderedField) {
						$fields[] = $orderedField->getName()->toUnderscore();
				}
				return implode(',', $fields);
		}

		protected function getTableName() {
				return (string)$this->model->getModelClassName()->getTableName();
		}

		protected function init() {
				$this->tca = array(
						'ctrl' => array(
								'title'	=> $this->model->getTitle(),
								'label' => (string)$this->model->getLabelField()->getName()->toUnderscore(),
								'tstamp' => 'tstamp',
								'crdate' => 'crdate',
								'cruser_id' => 'cruser_id',
								'dividers2tabs' => TRUE,
								'versioningWS' => 2,
								'versioning_followPages' => TRUE,
								'origUid' => 't3_origuid',
								'languageField' => 'sys_language_uid',
								'transOrigPointerField' => 'l10n_parent',
								'transOrigDiffSourceField' => 'l10n_diffsource',
								'delete' => 'deleted',
								'searchFields' => $this->getSearchFields(),
								'enablecolumns' => array(
										'disabled' => 'hidden',
										'starttime' => 'starttime',
										'endtime' => 'endtime',
								),
								'iconfile' => \t3lib_extMgm::extRelPath('sloth') . 'Resources/Public/Icons/domain_model.gif'
						),
						'types' => array(
								'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, ' . $this->getShowItems() . ',--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime')
						),
						'palettes' => array(
								'1' => array('showitem' => ''),
						),
						'columns' => array(
								'sys_language_uid' => array(
										'exclude' => 1,
										'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
										'config' => array(
												'type' => 'select',
												'foreign_table' => 'sys_language',
												'foreign_table_where' => 'ORDER BY sys_language.title',
												'items' => array(
														array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
														array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
												),
										),
								),
								'l10n_parent' => array(
										'displayCond' => 'FIELD:sys_language_uid:>:0',
										'exclude' => 1,
										'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
										'config' => array(
												'type' => 'select',
												'items' => array(
														array('', 0),
												),
												'foreign_table' => $this->getTableName(),
												'foreign_table_where' => 'AND ' . $this->getTableName(). '.pid=###CURRENT_PID### AND ' . $this->getTableName(). '.sys_language_uid IN (-1,0)',
										),
								),
								'l10n_diffsource' => array(
										'config' => array(
												'type' => 'passthrough',
										),
								),
								't3ver_label' => array(
										'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
										'config' => array(
												'type' => 'input',
												'size' => 30,
												'max' => 255,
										)
								),
								'hidden' => array(
										'exclude' => 1,
										'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
										'config' => array(
												'type' => 'check',
										),
								),
								'starttime' => array(
										'exclude' => 1,
										'l10n_mode' => 'mergeIfNotBlank',
										'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
										'config' => array(
												'type' => 'input',
												'size' => 13,
												'max' => 20,
												'eval' => 'datetime',
												'checkbox' => 0,
												'default' => 0,
												'range' => array(
														'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
												),
										),
								),
								'endtime' => array(
										'exclude' => 1,
										'l10n_mode' => 'mergeIfNotBlank',
										'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
										'config' => array(
												'type' => 'input',
												'size' => 13,
												'max' => 20,
												'eval' => 'datetime',
												'checkbox' => 0,
												'default' => 0,
												'range' => array(
														'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
												),
										),
								),
						)

				);

		}
}

?>