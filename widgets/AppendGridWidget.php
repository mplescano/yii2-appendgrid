<?php

namespace mplescano\yii\appendgrid\widgets;

/**
 * ex.appendgrid.1_5_0.widgets.CAppendGridWidget
 * 
 * @author mplescano
 *
 */
class AppendGridWidget extends CJuiInputWidget {
    
	public $scriptFile=false;
	
	public $cssFile=false;
    
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
    
    /**
     * @var string handles the assets folder path.
     */
    public $_assetsUrl;
    
    /**
     * @var bool|null Whether to republish assets on each request.
     * If set to true, all YiiBooster assets will be republished on each request.
     * Passing null to this option restores the default handling of CAssetManager of YiiBooster assets.
     *
     * @since YiiBooster 1.0.6
     */
    public $forceCopyAssets = false;
    
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
        if (is_array($this->columns) && count($this->columns) > 0) {
            foreach($this->columns as &$itemColumn) {
                if (!isset($itemColumn['name'])) {
                    throw new CException('Missing the attribute name in the columns config');
                }
                if (!isset($itemColumn['type'])) {
                    throw new CException('Missing the attribute type in the columns config');
                }
                if ($itemColumn['type'] == 'select' && !isset($itemColumn['ctrlOptions'])) {
                    throw new CException('Missing the attribute ctrlOptions in the columns config if the type is select');
                }
                else if ($itemColumn['type'] == 'select' && isset($itemColumn['ctrlOptions'])) {
                    if (Utils::isArrayAssoc($itemColumn['ctrlOptions'])) {
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
        
        if (YII_DEBUG) {
            $cssFile = 'jquery.appendGrid-1.5.1.css';
            $jsFile = 'jquery.appendGrid-1.5.1-patched.js';
        }
        else {
            $cssFile = 'jquery.appendGrid-1.5.1.min.css';
            $jsFile = 'jquery.appendGrid-1.5.1-patched.min.js';
        }
        
        if (!Yii::app()->request->isAjaxRequest) {
        	$cs = Yii::app()->getClientScript();
        	
        	$cs->registerCssFile($this->getAssetsUrl() . '/css/jquery.ui.core.css');
        	$cs->registerCssFile($this->getAssetsUrl() . '/css/jquery.ui.button.css');
        	$cs->registerCssFile($this->getAssetsUrl() . '/css/jquery.ui.theme.css');
        	$cs->registerCssFile($this->getAssetsUrl() . '/css/' . $cssFile);
        	
        	$cs->registerScriptFile($this->getAssetsUrl() . '/js/jquery.ui.core.js', CClientScript::POS_END);
        	$cs->registerScriptFile($this->getAssetsUrl() . '/js/jquery.ui.widget.js', CClientScript::POS_END);
        	$cs->registerScriptFile($this->getAssetsUrl() . '/js/jquery.ui.button.js', CClientScript::POS_END);
        	$cs->registerScriptFile($this->getAssetsUrl() . '/js/' . $jsFile, CClientScript::POS_END);
        }
    }
    
    protected function registerCoreScripts()
    {
    	if (!Yii::app()->request->isAjaxRequest) {
    		//error_log('cappendgrid registerCoreScripts...');
    		parent::registerCoreScripts();
    	}
    }
    
    /**
     * Returns the URL to the published assets folder.
     * @return string an absolute URL to the published asset
     */
    public function getAssetsUrl() {
    
    	if (isset($this->_assetsUrl)) {
    		return $this->_assetsUrl;
    	} else {
    		return $this->_assetsUrl = Yii::app()->getAssetManager()->publish(realpath(dirname(__FILE__) . '/../assets'), false, -1, $this->forceCopyAssets);
    	}
    }
    
    protected function resolveNameID($nameProperty='name',$attributeProperty='attribute')
    {
        if($this->$nameProperty!==null)
            $name=$this->$nameProperty;
        elseif(isset($this->htmlOptions[$nameProperty]))
        $name=$this->htmlOptions[$nameProperty];
        elseif($this->hasModel())
        $name=$this->resolveArrayName($this->model, $this->$attributeProperty);
        else
            throw new CException(Yii::t('zii','{class} must specify "model" and "{attribute}" or "{name}" property values.',
                    array('{class}'=>get_class($this),'{attribute}'=>$attributeProperty,'{name}'=>$nameProperty)));
    
        if(($id=$this->getId(false))===null)
        {
            if(isset($this->htmlOptions['id']))
                $id=$this->htmlOptions['id'];
            else
                $id=CHtml::getIdByName($name);
        }
    
        return array($name,$id);
    }
    
    public static function resolveArrayName($model, $attribute)
    {
        $modelName=CHtml::modelName($model);
    
        if(($pos=strpos($attribute,'['))!==false)
        {
            if($pos!==0)  // e.g. name[a][b]
                return $modelName.'['.substr($attribute,0,$pos).']'.substr($attribute,$pos);
            if(($pos=strrpos($attribute,']'))!==false && $pos!==strlen($attribute)-1)  // e.g. [a][b]name
            {
                $sub=substr($attribute,0,$pos+1);
                $attribute=substr($attribute,$pos+1);
                return $modelName.$sub.'['.$attribute.']';
            }
            if(preg_match('/\](\w+\[.*)$/',$attribute,$matches))
            {
                $name=$modelName.'['.str_replace(']','][',trim(strtr($attribute,array(']['=>']','['=>']')),']')).']';
                $attribute=$matches[1];
                return $name;
            }
        }
        return $modelName.'['.$attribute.']';
    }
    
    /**
     *### .run()
     *
     * Runs the widget.
     */
    public function run() {
        list($name, $id) = $this->resolveNameID();
        
        if (isset($this->htmlOptions['id'])) {
            $id = $this->htmlOptions['id'];
        }
        else {
            $this->htmlOptions['id'] = $id;
        }
        
        if (isset($this->htmlOptions['name'])) {
            $name = $this->htmlOptions['name'];
        }
        
        $arrData = array();
        if ($this->hasModel())
        {
            //echo CHtml::activeHiddenField($this->model,$this->attribute,$this->htmlOptions);
            $attribute = $this->attribute;
            $arrData = $this->model->{$attribute};
            if ($this->caption == null) {
                $this->caption = $this->model->getAttributeLabel($attribute);
            }
        }
        else
        {
            //echo CHtml::hiddenField($name,$this->value,$this->htmlOptions);
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
                        		!($itemData instanceof CActiveRecord && $itemData->hasAttribute($itemColumn['name']))) {
                            throw new CException('Missing the attribute ' . $itemColumn['name'] . ' in the inital data');
                        }
                        $arrItemInitData[$itemColumn['name']] = $itemData->{$itemColumn['name']};
                        //$label=$model->getAttributeLabel($attribute);
                        if ($itemData instanceof CModel) {
                            if (!isset($itemColumn['display'])) {
                                $itemColumn['display'] = $itemData->getAttributeLabel($itemColumn['name']);
                            }
                        }
                        if ($strClassName == null) {
                            $strClassName = CHtml::modelName($itemData);
                        }
                    }
                    else if (is_array($itemData)) {
                        if (!array_key_exists($itemColumn['name'], $itemData)) {
                            throw new CException('Missing the attribute ' . $itemColumn['name'] . ' in the inital data');
                        }
                        $arrItemInitData[$itemColumn['name']] = $itemData[$itemColumn['name']];
                    }
                    else {
                    	$arrItemInitData[$itemColumn['name']] = $itemData;
                    }
                }
                $arrInitData[] = $arrItemInitData;
            }
            
            $this->options['initData'] = $arrInitData;
        }
        
