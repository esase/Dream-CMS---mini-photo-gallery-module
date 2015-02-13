<?php
namespace Slideshow;

use Application\Service\Application as ApplicationService;
use Slideshow\Model\SlideshowBase as SlideshowBaseModel;
use Localization\Event\LocalizationEvent;
use Zend\ModuleManager\ModuleManagerInterface;

class Module
{
    /**
     * Init
     */
    public function init(ModuleManagerInterface $moduleManager)
    {
        $eventManager = LocalizationEvent::getEventManager();
        $eventManager->attach(LocalizationEvent::UNINSTALL, function ($e) use ($moduleManager) {
            $slideshow = $moduleManager->getEvent()->getParam('ServiceManager')
                ->get('Application\Model\ModelManager')
                ->getInstance('Slideshow\Model\SlideshowBase');

            // delete a language dependent slideshow categories
            if (null != ($categories = $slideshow->getAllCategories($e->getParam('object_id')))) {
                // process categories
                foreach ($categories as $category) {
                    $slideshow->deleteCategory((array) $category);
                }
            }
        });
    }

    /**
     * Return autoloader config array
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/autoload_classmap.php',
            ],
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    /**
     * Return service config array
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return [];
    }

    /**
     * Init view helpers
     */
    public function getViewHelperConfig()
    {
        return [
            'invokables' => [
                'slideshowWidget' => 'Slideshow\View\Widget\SlideshowWidget'
            ],
            'factories' => [
                'slideshowImageUrl' => function() {
                    $imagesDir = ApplicationService::getResourcesUrl() . SlideshowBaseModel::getImagesDir();
                    return new \Slideshow\View\Helper\SlideshowImageUrl($imagesDir);
                }
            ]
        ];
    }

    /**
     * Return path to config file
     *
     * @return boolean
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}