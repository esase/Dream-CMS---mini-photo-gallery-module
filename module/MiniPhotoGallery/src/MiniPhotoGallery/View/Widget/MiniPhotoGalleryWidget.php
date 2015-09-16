<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.dream-cms.kg/en/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Dream CMS software.
 * The Initial Developer of the Original Code is Dream CMS (http://www.dream-cms.kg).
 * All portions of the code written by Dream CMS are Copyright (c) 2014. All Rights Reserved.
 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2014 Dream CMS. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Dream CMS software
 * Attribution URL: http://www.dream-cms.kg/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */
namespace MiniPhotoGallery\View\Widget;

use Page\View\Widget\PageAbstractWidget;
use Acl\Service\Acl as AclService;

class MiniPhotoGalleryWidget extends PageAbstractWidget
{
    /**
     * Model instance
     *
     * @var \MiniPhotoGallery\Model\MiniPhotoGalleryWidget
     */
    protected $model;

    /**
     * Get model
     *
     * @return \MiniPhotoGallery\Model\MiniPhotoGalleryWidget
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
     * Include js and css files
     *
     * @return void
     */
    public function includeJsCssFiles()
    {
        $this->getView()->layoutHeadScript()->
                appendFile($this->getView()->layoutAsset('jquery.fancybox.js', 'js', 'miniphotogallery'));

        $this->getView()->layoutHeadLink()->
                appendStylesheet($this->getView()->layoutAsset('jquery.fancybox.css', 'css', 'miniphotogallery'));

        if (!$this->getView()->localization()->isCurrentLanguageLtr()) {
            $this->getView()->layoutHeadLink()->
                appendStylesheet($this->getView()->layoutAsset('jquery.fancybox.rtl.css', 'css', 'miniphotogallery'));
        }
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
                        'thumbs_width_medium' => $this->getWidgetSetting('miniphotogallery_thumbs_width_medium'),
                        'thumbs_width_small' => $this->getWidgetSetting('miniphotogallery_thumbs_width_small'),
                        'thumbs_width_extra_small' => $this->getWidgetSetting('miniphotogallery_thumbs_width_extra_small')
                    ],
                    'uniform_height' => '#' . $galleryWrapperId . ' .thumbnail'
                ]);

                // add an init script
                $content = $this->getView()->partial('mini-photo-gallery/widget/_photos-list-init', [
                    'wrapper' => $galleryWrapperId,
                    'data' => $dataList,
                    'title_type' => $this->getWidgetSetting('miniphotogallery_title_type')
                ]);

                if ($this->getRequest()->isXmlHttpRequest()) {
                    return $content;
                }

                // wrap all data
                return $this->getView()->partial('mini-photo-gallery/widget/photos-list', [
                    'wrapper' => $galleryWrapperId,
                    'data' => $content
                ]);
            }
        }

        return false;
    }
}