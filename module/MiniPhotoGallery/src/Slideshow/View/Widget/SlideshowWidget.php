<?php
namespace Slideshow\View\Widget;

use Page\View\Widget\PageAbstractWidget;
use Acl\Service\Acl as AclService;

class SlideshowWidget extends PageAbstractWidget
{
    /**
     * Model instance
     * @var object  
     */
    protected $model;

    /**
     * Get model
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('Slideshow\Model\SlideshowWidget');
        }

        return $this->model;
    }

    /**
     * Include js and css files
     *
     * @return void
     */
    public function includeJsCssFiles()
    {
        $this->getView()->layoutHeadScript()->
                appendFile($this->getView()->layoutAsset('jquery.flexslider.js', 'js', 'slideshow'));

        $this->getView()->layoutHeadLink()->
                appendStylesheet($this->getView()->layoutAsset('flexslider.css', 'css', 'slideshow'));
    }

    /**
     * Get widget content
     *
     * @return string|boolean
     */
    public function getContent() 
    {
        if (AclService::checkPermission('slideshow_view', false) 
                && null != ($category = $this->getWidgetSetting('slideshow_category'))) {

            $images = $this->getModel()->getImages($category);

            if (count($images)) {
                AclService::checkPermission('slideshow_view', true);
                return $this->getView()->partial('slideshow/widget/slideshow', [
                    'enable_slideshow' => (int) $this->getWidgetSetting('slideshow_on'),
                    'images_width' => (int) $this->getWidgetSetting('slideshow_image_width'),
                    'images_height' => (int) $this->getWidgetSetting('slideshow_image_height'),
                    'images' => $images
                ]);
            }
        }

        return false;
    }
}