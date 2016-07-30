<?php

namespace mplescano\yii\appendgrid\widgets;

use yii\validators\Validator;

/**
 *
 * TODO hacer que sea multicolumna, ver impacto en los demas, modificar acordemente
 * @author mlescano
 *
 */
class UniqueColumnValueValidator extends Validator
{
	
    public $columns = array();
    
    public $itemClassName;
	
	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	public function validateAttribute($object,$attribute)
	{
		$arrValue = $object->$attribute;
		
		$label = $object->getAttributeLabel($attribute);
		
		$arrUniqueValues = array();
		
		if ($arrValue != null && is_array($arrValue) && count($arrValue) > 0) {
			//por cada item, ver si es repetido
			//lo que se quiere validar aqui es que para un array de columnas, debe de tener valores combinados unicos
			$index = 0;
			foreach($arrValue as $rowItem) {
				if (is_object($rowItem)) {
					//error_log('is object::');
					$arrCols = get_object_vars($rowItem);
					$valCol = '';
					for ($indCol = 0; $indCol < count($this->columns); $indCol++) {
						if (isset($arrCols[$this->columns[$indCol]])) {
							$valCol .= $arrCols[$this->columns[$indCol]];
							if ($indCol != (count($this->columns) - 1)) {
								$valCol .= '::';
							}
						}
					}
				}
				else if (is_array($rowItem)) {
					//error_log('is array::' . print_r($rowItem, true));
					if ($this->itemClassName != null && isset($rowItem[$this->itemClassName])) {
						$valCol = '';
						for ($indCol = 0; $indCol < count($this->columns); $indCol++) {
							if (isset($rowItem[$this->itemClassName][$this->columns[$indCol]])) {
								$valCol .= $rowItem[$this->itemClassName][$this->columns[$indCol]];
								if ($indCol != (count($this->columns) - 1)) {
									$valCol .= '::';
								}
							}
						}
					}
					else {
						$valCol = '';
						for ($indCol = 0; $indCol < count($this->columns); $indCol++) {
							if (isset($rowItem[$this->columns[$indCol]])) {
								$valCol .= $rowItem[$this->columns[$indCol]];
								if ($indCol != (count($this->columns) - 1)) {
									$valCol .= '::';
								}
							}
						}
					}
				}
				else if (!$this->isEmpty($rowItem) && is_scalar($rowItem)) {
				    $valCol = $rowItem;
				}
				else {
				    continue;
				}
				
				if (array_search($valCol, $arrUniqueValues) !== false) {
				    $this->addError($object, $attribute, 'Para la grilla ' . $label . ' en la fila ' . ($index + 1) . ' tiene un valor o valores que se repite con uno anterior, eliminelo o ingrese un valor diferente.');
				}
				else {
				    $arrUniqueValues[] = $valCol;
				}
				$index++;
			}
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