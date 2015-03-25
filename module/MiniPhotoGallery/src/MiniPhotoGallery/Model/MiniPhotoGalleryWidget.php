<?php
namespace MiniPhotoGallery\Model;

use Application\Service\ApplicationSetting as SettingService;
use Zend\Paginator\Adapter\DbSelect as DbSelectPaginator;
use Zend\Paginator\Paginator;

class MiniPhotoGalleryWidget extends MiniPhotoGalleryBase
{
    /**
     * Get images
     *
     * @param integer $page
     * @param integer $category
     * @param integer $perPage
     * @return array|object
     */
    public function getImages($page, $category, $perPage)
    {
        $select = $this->select();
        $select->from('miniphotogallery_image')
            ->columns([
                'name',
                'description',
                'image',
            ])
            ->order('order asc, created desc')
            ->where([
                'category_id' => $category
            ]);

        $paginator = new Paginator(new DbSelectPaginator($select, $this->adapter));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);
        $paginator->setPageRange(SettingService::getSetting('application_page_range'));

        return $paginator;
    }
}