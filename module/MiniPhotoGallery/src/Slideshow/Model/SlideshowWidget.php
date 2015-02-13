<?php
namespace Slideshow\Model;

use Zend\Db\ResultSet\ResultSet;

class SlideshowWidget extends SlideshowBase
{
	/**
     * Get images
     * 
     * @return object ResultSet
     */
    public function getImages()
    {
        $select = $this->select();
        $select->from('slideshow_image')
            ->columns([
                'id',
                'description',
                'image',
				'url'
            ]);

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }
}