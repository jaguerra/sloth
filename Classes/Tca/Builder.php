<?php

namespace Icti\Sloth\Tca;

use Icti\Sloth\MetaModel;

class Builder {

		protected $model;
		protected $tca;

		/**
 		 * @var Icti\Sloth\Cms\Facade
 		 * @inject
 		 */
		protected $cmsFacade;

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

		protected function buildRelationForeignTableWhere($field) {
				$foreignTable = $this->cmsFacade->getTableNameFromClassName($field->getSource());
				$orderBy = $this->cmsFacade->getTcaTableOrderField($foreignTable);
				return  'AND (' . $foreignTable . '.pid = ###CURRENT_PID###
            or ' . $foreignTable . '.pid = ###STORAGE_PID###
            or ' . $foreignTable . '.pid IN (###PAGE_TSCONFIG_IDLIST###))
                ORDER BY ' . $foreignTable . '.' . $orderBy;
		}

		protected function buildColumnRelationHasOne($field) {
				$column = $this->buildBaseColumn($field);
				$foreignTable = $this->cmsFacade->getTableNameFromClassName($field->getSource());
				$column['config'] = array(
						'type' => 'select',
						'foreign_table' => $foreignTable,
						'foreign_table_where' => $this->buildRelationForeignTableWhere($field),
						'size' => 1,
						'maxitems' => 1,
						'multiple' => 0,
				);
				return $column;
		}

		protected function buildColumnRelationHasMany($field) {
				$column = $this->buildBaseColumn($field);
				$foreignTable = $this->cmsFacade->getTableNameFromClassName($field->getSource());
				$foreignField = $this->cmsFacade->getInlineRelationForeignFieldName($field);
				$column['config'] = array(
						'type' => 'inline',
						'foreign_table' => $foreignTable,
						'foreign_field' => $foreignField,
						'maxitems'      => 9999,
						'appearance' => array(
								'collapse' => 0,
								'levelLinksPosition' => 'top',
								'showSynchronizationLink' => 1,
								'showPossibleLocalizationRecords' => 1,
								'showAllLocalizationLink' => 1
						),
				);
				return $column;
		}

		protected function buildColumnRelationHasAndBelongsToMany($field) {
				$column = $this->buildBaseColumn($field);
				$column['config'] = array(
						'type' => 'select',
						'foreign_table' => $this->cmsFacade->getTableNameFromClassName($field->getSource()),
						'foreign_table_where' => $this->buildRelationForeignTableWhere($field),
						'MM' => $this->cmsFacade->getMMTableName($field, $field->getSource()),
						'size' => 10,
						'autoSizeMax' => 30,
						'maxitems' => 9999,
						'multiple' => 0,
						'wizards' => array(
								'_PADDING' => 1,
								'_VERTICAL' => 1,
								'edit' => array(
										'type' => 'popup',
										'title' => 'Edit',
										'script' => 'wizard_edit.php',
										'icon' => 'edit2.gif',
										'popup_onlyOpenIfSelected' => 1,
										'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
								),
								'add' => Array(
										'type' => 'script',
										'title' => 'Create new',
										'icon' => 'add.gif',
										'params' => array(
												'table' => $this->cmsFacade->getTableNameFromClassName($field->getSource()),
												'pid' => '###CURRENT_PID###',
												'setValue' => 'prepend'
										),
										'script' => 'wizard_add.php',
								),
						),

				);
				return $column;
		}

		protected function buildColumnFieldString($field) {
				$column = $this->buildBaseColumn($field);
				$column['config'] = array(
						'type' => 'input',
						'size' => 30,
				);
				return $column;
		}

		protected function buildColumnFieldText($field) {
				$column = $this->buildBaseColumn($field);
				$column['config'] = array(
						'type' => 'text',
						'cols' => 40,
						'rows' => 15,
						'eval' => 'trim',
				);
				return $column;
		}

		protected function buildColumnFieldRTE($field) {
				$column = $this->buildBaseColumn($field);
				$column['defaultExtras'] = 'richtext[]';
				$column['config'] = array(
						'type' => 'text',
						'cols' => 40,
						'rows' => 15,
						'eval' => 'trim',
				);
				return $column;
		}

		protected function buildColumnFieldInteger($field) {
				$column = $this->buildBaseColumn($field);
				$column['config'] = array(
						'type' => 'input',
						'size' => 4,
						'eval' => 'int'
				);
				return $column;
		}

		protected function buildColumnFieldCheck($field) {
				$column = $this->buildBaseColumn($field);
				$column['config'] = array(
						'type' => 'check',
						'default' => 0
				);
				return $column;
		}

		protected function buildColumnFieldFiles($field) {
				$column = $this->buildBaseColumn($field);
				$column['config'] = array(
						'type' => 'group',
						'internal_type' => 'file',
						'uploadfolder' => $this->getUploadFolder(),
						'show_thumbs' => 0,
						'size' => 5,
						'allowed' => '',
						'disallowed' => 'php',
				);
				return $column;
		}

		protected function buildColumnFieldImages($field) {
				$column = $this->buildBaseColumn($field);
				$column['config'] = array(
						'type' => 'group',
						'internal_type' => 'file',
						'uploadfolder' => $this->getUploadFolder(),
						'show_thumbs' => 1,
						'size' => 5,
						'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
						'disallowed' => '',
				);
				return $column;
		}

		/**
		 * Gets the value of upload folder 
		 *
		 * @return
		 */
		public function getUploadFolder() {
			return 'uploads/tx_icticontent';
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

		public function getTableName() {
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