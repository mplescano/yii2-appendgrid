<?php

namespace mplescano\yii\appendgrid\widgets;

use yii\web\AssetBundle;

class AppendGridAsset extends AssetBundle
{
    
    public $sourcePath = '@npm/jquery.appendgrid';
    public $js = [
    'jquery.appendGrid-1.6.2.js'
    ];
    public $css = [
    'jquery.appendGrid-1.6.2.css'
    ];
    public $depends = [
        'yii\bootstrap\BootstrapAsset',
        'mplescano\yii\appendgrid\widgets\JuiAsset',
    ];
    
}