        $idContainer = $this->htmlOptions['id'] = $id . '_container';
        $this->htmlOptions['name'] = $id . '_container';
        echo CHtml::tag('table', $this->htmlOptions, '');
        
        if (!isset($this->options['idPrefix'])) {
            $this->options['idPrefix'] = $id;
        }
        if (!isset($this->options['nameFormatter'])) {
            if ($this->isScalarData) {
                $this->options['nameFormatter'] = 'js:function (idPrefix, name, uniqueIndex) {
                    return \'' . $name . '\' + \'[]\';
                }';//\' + uniqueIndex + \'
            }
            else {
                if ($strClassName == null) {
                    $this->options['nameFormatter'] = 'js:function (idPrefix, name, uniqueIndex) {
                        return \'' . $name . '\' + "[" + uniqueIndex + "]" + "[" + name + "]";
                    }';
                }
                else {
                    $this->options['nameFormatter'] = 'js:function (idPrefix, name, uniqueIndex) {
                        return \'' . $name . '\' + "[" + uniqueIndex + "]" + "[' . $strClassName . ']" + "[" + name + "]";
                    }';
                }
            }
        }
        if (!isset($this->options['hideButtons'])) {
            $this->options['hideButtons'] = array('insert' => 'true', 'moveUp' => 'true', 'moveDown' => 'true', 'removeLast' => 'true');
        }
        
        $this->options['columns'] = $this->columns;
        $this->options['initRows'] = $this->initRows;
        if ($this->caption != null) {
            $this->options['caption'] = $this->caption;
        }
        $options = CJavaScript::encode($this->options);

        $js = "jQuery('#{$idContainer}').appendGrid($options);";
        
        $cs = Yii::app()->getClientScript();
        $cs->registerScript(__CLASS__ . '#' . $idContainer, $js, null, array(), !Yii::app()->request->isAjaxRequest);
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