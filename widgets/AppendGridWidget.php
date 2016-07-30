<?php

namespace mplescano\yii\appendgrid\widgets;

use Yii;
use yii\helpers\Json;
use yii\db\ActiveRecord;
use yii\base\Model;
use yii\jui\InputWidget;
use yii\base\Exception;
use yii\helpers\Html;
use yii\web\JsExpression;

/**
 * ex.appendgrid.1_5_0.widgets.CAppendGridWidget
 * 
 * @author mplescano
 *
 */
class AppendGridWidget extends InputWidget {
    
    /**
     * array(
     *   column01,
     *   column02,
     *   ...
     * )
     * 
     * column01: array(
     *     name => '', //required
     *     display => '',
     *     type => '', //required
     *     ctrlAttr => array(maxlength => ''),
     *     ctrlOptions => array(valueOption => labelOption),
     *     ctrlCss => array(width => '100px'),
     *     onChange => 'js:function (evt, rowIndex) {...',
     *     onClick => 'js:function (evt, rowIndex) {...',
     *     ctrlClass => 'class-three',
     *     
     * )
     * 
     * @var array
     */
    public $columns = array();
    
    public $caption;
    
    public $initRows = 0;
    
    public $isScalarData = false;
    
    public $itemClassName = null;
    
    /**
     * @var array: array('columnName01' => array('optionValue' => array('attr-name01' => 'attr-value01', 'attr-name02' => 'attr-value02')))
     */
    public $optionAttributes = array();
    
    /**
     *### .init()
     *
     * Initializes the widget.
     */
    public function init() {
        
        if ($this->name === null && !isset($this->options['name']) && !$this->hasModel()) {
            throw new Exception(Yii::t('app','{class} must specify "model" and "{attribute}" or "{name}" property values.',
                    array('{class}' => get_class($this), '{attribute}' => 'attribute', '{name}' => 'name')));
        }
        
        if ($this->name === null && $this->hasModel() && !isset($this->options['name'])) {
            $this->options['name'] = Html::getInputName($this->model, $this->attribute);
        }
        
        if ($this->name !== null && !$this->hasModel() && !isset($this->options['name'])) {
            $this->options['name'] = $this->name;
        }
        
        if (is_array($this->columns) && count($this->columns) > 0) {
            foreach($this->columns as &$itemColumn) {
                if (!isset($itemColumn['name'])) {
                    throw new Exception('Missing the attribute name in the columns config');
                }
                if (!isset($itemColumn['type'])) {
                    throw new Exception('Missing the attribute type in the columns config');
                }
                if ($itemColumn['type'] == 'select' && !isset($itemColumn['ctrlOptions'])) {
                    throw new Exception('Missing the attribute ctrlOptions in the columns config if the type is select');
                }
                else if ($itemColumn['type'] == 'select' && isset($itemColumn['ctrlOptions'])) {
                    if (isArrayAssoc($itemColumn['ctrlOptions'], false)) {
                        $arrObjOptions = array();
                        foreach($itemColumn['ctrlOptions'] as $key => $value) {
                            $arrOptionAttr = array();
                            if (isset($this->optionAttributes[$itemColumn['name']]) && count($this->optionAttributes[$itemColumn['name']]) > 0 
                            		&& isset($this->optionAttributes[$itemColumn['name']][$key]) && is_array($this->optionAttributes[$itemColumn['name']][$key])) {
                                foreach ($this->optionAttributes[$itemColumn['name']][$key] as $attrName => $attrValue) {
                                    $arrOptionAttr[] = new OptionAttribute($attrName, $attrValue);
                                }
                            }
                            $arrObjOptions[] = new OptionSelect($key, $value, $arrOptionAttr);
                        }
                        $itemColumn['ctrlOptions'] = $arrObjOptions;
                    }
                }
            }
        }
        
        parent::init();
        
        if (!Yii::$app->request->isAjax) {
            AppendGridAsset::register($this->getView());
        }
    }
    
