<?php

namespace mplescano\yii\appendgrid\widgets;

use yii\base\Model;
use yii\validators\Validator;

class ItemClassValidator extends Validator
{
	
	public $itemClassName;
	
	public $payload = array();
	
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
			
			$index = 0;
			foreach($arrValue as $rowItem) {
				if (is_object($rowItem)) {
					//error_log('is object::');
					//$arrCols = get_object_vars($rowItem);
					//$valCol = $arrCols[$this->column];
					if (get_class($rowItem) == $this->itemClassName &&
						$rowItem instanceof Model) {
						if (is_array($this->payload) && count($this->payload) > 0 &&
								property_exists($rowItem, 'payload')) {
							$arrPropPayload = array();
							foreach ($this->payload as $propPayload) {
								if (property_exists($object, $propPayload)) {
									$arrPropPayload[$propPayload] = $object->{$propPayload};
								}
							}
							
							$rowItem->payload = $arrPropPayload;
						}
						$rowItem->validate();
						if ($rowItem->hasErrors()) {
							foreach($rowItem->getErrors() as $attrCol => $errors) {
								$labelCol = $rowItem->getAttributeLabel($attrCol);
								$this->addError($object, $attribute, 'Para la grilla ' . $label . ' y fila ' . ($index + 1) . ' la columna ' . $labelCol . ' tiene los siguientes errores:' . join(', ', $errors));
							}
						}
					}
				}
				else if (is_array($rowItem)) {
					//error_log('is array::' . print_r($rowItem, true));
					if ($this->itemClassName != null && isset($rowItem[$this->itemClassName])) {
						$arrAttrs = $rowItem[$this->itemClassName];
					}
					else {
						$arrAttrs = $rowItem;
					}
					$objItem = new $this->itemClassName();
					if (is_array($this->payload) && count($this->payload) > 0 && 
							property_exists($objItem, 'payload')) {
						$arrPropPayload = array();
						foreach ($this->payload as $propPayload) {
							if (property_exists($object, $propPayload)) {
								$arrPropPayload[$propPayload] = $object->{$propPayload};
							}
						}
								
						$objItem->payload = $arrPropPayload;
					}
					$objItem->attributes = $arrAttrs;
					$objItem->validate();
					if ($objItem->hasErrors()) {
						foreach($objItem->getErrors() as $attrCol => $errors) {
							$labelCol = $objItem->getAttributeLabel($attrCol);
							$this->addError($object, $attribute, 'Para la grilla ' . $label . ' y fila ' . ($index + 1) . ' la columna ' . $labelCol . ' tiene los siguientes errores:' . join(', ', $errors));
						}
					}
				}
				else if (!$this->isEmpty($rowItem) && is_scalar($rowItem)) {
					continue;
				}
				else {
					continue;
				}
			}
			
		}
	
	}
}