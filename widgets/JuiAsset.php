<?php

namespace mplescano\yii\appendgrid\widgets;

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class JuiAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery-ui';
    public $js = [
        'ui/core.js',
        'ui/widget.js',
        'ui/button.js',
    ];
    public $css = [
        'themes/base/core.css',
        'themes/base/button.css',
        'themes/smoothness/theme.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
