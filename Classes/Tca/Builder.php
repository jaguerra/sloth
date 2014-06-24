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
				$this->postProcess();
				return $this->tca;
		}

		protected function postProcess() {
				if ($this->isMethodCallableForClassName($this->model->getModelClassName(), 'postProcessTCA')) {
						$funcName = (string)$this->model->getModelClassName() . '::postProcessTCA';
						$this->tca = call_user_func($funcName, $this->tca);
				}
		}

		protected function isMethodCallableForClassName($className, $method) {
				try {
						$reflection = new \ReflectionMethod((string)$className, $method);
						return ($reflection->isPublic() && $reflection->isStatic());
				} catch (\ReflectionException $e) {
						return FALSE;
				}
		}

		protected function buildColumns() {
				foreach ($this->model->getOrderedFields() as $field) {
						$typeName = (get_class($field) === 'Icti\\Sloth\\MetaModel\\Field')?'Field':'Relation';
						$selectMethodName = 'getSelectValuesFor' . ucfirst($field->getName());
						if ($this->isMethodCallableForClassName($this->model->getModelClassName(), $selectMethodName)) {
								$func = 'buildColumnSelectValues';
						} else {
								$func = 'buildColumn' . $typeName . $field->getType();
						}
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
				$attributes = $field->getAttributes();

				if (isset($attributes['sloth\localAuxValues'])) {
						return  'AND (' . $foreignTable . '.pid = ###CURRENT_PID###
            		or ' . $foreignTable . '.pid = ###STORAGE_PID###
            		or ' . $foreignTable . '.pid IN (###PAGE_TSCONFIG_IDLIST###))
                		ORDER BY ' . $foreignTable . '.' . $orderBy;
				} else {
						return 'ORDER BY ' . $foreignTable . '.' . $orderBy;
				}
		}

		protected function buildColumnRelationBelongsTo($field) {
				$column = $this->buildBaseColumn($field);
				$foreignTable = $this->cmsFacade->getTableNameFromClassName($field->getSource());

				/*
 				 * BelongsTo relations are meant to complement HasMany
 				 * To be used whithin ExtBase. No manual editing on TCA
 				 * shall be needed.
 				 *
 				 * But if you want to use the relation within Extbase queries
 				 * this field must be a proper TCA relation...
 				 *
 				 */
				$column['l10n_mode'] = 'exclude';
				$column['l10n_display'] = 'defaultAsReadonly';
				$column['config'] = array(
						'type' => 'select',
						'foreign_table' => $foreignTable,
						'foreign_table_where' => ' AND ' . $foreignTable . '.pid = ###CURRENT_PID###',
						'size' => 1,
						'maxitems' => 1,
						'multiple' => 0,
						'items' => array(
								array('', 0),
						),
				);
				return $column;
		}

		protected function buildColumnRelationHasOne($field) {
				$column = $this->buildBaseColumn($field);
				$foreignTable = $this->cmsFacade->getTableNameFromClassName($field->getSource());

				$column['l10n_mode'] = 'exclude';
				$column['l10n_display'] = 'defaultAsReadonly';
				$column['config'] = array(
						'type' => 'select',
						'foreign_table' => $foreignTable,
						'foreign_table_where' => $this->buildRelationForeignTableWhere($field),
						'size' => 1,
						'maxitems' => 1,
						'multiple' => 0,
						'items' => array(
								array('', 0),
						),
				);
				return $column;
		}


		protected function buildColumnRelationHasMany($field) {
				/**
 				 * Translations are allowed in-place for the editors
 				 * to manage them within the parent record.
 				 *
 				 * Caveat: This allows "Create new" child elements within
 				 * a parent's translation which is not supported by Extbase.
 				 *
 				 * A safer solution is to disallow translations inline but then
 				 * editors must translate records outside the parent's.
 				 *
 				 * Remember: WE DO NOT SUPPORT TRANSLATED RELATIONS, related 
 				 * records must provide its own translations.
 				 */
				$column = $this->buildBaseColumn($field);
				$foreignTable = $this->cmsFacade->getTableNameFromClassName($field->getSource());

				if ($field->isInverseOf()) {
						$foreignField = (string)$field->getInverseOf()->toUnderscore();
				} else {
						$foreignField = $this->cmsFacade->getInlineRelationForeignFieldName($field);
				}

				$column['config'] = array(
						'type' => 'inline',
						'foreign_table' => $foreignTable,
						'foreign_field' => $foreignField,
						'maxitems'      => 9999,
						'appearance' => array(
								'collapseAll' => 1,
								'enabledControls' => array(
										'localize' => TRUE,
								),
								'levelLinksPosition' => 'top',
								'showSynchronizationLink' => 1,
								'showPossibleLocalizationRecords' => 0,
								'showAllLocalizationLink' => 1
						),
						'behaviour' => array(
								'localizationMode' => 'select'
						)
				);
				return $column;
		}

		protected function buildColumnRelationHasAndBelongsToMany($field) {

				if ($field->isInverseOf()) {
						return $this->buildColumnRelationInverseHasAndBelongsToMany($field);
				}

				$column = $this->buildBaseColumn($field);
				$column['l10n_mode'] = 'exclude';
				$column['l10n_display'] = 'defaultAsReadonly';

				if ($field->isAttributeSet('sloth\group')) {
						$column['config'] = array(
								'type' => 'group',
								'internal_type' => 'db',
								'allowed' => $this->cmsFacade->getTableNameFromClassName($field->getSource()),
								'foreign_table' => $this->cmsFacade->getTableNameFromClassName($field->getSource()),
								'MM' => $this->cmsFacade->getMMTableName($field, $field->getSource()),
								'size' => 10,
								'autoSizeMax' => 30,
								'maxitems' => 9999,
								'multiple' => 0,
						);

				} else {

						$column['config'] = array(
								'type' => 'select',
								'foreign_table' => $this->cmsFacade->getTableNameFromClassName($field->getSource()),
								'foreign_table_where' => $this->buildRelationForeignTableWhere($field),
								'MM' => $this->cmsFacade->getMMTableName($field, $field->getSource()),
								'size' => 10,
								'autoSizeMax' => 30,
								'maxitems' => 9999,
								'multiple' => 0,
						);
				}
				return $column;
		}

		protected function buildColumnRelationInverseHasAndBelongsToMany($field) {
				$column = $this->buildBaseColumn($field);
				$column['l10n_mode'] = 'exclude';
				$column['l10n_display'] = 'defaultAsReadonly';
				$column['config'] = array(
						'type' => 'select',
						'foreign_table' => $this->cmsFacade->getTableNameFromClassName($field->getSource()),
						'foreign_table_where' => $this->buildRelationForeignTableWhere($field),
						'MM' => $this->cmsFacade->getInverseMMTableName($field),
						'MM_opposite_field' => $this->cmsFacade->getFieldNameFromPropertyName( $field->getInverseOf() ),
						'size' => 10,
						'autoSizeMax' => 30,
						'maxitems' => 9999,
						'multiple' => 0,
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
						'maxitems' => 100,
						'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
						'disallowed' => '',
				);
				return $column;
		}

		protected function buildColumnFieldDateTime($field) {
				$column = $this->buildBaseColumn($field);
				$column['config'] = array(
						'type' => 'input',
						'size' => 13,
						'max' => 20,
						'eval' => 'datetime',
						'checkbox' => 0,
						'default' => 0,
				);
				return $column;
		}

		protected function buildColumnSelectValues($field) {
				$column = $this->buildBaseColumn($field);
				$selectFuncName = (string)$this->model->getModelClassName() . '::getSelectValuesFor' . ucfirst($field->getName());

				$column['l10n_mode'] = 'exclude';
				$column['l10n_display'] = 'defaultAsReadonly';
				$column['config'] = array(
						'type' => 'select',
						'size' => 1,
						'maxitems' => 1,
						'multiple' => 0,
						'items' => call_user_func($selectFuncName)
				);
				return $column;
		}



		/**
		 * Gets the value of upload folder 
		 *
		 * @return
		 */
		public function getUploadFolder() {
			return 'uploads/tx_itemas';
		}


		protected function getSearchFields() {
				return (string)$this->model->getLabelField()->getName()->toUnderscore();
		}

		/**
 		 * Ordering tries to be as much implicit as possible
 		 *
 		 * We insert fields into tabs as follows:
 		 * - Use a tab name as the table as default
 		 * - "General" tab always come first
 		 *
 		 * We order fields within tabs based on priority (integer)
 		 * - Default priority is 1000
 		 * - On tie, original order is kept
 		 */
		protected function getShowItems() {
				$fields = array();
				$tabs = array();
				$defaultTab = $this->model->getTitle();
				$defaultPriority = 1000;
				$mainTab = 'General';

				foreach($this->model->getOrderedFields() as $orderedField) {
						if ($orderedField->isAttributeSet('sloth\tab')) {
								$tab = $orderedField->getAttribute('sloth\tab');
						} else {
								$tab = $defaultTab;
						}

						if ($orderedField->isAttributeSet('sloth\priority')) {
								$priority = (int)$orderedField->getAttribute('sloth\priority');
						} else {
								$priority = $defaultPriority;
						}

						$tabs[$tab][$priority][] = $orderedField->getName()->toUnderscore();
				}

				/*
 				 * Put "General" tab on first place
 				 */
				uksort($tabs, function($a, $b) use ($mainTab) {
						if ($a == $mainTab) {
								return -1;
						} else if ($b == $mainTab) {
								return 1;
						} else {
								return 0;
						}
				});

				$showItems = '';
				foreach ($tabs as $tabLabel => $tab) {
						$tabFields = array();
						ksort($tab);
						foreach ($tab as $priority) {
								foreach ($priority as $field) {
										$tabFields[] = $field;
								}
						}

						if ($tabLabel == $mainTab) {
								$showItems .= implode(',', $tabFields) . ',';
						} else {
								$showItems .= '--div--;' . $tabLabel . ',' . implode(',', $tabFields) . ',';
						}
				}
				return $showItems;
		}

		public function getTableName() {
				return (string)$this->cmsFacade->getTableNameFromClassName($this->model->getModelClassName());
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
										'fe_group' => 'fe_group'
								),
								'hideTable' => ($this->model->isAttributeSet('sloth\hideTable')) ? TRUE : FALSE,
								'iconfile' => \t3lib_extMgm::extRelPath('sloth') . 'Resources/Public/Icons/domain_model.gif'
						),
						'types' => array(
								'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, ' . $this->getShowItems() . '--div--;LLL:EXT:cms/locallang_ttc.xml:tabs.access,starttime, endtime, --linebreak--, fe_group')
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
								'fe_group' => array(
										'exclude' => 1,
										'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.fe_group',
										'config' => array(
												'type' => 'select',
												'size' => 5,
												'maxitems' => 20,
												'items' => array(
														array(
																'LLL:EXT:lang/locallang_general.xlf:LGL.hide_at_login',
																-1
														),
														array(
																'LLL:EXT:lang/locallang_general.xlf:LGL.any_login',
																-2
														),
														array(
																'LLL:EXT:lang/locallang_general.xlf:LGL.usergroups',
																'--div--'
														)
												),
												'exclusiveKeys' => '-1,-2',
												'foreign_table' => 'fe_groups',
												'foreign_table_where' => 'ORDER BY fe_groups.title'
										)
								),
						)

				);

		}
}

?>