    /**
     *### .run()
     *
     * Runs the widget.
     */
    public function run() {
        //list($name, $id) = $this->resolveNameID();

        $id = $this->options['id'];
        $name = $this->options['name'];
        
        $arrData = array();
        if ($this->hasModel())
        {
            $attribute = $this->attribute;
            $arrData = $this->model->{$attribute};
            if ($this->caption == null) {
                $this->caption = $this->model->getAttributeLabel($attribute);
            }
        }
        else
        {
            $arrData = $this->value;
        }
        
        $arrInitData = array();
        $strClassName = $this->itemClassName;
        if (is_array($arrData) && count($arrData) > 0) {
            foreach($arrData as $itemData) {
                $arrItemInitData = array();
                foreach($this->columns as &$itemColumn) {
                    if (is_object($itemData)) {
                        if (!property_exists($itemData, $itemColumn['name']) && 
                        		!($itemData instanceof ActiveRecord && $itemData->hasAttribute($itemColumn['name']))) {
                            throw new Exception('Missing the attribute ' . $itemColumn['name'] . ' in the inital data');
                        }
                        $arrItemInitData[$itemColumn['name']] = $itemData->{$itemColumn['name']};
                        //$label=$model->getAttributeLabel($attribute);
                        if ($itemData instanceof Model) {
                            if (!isset($itemColumn['display'])) {
                                $itemColumn['display'] = $itemData->getAttributeLabel($itemColumn['name']);
                            }
                        }
                        if ($strClassName == null) {
                            $strClassName = modelName($itemData);
                        }
                    }
                    else if (is_array($itemData)) {
                        if (!array_key_exists($itemColumn['name'], $itemData)) {
                            throw new Exception('Missing the attribute ' . $itemColumn['name'] . ' in the inital data');
                        }
                        $arrItemInitData[$itemColumn['name']] = $itemData[$itemColumn['name']];
                    }
                    else {
                    	$arrItemInitData[$itemColumn['name']] = $itemData;
                    }
                }
                $arrInitData[] = $arrItemInitData;
            }
            
            $this->clientOptions['initData'] = $arrInitData;
        }
        
        $htmlOptions = array();
        $idContainer = $htmlOptions['id'] = $id . '_container';
        $htmlOptions['name'] = $id . '_container';
        echo Html::tag('table', '', $htmlOptions);
        
        if (!isset($this->clientOptions['idPrefix'])) {
            $this->clientOptions['idPrefix'] = $id;
        }
        if (!isset($this->clientOptions['nameFormatter'])) {
            if ($this->isScalarData) {
                $this->clientOptions['nameFormatter'] = new JsExpression('function (idPrefix, name, uniqueIndex) {
                    return \'' . $name . '\' + \'[]\';
                }');//\' + uniqueIndex + \'
            }
            else {
                if ($strClassName == null) {
                    $this->clientOptions['nameFormatter'] = new JsExpression('function (idPrefix, name, uniqueIndex) {
                        return \'' . $name . '\' + "[" + uniqueIndex + "]" + "[" + name + "]";
                    }');
                }
                else {
                    $this->clientOptions['nameFormatter'] = new JsExpression('function (idPrefix, name, uniqueIndex) {
                        return \'' . $name . '\' + "[" + uniqueIndex + "]" + "[' . $strClassName . ']" + "[" + name + "]";
                    }');
                }
            }
        }
        if (!isset($this->clientOptions['hideButtons'])) {
            $this->clientOptions['hideButtons'] = array('insert' => 'true', 'moveUp' => 'true', 'moveDown' => 'true', 'removeLast' => 'true');
        }
        
        $this->clientOptions['columns'] = $this->columns;
        $this->clientOptions['initRows'] = $this->initRows;
        if ($this->caption != null) {
            $this->clientOptions['caption'] = $this->caption;
        }
        $options = Json::htmlEncode($this->clientOptions);

        $js = "jQuery('#{$idContainer}').appendGrid($options);";
        
        $this->getView()->registerJs($js);
    }
}

class OptionSelect {
    
    public $label;
    
    public $value;
    
    /**
     * @var OptionAttribute[]
     */
    public $attributes;
    
    public function __construct($key, $keyValue, $attributes = array()) {
        $this->label = $keyValue;
        $this->value = $key;
        $this->attributes = $attributes;
    }
}

class OptionAttribute {
    
    public $attrName;
    
    public $attrValue;
    
    public function __construct($attrName, $attrValue) {
        $this->attrName = $attrName;
        $this->attrValue = $attrValue;
    }
}

/**
 *
 * @see http://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
 * @param array $array
 * @param boolean $strict
 * @return boolean
 */
function isArrayAssoc(array $array, $strict = true)
{
    $countAssoc = 0;
    foreach ($array as $key => $value) {
        if (is_string($key)) {
            $countAssoc++;
        }
    }
    if ($strict) {
        return ($countAssoc == count($array));
    }
    return ($countAssoc > 0);
}

/**
 * Generates HTML name for given model.
 * @see CHtml::setModelNameConverter()
 * @param CModel|string $model the data model or the model class name
 * @return string the generated HTML name value
 * @since 1.1.14
 */
function modelName($model)
{
    $className = is_object($model) ? get_class($model) : (string) $model;
    return trim(str_replace('\\','_',$className),'_');
}
