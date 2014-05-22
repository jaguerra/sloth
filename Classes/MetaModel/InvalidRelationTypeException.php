<?php

namespace Icti\Sloth\MetaModel;

class InvalidRelationTypeException extends \ErrorException {
		public function __construct(Model $model, $name, $type) {
				$modelClassName = $model->getModelClassName();
				$msg = 'Invalid type "' . $type . '" found in ' . $modelClassName . '::' . $name . "\n";
				$msg .= 'Valid types: ' . Relation::Types;

				parent::__construct($msg);
		}
}


?>