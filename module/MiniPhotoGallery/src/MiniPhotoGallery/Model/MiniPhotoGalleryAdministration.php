<?php
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
     *      string url
     *      integer created
     *      integer order
     * @param array $formData
     *      string name
     *      string description
     *      string image
     *      string url
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
     *      string url
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
     * @throws MiniPhotoGallery\Exception\MiniPhotoGalleryException
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
     * @return object
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
     * @return object
     */
    public function getImages($categoryId, $page = 1, $perPage = 0, $orderBy = null, $orderType = null)
    {
        $orderFields = [
            'id',
            'name',
            'url',
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
                'url',
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