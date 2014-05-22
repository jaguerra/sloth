<?php

namespace Icti\Sloth\MetaModel;

class InvalidFieldTypeException extends \ErrorException {
		public function __construct(Model $model, $name, $type) {
				$modelClassName = $model->getModelClassName();
				$msg = 'Invalid type "' . $type . '" found in ' . $modelClassName . '::' . $name . "\n";
				$msg .= 'Valid types: ' . Field::Types;

				parent::__construct($msg);
		}
}


?>