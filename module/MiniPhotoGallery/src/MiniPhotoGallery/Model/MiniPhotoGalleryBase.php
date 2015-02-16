<?php
namespace MiniPhotoGallery\Model;

use MiniPhotoGallery\Exception\MiniPhotoGalleryException;
use MiniPhotoGallery\Event\MiniPhotoGalleryEvent;
use Application\Utility\ApplicationErrorLogger;
use Application\Model\ApplicationAbstractBase;
use Application\Utility\ApplicationFileSystem as FileSystemUtility;
use Zend\Db\ResultSet\ResultSet;
use Exception;

class MiniPhotoGalleryBase extends ApplicationAbstractBase
{
    /**
     * Images directory
     * @var string
     */
    protected static $imagesDir = 'miniphotogallery/';

    /**
     * Get images directory name
     *
     * @return string
     */
    public static function getImagesDir()
    {
        return self::$imagesDir;
    }

    /**
     * Get all categories
     * 
     * @param string $language
     * @return object ResultSet
     */
    public function getAllCategories($language = null)
    {
        $select = $this->select();
        $select->from('miniphotogallery_category')
            ->columns([
                'id',
                'name',
                'language'
            ]);

      if ($language) {
            $select->where([
                'language' => $language
            ]);
      }

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }

    /**
     * Delete a category
     *
     * @param array $categoryInfo
     *      integer id
     *      string name
     *      string language
    * @throws MiniPhotoGallery/Exception/MiniPhotoGalleryException
     * @return boolean|string
     */
    public function deleteCategory(array $categoryInfo)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            // get all images
            $select = $this->select();
            $select->from('miniphotogallery_image')
               ->columns([
                   'image'
               ])
            ->where([
               'category_id' => $categoryInfo['id']
            ]);

            $statement = $this->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet;
            $resultSet->initialize($statement->execute());

            // delete assigned images
            foreach ($resultSet as $image) {
                // delete an image
                if (true !== ($imageDeleteResult = $this->deleteMiniPhotoGalleryImage($image['image']))) {
                   throw new MiniPhotoGalleryException('Image deleting failed');
                }
            }

            $delete = $this->delete()
                ->from('miniphotogallery_category')
                ->where([
                    'id' => $categoryInfo['id']
                ]);

            $statement = $this->prepareStatementForSqlObject($delete);
            $result = $statement->execute();

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        $result =  $result->count() ? true : false;

        // fire the delete category event
        if ($result) {
            MiniPhotoGalleryEvent::fireDeleteCategoryEvent($categoryInfo['id']);
        }

        return $result;
    }

    /**
     * Delete an image
     *
     * @param array $imageInfo
     *      integer id
     *      string name
     *      string description
     *      integer category_id
     *      string image
     *      string url
     *      integer created
     * @throws MiniPhotoGallery/Exception/MiniPhotoGalleryException
     * @return boolean|string
     */
    public function deleteImage(array $imageInfo)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $delete = $this->delete()
                ->from('miniphotogallery_image')
                ->where([
                    'id' => $imageInfo['id']
                ]);

            $statement = $this->prepareStatementForSqlObject($delete);
            $result = $statement->execute();

            // delete an image
            if (true !== ($imageDeleteResult = $this->deleteMiniPhotoGalleryImage($imageInfo['image']))) {
                throw new MiniPhotoGalleryException('Image deleting failed');
            }

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        $result =  $result->count() ? true : false;

        // fire the delete image event
        if ($result) {
            MiniPhotoGalleryEvent::fireDeleteImageEvent($imageInfo['id']);
        }

        return $result;
    }

    /**
     * Delete an image
     *
     * @param string $imageName
     * @return boolean
     */
    protected function deleteMiniPhotoGalleryImage($imageName)
    {
        if (true !== ($result = FileSystemUtility::deleteResourceFile($imageName, self::$imagesDir))) {
            return $result;
        }

        return true; 
    }

    /**
     * Get category info
     *
     * @param integer $id
     * @param boolean $currentLanguage
     * @return array
     */
    public function getCategoryInfo($id, $currentLanguage = true)
    {
        $select = $this->select();
        $select->from('miniphotogallery_category')
            ->columns([
                'id',
                'name'
            ])
            ->where([
                'id' => $id
            ]);

        if ($currentLanguage) {
            $select->where([
                'language' => $this->getCurrentLanguage()
            ]);
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return $result->current();
    }

    /**
     * Get image info
     *
     * @param integer $id
     * @param boolean $currentLanguage
     * @return array
     */
    public function getImageInfo($id, $currentLanguage = true)
    {
        $select = $this->select();
        $select->from(['a' => 'miniphotogallery_image'])
            ->columns([
                'id',
                'name',
                'description',
                'category_id',
                'image',
                'url',
                'created',
            ])
            ->join(
                ['b' => 'miniphotogallery_category'],
                'a.category_id = b.id',
                [
                    'category_name' => 'name'
                ]
            )
            ->where([
                'a.id' => $id
            ]);

        if ($currentLanguage) {
            $select->where([
                'b.language' => $this->getCurrentLanguage()
            ]);
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return $result->current();
    }
}