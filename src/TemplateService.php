<?php
/**
 * Template Service plugin for Craft CMS 5.x
 * 
 * Enhanced template services - Brings back autocomplete and more
 * 
 * @author ByVoss Technologies
 * @copyright Copyright (c) 2025 ByVoss Technologies
 * @link https://byvoss.tech
 * @license MIT
 */

namespace byvoss\templateservice;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\web\View;
use yii\base\Event;

class TemplateService extends Plugin
{
    public static $plugin;
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = false;

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Only load in CP
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->registerCpRoutes();
            $this->attachAssetBundle();
        }

        Craft::info('Template Service plugin loaded', __METHOD__);
    }

    protected function registerCpRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['template-service/templates'] = 'template-service/templates/list';
            }
        );
    }

    protected function attachAssetBundle()
    {
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_TEMPLATE,
            function () {
                $view = Craft::$app->getView();
                
                // Inject on relevant CP pages
                $segments = Craft::$app->getRequest()->getSegments();
                $isRelevantPage = false;
                
                // Check for pages where template fields appear
                $relevantSegments = [
                    'sections',
                    'entry-types', 
                    'singles',
                    'structures',
                    'categories',
                    'globals',
                    'users',
                    'settings' // Include settings pages
                ];
                
                foreach ($segments as $segment) {
                    if (in_array($segment, $relevantSegments)) {
                        $isRelevantPage = true;
                        break;
                    }
                }
                
                // Also check if we're on a site settings page
                $currentRoute = Craft::$app->getRequest()->getFullPath();
                if (str_contains($currentRoute, 'settings/sites')) {
                    $isRelevantPage = true;
                }
                
                if ($isRelevantPage && Craft::$app->getRequest()->getIsCpRequest()) {
                    $view->registerAssetBundle(assets\TemplateServiceAsset::class);
                }
            }
        );
    }
}