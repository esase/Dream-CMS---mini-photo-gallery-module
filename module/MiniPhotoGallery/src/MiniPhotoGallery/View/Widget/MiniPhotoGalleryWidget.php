<?php
namespace MiniPhotoGallery\View\Widget;

use Page\View\Widget\PageAbstractWidget;
use Acl\Service\Acl as AclService;

class MiniPhotoGalleryWidget extends PageAbstractWidget
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
                ->getInstance('MiniPhotoGallery\Model\MiniPhotoGalleryWidget');
        }

        return $this->model;
    }

    /**
     * Get widget content
     *
     * @return string|boolean
     */
    public function getContent() 
    {
        if (AclService::checkPermission('miniphotogallery_view', false) 
                && null != ($category = $this->getWidgetSetting('miniphotogallery_category'))) {

            // get a pagination page number
            $pageParamName = 'page_' . $this->widgetConnectionId;
            $page = $this->getView()->applicationRoute()->getQueryParam($pageParamName , 1);
            $paginator = $this->getModel()->getImages($page, $category, (int) $this->getWidgetSetting('miniphotogallery_per_page'));

            if ($paginator->count()) {
                AclService::checkPermission('miniphotogallery_view', true);
                $galleryWrapperId = 'mini-photo-gallery-list-' . $this->widgetConnectionId;

                // get data list
                $dataList = $this->getView()->partial('partial/data-list', [
                    'ajax' => [
                        'wrapper_id' => $galleryWrapperId,
                        'widget_connection' => $this->widgetConnectionId,
                        'widget_position' => $this->widgetPosition
                    ],
                    'paginator' => $paginator,
                    'paginator_page_query' => $pageParamName,
                    'unit' => 'mini-photo-gallery/partial/_photo-unit',
                    'unit_params' => [
                    ]
                ]);

                if ($this->getRequest()->isXmlHttpRequest()) {
                    return $dataList;
                }

                return $this->getView()->partial('mini-photo-gallery/widget/photos-list', [
                    'wrapper' => $galleryWrapperId,
                    'data' => $dataList
                ]);
            }
        }

        return false;
    }
}