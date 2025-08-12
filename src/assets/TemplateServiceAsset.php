<?php
/**
 * Template Service plugin for Craft CMS 5.x
 * 
 * @author ByVoss Technologies
 * @copyright Copyright (c) 2025 ByVoss Technologies
 * @link https://byvoss.tech
 * @license MIT
 */

namespace byvoss\templateservice\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class TemplateServiceAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__ . '/dist';
        
        $this->depends = [
            CpAsset::class,
        ];
        
        $this->js = [
            'js/template-service.js',
        ];
        
        $this->css = [
            'css/template-service.css',
        ];
        
        parent::init();
    }
}