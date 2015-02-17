<?php
namespace MiniPhotoGallery\Event;

use User\Service\UserIdentity as UserIdentityService;
use Application\Event\ApplicationAbstractEvent;

class MiniPhotoGalleryEvent extends ApplicationAbstractEvent
{
    /**
     * Delete category event
     */
    const DELETE_CATEGORY = 'miniphotogallery_delete_category';

    /**
     * Add category event
     */
    const ADD_CATEGORY = 'miniphotogallery_add_category';

    /**
     * Edit category event
     */
    const EDIT_CATEGORY = 'miniphotogallery_edit_category';

    /**
     * Add image event
     */
    const ADD_IMAGE = 'miniphotogallery_add_image';

    /**
     * Edit image event
     */
    const EDIT_IMAGE = 'miniphotogallery_edit_image';

    /**
     * Delete image event
     */
    const DELETE_IMAGE = 'miniphotogallery_delete_image';

    /**
     * Fire delete image event
     *
     * @param integer $imageId
     * @return void
     */
    public static function fireDeleteImageEvent($imageId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - Mini photo gallery image deleted by guest'
            : 'Event - Mini photo gallery image deleted by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$imageId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $imageId];

        self::fireEvent(self::DELETE_IMAGE, 
                $imageId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

    /**
     * Fire edit image event
     *
     * @param integer $imageId
     * @return void
     */
    public static function fireEditImageEvent($imageId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - Mini photo gallery image edited by guest'
            : 'Event - Mini photo gallery image edited by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$imageId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $imageId];

        self::fireEvent(self::EDIT_IMAGE, 
                $imageId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

    /**
     * Fire add image event
     *
     * @param integer $imageId
     * @return void
     */
    public static function fireAddImageEvent($imageId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - Mini photo gallery image added by guest'
            : 'Event - Mini photo gallery image added by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$imageId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $imageId];

        self::fireEvent(self::ADD_IMAGE, 
                $imageId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

    /**
     * Fire edit category event
     *
     * @param integer $categoryId
     * @return void
     */
    public static function fireEditCategoryEvent($categoryId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - Mini photo gallery category edited by guest'
            : 'Event - Mini photo gallery category edited by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$categoryId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $categoryId];

        self::fireEvent(self::EDIT_CATEGORY, 
                $categoryId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

    /**
     * Fire add category event
     *
     * @param integer $categoryId
     * @return void
     */
    public static function fireAddCategoryEvent($categoryId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - Mini photo gallery category added by guest'
            : 'Event - Mini photo gallery category added by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$categoryId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $categoryId];

        self::fireEvent(self::ADD_CATEGORY, 
                $categoryId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

   /**
     * Fire delete category event
     *
     * @param integer $categoryId
     * @return void
     */
    public static function fireDeleteCategoryEvent($categoryId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - Mini photo gallery category deleted by guest'
            : 'Event - Mini photo gallery category deleted by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$categoryId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $categoryId];

        self::fireEvent(self::DELETE_CATEGORY, 
                $categoryId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }
}