<?php
namespace MiniPhotoGallery;

use Application\Service\Application as ApplicationService;
use MiniPhotoGallery\Model\MiniPhotoGalleryBase as MiniPhotoGalleryBaseModel;
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
            $gallery = $moduleManager->getEvent()->getParam('ServiceManager')
                ->get('Application\Model\ModelManager')
                ->getInstance('MiniPhotoGallery\Model\MiniPhotoGalleryBase');

            // delete a language dependent gallery categories
            if (null != ($categories = $gallery->getAllCategories($e->getParam('object_id')))) {
                // process categories
                foreach ($categories as $category) {
                    $gallery->deleteCategory((array) $category);
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
            ],
            'factories' => [
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