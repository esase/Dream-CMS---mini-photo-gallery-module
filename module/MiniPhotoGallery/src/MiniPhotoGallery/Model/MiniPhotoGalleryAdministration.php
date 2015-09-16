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
namespace MiniPhotoGallery\Model;

use Application\Utility\ApplicationImage as ImageUtility;
use MiniPhotoGallery\Exception\MiniPhotoGalleryException;
use MiniPhotoGallery\Event\MiniPhotoGalleryEvent;
use Application\Utility\ApplicationFileSystem as FileSystemUtility;
use Application\Utility\ApplicationErrorLogger;
use Application\Service\ApplicationSetting as SettingService;
use Application\Utility\ApplicationPagination as PaginationUtility;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect as DbSelectPaginator;
use Zend\Db\Sql\Predicate\NotIn as NotInPredicate;
use Zend\Db\Sql\Predicate\Like as LikePredicate;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression as Expression;
use Exception;

class MiniPhotoGalleryAdministration extends MiniPhotoGalleryBase
{
    /**
     * Edit image
     *
     * @param array $imageInfo
     *      integer id
     *      string name
     *      string description
     *      integer category_id
     *      string image
     *      integer created
     *      integer order
     * @param array $formData
     *      string name
     *      string description
     *      string image
     *      integer order
     * @param array $image
     * @return boolean|string
     */
    public function editImage($imageInfo, array $formData, array $image = [])
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $update = $this->update()
                ->table('miniphotogallery_image')
                ->set($formData)
                ->where([
                    'id' => $imageInfo['id']
                ]);

            $statement = $this->prepareStatementForSqlObject($update);
            $statement->execute();

            // upload the image
            $this->uploadImage($imageInfo['id'], $image, $imageInfo['image']);

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire the add image event
        MiniPhotoGalleryEvent::fireEditImageEvent($imageInfo['id']);

