<?php
namespace MiniPhotoGallery\Service;

use Application\Service\ApplicationServiceLocator as ServiceLocatorService;
use Localization\Service\Localization as LocalizationService;

class MiniPhotoGallery
{
    /**
     * Categories
     * @var array
     */
    protected static $categories = null;

    /**
     * Get all categories
     *
     * @return array
     */
    public static function getAllCategories()
    {
        if (null === self::$categories) {
            $categories = ServiceLocatorService::getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('MiniPhotoGallery\Model\MiniPhotoGalleryBase')
                ->getAllCategories(LocalizationService::getCurrentLocalization()['language']);

			// process categories
			self::$categories = [];
			foreach ($categories as $category) {
				self::$categories[$category->id] = $category->name;
			}
        }

        return self::$categories;
    }
}