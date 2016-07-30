<?php

namespace mplescano\yii\appendgrid\widgets;

use yii\validators\Validator;

class NoEmptyRowsValidator extends Validator
{
	
	public $itemClassName;
	
	public $requiredRows = false;
	
	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object, $attribute)
	{
		$arrValue = $object->$attribute;
		
		$label = $object->getAttributeLabel($attribute);
		
		if ($arrValue != null && is_array($arrValue) && count($arrValue) > 0) {
			//TODO por cada item, ver si es vacio o nulo
			//pueda ser que un campo sea vacio o nulo, pero lo 
			//que se quiere validar aqui es que todos los campos de una fila no deben estar vacios..
			$index = 0;
			foreach($arrValue as $rowItem) {
				if (is_object($rowItem)) {
					$arrCols = get_object_vars($rowItem);
					$numCols = count($arrCols);
					$numEmptyCols = 0;
					foreach ($arrCols as $key => $colItem) {
						if ($this->isEmpty($colItem)) {
							$numEmptyCols++;
						}
					}
					if ($numCols == $numEmptyCols) {
						$this->addError($object, $attribute, 'Para la grilla ' . $label . ', la fila ' . ($index + 1) . ' tiene todo sus campos vacios, eliminelo o ingrese un valor.');
					}
				}
				else if (is_array($rowItem)) {
					if ($this->itemClassName != null) {
						$arrCols = $rowItem[AppendGridWidget::modelName($this->itemClassName)];
					}
					else {
						$arrCols = $rowItem;
					}
					$numCols = count($arrCols);
					$numEmptyCols = 0;
					foreach ($arrCols as $colItem) {
						if ($this->isEmpty($colItem)) {
							$numEmptyCols++;
						}
					}
					if ($numCols == $numEmptyCols) {
						$this->addError($object, $attribute, 'Para la grilla ' . $label . ', la fila ' . ($index + 1) . ' tiene todo sus campos vacios, eliminelo o ingrese un valor.');
					}
				}
				else if ($this->isEmpty($rowItem)) {
					$this->addError($object, $attribute, 'Para la grilla ' . $label . ', la fila ' . ($index + 1) . ' tiene un valor vacio, eliminelo o ingrese un valor.');
				}
				$index++;
			}
		}
		else if ($this->requiredRows) {
			$this->addError($object, $attribute, 'La grilla ' . $label . ' debe de tener al menos una fila ingresada ó modificada, verifique.');
		}
		
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 * Do not override this method if the validator does not support client-side validation.
	 * Two predefined JavaScript variables can be used:
	 * <ul>
	 * <li>value: the value to be validated</li>
	 * <li>messages: an array used to hold the validation error messages for the value</li>
	 * </ul>
	 * @param CModel $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script. Null if the validator does not support client-side validation.
	 * @see CActiveForm::enableClientValidation
	 * @since 1.1.7
	 */
	public function clientValidateAttribute($object,$attribute,$view)
	{
		//TODO...
	}
	
}