        return true;
    }

    /**
     * Add an image
     * 
     * @param integer $categoryId
     * @param array $imageInfo
     *      string name
     *      string description
     *      integer order
     * @param array $image
     * @return boolean|string
     */
    public function addImage($categoryId, array $imageInfo, array $image = [])
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $insert = $this->insert()
                ->into('miniphotogallery_image')
                ->values(array_merge($imageInfo, [
                    'category_id' => $categoryId,
                    'created' => time()
                ]));

            $statement = $this->prepareStatementForSqlObject($insert);
            $statement->execute();
            $insertId = $this->adapter->getDriver()->getLastGeneratedValue();

            // upload the image
            $this->uploadImage($insertId, $image);

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire the add image event
        MiniPhotoGalleryEvent::fireAddImageEvent($insertId);

        return true;
    }

    /**
     * Upload an image
     *
     * @param integer $imageId
     * @param array $image
     *      string name
     *      string type
     *      string tmp_name
     *      integer error
     *      integer size
     * @param string $oldImage
     * @throws \MiniPhotoGallery\Exception\MiniPhotoGalleryException
     * @return void
     */
    protected function uploadImage($imageId, array $image, $oldImage = null)
    {
        if (!empty($image['name'])) {
            // delete an old image
            if ($oldImage) {
                if (true !== ($result = $this->deleteMiniPhotoGalleryImage($oldImage))) {
                    throw new MiniPhotoGalleryException('Image deleting failed');
                }
            }

            // upload the image
            if (false === ($imageName =
                    FileSystemUtility::uploadResourceFile($imageId, $image, self::$imagesDir))) {

                throw new MiniPhotoGalleryException('Image uploading failed');
            }

            // resize the image
            ImageUtility::resizeResourceImage($imageName, self::$imagesDir,
                    (int) SettingService::getSetting('miniphotogallery_thumbnail_width'),
                    (int) SettingService::getSetting('miniphotogallery_thumbnail_height'), self::$thumbnailsDir);

            $update = $this->update()
                ->table('miniphotogallery_image')
                ->set([
                    'image' => $imageName
                ])
                ->where([
                    'id' => $imageId
                ]);

            $statement = $this->prepareStatementForSqlObject($update);
            $statement->execute();
        }
    }

    /**
     * Edit category
     *
     * @param integer $categoryId
     * @param array $categoryInfo
     *      string name
     * @return boolean|string
     */
    public function editCategory($categoryId, array $categoryInfo)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $update = $this->update()
                ->table('miniphotogallery_category')
                ->set($categoryInfo)
                ->where([
                    'id' => $categoryId,
                    'language' => $this->getCurrentLanguage()
                ]);

            $statement = $this->prepareStatementForSqlObject($update);
            $statement->execute();

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire the edit category event
        MiniPhotoGalleryEvent::fireEditCategoryEvent($categoryId);

        return true;
    }

    /**
     * Add a new category
     *
     * @param array $categoryInfo
     *      string name
     * @return boolean|string
     */
    public function addCategory(array $categoryInfo)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $insert = $this->insert()
                ->into('miniphotogallery_category')
                ->values(array_merge($categoryInfo, [
                    'language' => $this->getCurrentLanguage()
                ]));

            $statement = $this->prepareStatementForSqlObject($insert);
            $statement->execute();
            $insertId = $this->adapter->getDriver()->getLastGeneratedValue();

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire the add category event
        MiniPhotoGalleryEvent::fireAddCategoryEvent($insertId);

        return true;
    }

    /**
     * Is a category free
     *
     * @param string $categoryName
     * @param integer $categoryId
     * @return boolean
     */
    public function isCategoryFree($categoryName, $categoryId = 0)
    {
        $select = $this->select();
        $select->from('miniphotogallery_category')
            ->columns([
                'id'
            ])
            ->where([
                'name' => $categoryName,
                'language' => $this->getCurrentLanguage()
            ]);

        if ($categoryId) {
            $select->where([
                new NotInPredicate('id', [$categoryId])
            ]);
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->current() ? false : true;
    }

    /**
     * Get categories
     *
     * @param integer $page
     * @param integer $perPage
     * @param string $orderBy
     * @param string $orderType
     * @param array $filters
     *      string name
     * @return \Zend\Paginator\Paginator
     */
    public function getCategories($page = 1, $perPage = 0, $orderBy = null, $orderType = null, array $filters = [])
    {
        $orderFields = [
            'id',
            'name',
            'images'
        ];

        $orderType = !$orderType || $orderType == 'desc'
            ? 'desc'
            : 'asc';

        $orderBy = $orderBy && in_array($orderBy, $orderFields)
            ? $orderBy
            : 'id';

        $select = $this->select();
        $select->from(['a' => 'miniphotogallery_category'])
            ->columns([
                'id',
                'name'
            ])
            ->join(
                ['b' => 'miniphotogallery_image'],
                'a.id = b.category_id',
                [
                    'images' => new Expression('count(b.id)')
                ],
                'left'
            )
            ->where([
                'a.language' => $this->getCurrentLanguage()
            ])
            ->group('a.id')
            ->order($orderBy . ' ' . $orderType);

        // filter by name
        if (!empty($filters['name'])) {
            $select->where([
                new LikePredicate('a.name', '%' . $filters['name'] . '%')
            ]);
        }

        $paginator = new Paginator(new DbSelectPaginator($select, $this->adapter));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(PaginationUtility::processPerPage($perPage));
        $paginator->setPageRange(SettingService::getSetting('application_page_range'));

        return $paginator;
    }

    /**
     * Get images
     *
     * @param integer $categoryId
     * @param integer $page
     * @param integer $perPage
     * @param string $orderBy
     * @param string $orderType
     * @return \Zend\Paginator\Paginator
     */
    public function getImages($categoryId, $page = 1, $perPage = 0, $orderBy = null, $orderType = null)
    {
        $orderFields = [
            'id',
            'name',
            'created',
            'order'
        ];

        $orderType = !$orderType || $orderType == 'desc'
            ? 'desc'
            : 'asc';

        $orderBy = $orderBy && in_array($orderBy, $orderFields)
            ? $orderBy
            : 'id';

        $select = $this->select();
        $select->from(['a' => 'miniphotogallery_image'])
            ->columns([
                'id',
                'name',
                'order',
                'created'
            ])
            ->where([
                'category_id' => $categoryId
            ])
            ->order($orderBy . ' ' . $orderType);

        $paginator = new Paginator(new DbSelectPaginator($select, $this->adapter));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(PaginationUtility::processPerPage($perPage));
        $paginator->setPageRange(SettingService::getSetting('application_page_range'));

        return $paginator;
    }
}