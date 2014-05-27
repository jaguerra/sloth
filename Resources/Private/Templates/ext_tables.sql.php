# -- Generated by EXT:sloth

<?php
	foreach($v as $model) {
?>
# --
CREATE TABLE <?php echo $this->getTableNameFromClassName($model->getModelClassName()); ?> (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

<?php 
			foreach($model->getFields() as $field) { 
					$fieldName = $this->camelCaseToUnderscore( $field->getName() );
					$fieldStart = "\t";
					switch($field->getType()) {
					case 'String':
							echo $fieldStart . $fieldName . ' varchar(255) DEFAULT \'\' NOT NULL,' . "\n";
							break;
					case 'Text':
				  case 'RTE':
					case 'Files':
					case 'Images':
							echo $fieldStart . $fieldName . ' text NOT NULL,' . "\n";
							break;
					case 'Integer':
							echo $fieldStart . $fieldName . ' int(11) DEFAULT \'0\' NOT NULL,' . "\n";
							break;
					case 'Check':
							echo $fieldStart . $fieldName . ' tinyint(1) unsigned DEFAULT \'0\' NOT NULL,' . "\n";
							break;
					}
			}

			foreach($model->getRelations() as $field) { 
					$fieldName = $this->camelCaseToUnderscore( $field->getName() );
					$fieldStart = "\t";
					echo $fieldStart . $fieldName . ' int(11) unsigned DEFAULT \'0\' NOT NULL,' . "\n";
			}
?>

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	t3ver_oid int(11) DEFAULT '0' NOT NULL,
	t3ver_id int(11) DEFAULT '0' NOT NULL,
	t3ver_wsid int(11) DEFAULT '0' NOT NULL,
	t3ver_label varchar(255) DEFAULT '' NOT NULL,
	t3ver_state tinyint(4) DEFAULT '0' NOT NULL,
	t3ver_stage int(11) DEFAULT '0' NOT NULL,
	t3ver_count int(11) DEFAULT '0' NOT NULL,
	t3ver_tstamp int(11) DEFAULT '0' NOT NULL,
	t3ver_move_id int(11) DEFAULT '0' NOT NULL,

	t3_origuid int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY t3ver_oid (t3ver_oid,t3ver_wsid),
	KEY language (l10n_parent,sys_language_uid)

);

<?php
			foreach($model->getRelations() as $relation) {
					if ($relation->getType() === 'HasAndBelongsToMany') {
							$relationTableName = $this->getMMTableName($relation);
?>

# --
CREATE TABLE <?php echo $relationTableName; ?> (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


<?php
					}
			}
?>

<?php
			foreach($model->getRelations() as $relation) {
					if ($relation->getType() === 'HasMany') {
							$relationTableName = $this->getTableNameFromClassName($relation->getSource());
							$relationTableField = $this->cmsFacade->getInlineRelationForeignFieldName($relation);
?>

# --
CREATE TABLE <?php echo $relationTableName; ?> (
	<?php echo $relationTableField; ?> int(11) unsigned DEFAULT '0' NOT NULL
);


<?php
					}
			}
?>

<?php
	}
?>