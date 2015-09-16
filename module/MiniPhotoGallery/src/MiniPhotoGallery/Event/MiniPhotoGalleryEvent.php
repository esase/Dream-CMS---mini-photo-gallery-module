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