<?php
/**
 * Template Service plugin for Craft CMS 5.x
 * 
 * @author ByVoss Technologies
 * @copyright Copyright (c) 2025 ByVoss Technologies
 * @link https://byvoss.tech
 * @license MIT
 */

namespace byvoss\templateservice\controllers;

use Craft;
use craft\web\Controller;
use yii\web\Response;

class TemplatesController extends Controller
{
    /**
     * Returns a list of all available templates
     */
    public function actionList(): Response
    {
        $this->requirePermission('accessCp');
        
        $templates = [];
        
        // Get the base templates path
        $templatesPath = Craft::$app->getPath()->getSiteTemplatesPath();
        
        // Get all sites
        $sites = Craft::$app->getSites()->getAllSites();
        
        // Check each site for its template root
        foreach ($sites as $site) {
            // Get the site's template mode setting
            $siteSettings = $site->getSettings();
            $templateRoot = isset($siteSettings['template']) ? $siteSettings['template'] : null;
            
            // If site has a custom template root defined, use that
            if ($templateRoot) {
                $sitePath = $templatesPath . DIRECTORY_SEPARATOR . $templateRoot;
                if (is_dir($sitePath)) {
                    $siteTemplates = $this->scanTemplates($sitePath, $templateRoot);
                    $templates = array_merge($templates, $siteTemplates);
                }
            } else {
                // Check if there's a folder with the site handle
                $siteHandle = $site->handle;
                $sitePath = $templatesPath . DIRECTORY_SEPARATOR . $siteHandle;
                
                if (is_dir($sitePath)) {
                    $siteTemplates = $this->scanTemplates($sitePath, $siteHandle);
                    $templates = array_merge($templates, $siteTemplates);
                }
            }
        }
        
        // Also scan root templates directory for files not in site folders
        if (is_dir($templatesPath)) {
            $items = scandir($templatesPath);
            foreach ($items as $item) {
                if ($item[0] === '.') continue;
                
                $fullPath = $templatesPath . DIRECTORY_SEPARATOR . $item;
                
                // Only add root level templates (not directories we already scanned)
                if (is_file($fullPath) && str_ends_with($item, '.twig')) {
                    $templatePath = substr($item, 0, -5);
                    $templates[] = [
                        'path' => $templatePath,
                        'type' => 'template',
                        'label' => $templatePath,
                        'fullLabel' => $templatePath
                    ];
                }
            }
        }
        
        // Remove duplicates
        $uniqueTemplates = [];
        $seen = [];
        foreach ($templates as $template) {
            if (!isset($seen[$template['path']])) {
                $uniqueTemplates[] = $template;
                $seen[$template['path']] = true;
            }
        }
        
        // Sort templates alphabetically
        usort($uniqueTemplates, function($a, $b) {
            return strcmp($a['path'], $b['path']);
        });
        
        return $this->asJson([
            'templates' => $uniqueTemplates
        ]);
    }
    
    /**
     * Recursively scan for .twig templates
     */
    private function scanTemplates($path, $prefix = ''): array
    {
        $templates = [];
        
        if (!is_dir($path)) {
            return $templates;
        }
        
        $items = scandir($path);
        
        foreach ($items as $item) {
            // Skip dots and hidden files
            if ($item[0] === '.') {
                continue;
            }
            
            $fullPath = $path . DIRECTORY_SEPARATOR . $item;
            $relativePath = $prefix ? $prefix . '/' . $item : $item;
            
            if (is_dir($fullPath)) {
                // Don't include folders starting with underscore in autocomplete
                // but do scan their contents
                if ($item[0] !== '_') {
                    // Add the folder itself as an option
                    $templates[] = [
                        'path' => $relativePath,
                        'type' => 'folder',
                        'label' => $relativePath . '/'
                    ];
                }
                
                // Recursively scan subdirectory
                $subTemplates = $this->scanTemplates($fullPath, $relativePath);
                $templates = array_merge($templates, $subTemplates);
                
            } elseif (is_file($fullPath) && str_ends_with($item, '.twig')) {
                // Remove .twig extension for the path
                $templatePath = substr($relativePath, 0, -5);
                
                // Create a nice label
                $label = $templatePath;
                if ($prefix) {
                    // Show indentation in label
                    $depth = substr_count($prefix, '/');
                    $indent = str_repeat('  ', $depth);
                    $fileName = basename($templatePath);
                    $label = $indent . '└ ' . $fileName . ' (' . dirname($relativePath) . ')';
                }
                
                $templates[] = [
                    'path' => $templatePath,
                    'type' => 'template',
                    'label' => $templatePath,
                    'fullLabel' => $label
                ];
            }
        }
        
        return $templates;
    }
}