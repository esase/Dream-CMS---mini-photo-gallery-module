SET sql_mode='STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE';

SET @moduleId = __module_id__;

-- application admin menu

SET @maxOrder = (SELECT `order` + 1 FROM `application_admin_menu` ORDER BY `order` DESC LIMIT 1);

INSERT INTO `application_admin_menu_category` (`name`, `module`, `icon`) VALUES
('Mini photo gallery', @moduleId, 'miniphotogallery_menu_item.png');

SET @menuCategoryId = (SELECT LAST_INSERT_ID());
SET @menuPartId = (SELECT `id` FROM `application_admin_menu_part` WHERE `name` = 'Modules');

INSERT INTO `application_admin_menu` (`name`, `controller`, `action`, `module`, `order`, `category`, `part`) VALUES
('List of categories', 'miniphotogalleries-administration', 'list-categories', @moduleId, @maxOrder + 1, @menuCategoryId, @menuPartId),
('Settings', 'miniphotogalleries-administration', 'settings', @moduleId, @maxOrder + 2, @menuCategoryId, @menuPartId);

-- acl resources

INSERT INTO `acl_resource` (`resource`, `description`, `module`) VALUES
('miniphotogalleries_administration_list_categories', 'ACL - Viewing mini photo gallery categories in admin area', @moduleId),
('miniphotogalleries_administration_add_category', 'ACL - Adding mini photo gallery categories in admin area', @moduleId),
('miniphotogalleries_administration_delete_categories', 'ACL - Deleting mini photo gallery categories in admin area', @moduleId),
('miniphotogalleries_administration_edit_category', 'ACL - Editing mini photo gallery categories in admin area', @moduleId),
('miniphotogalleries_administration_browse_images', 'ACL - Browsing mini photo gallery images in admin area', @moduleId),
('miniphotogalleries_administration_add_image', 'ACL - Adding mini photo gallery images in admin area', @moduleId),
('miniphotogalleries_administration_edit_image', 'ACL - Editing mini photo gallery images in admin area', @moduleId),
('miniphotogalleries_administration_delete_images', 'ACL - Deleting mini photo gallery images in admin area', @moduleId),
('miniphotogalleries_administration_settings', 'ACL - Editing mini photo gallery settings in admin area', @moduleId);

INSERT INTO `acl_resource` (`resource`, `description`, `module`) VALUES
('miniphotogallery_view', 'ACL - Viewing mini photo gallery', @moduleId);
SET @viewMiniPhotoGalleryResourceId = (SELECT LAST_INSERT_ID());

INSERT INTO `acl_resource_connection` (`role`, `resource`) VALUES
(3, @viewMiniPhotoGalleryResourceId),
(2, @viewMiniPhotoGalleryResourceId);

-- application events

INSERT INTO `application_event` (`name`, `module`, `description`) VALUES
('miniphotogallery_delete_category', @moduleId, 'Event - Deleting mini photo gallery categories'),
('miniphotogallery_add_category', @moduleId, 'Event - Adding mini photo gallery categories'),
('miniphotogallery_edit_category', @moduleId, 'Event - Editing mini photo gallery categories'),
('miniphotogallery_add_image', @moduleId, 'Event - Adding mini photo gallery images'),
('miniphotogallery_edit_image', @moduleId, 'Event - Editing mini photo gallery images'),
('miniphotogallery_delete_image', @moduleId, 'Event - Deleting mini photo gallery images');

-- application settings

INSERT INTO `application_setting_category` (`name`, `module`) VALUES
('Main settings', @moduleId);
SET @settingsCategoryId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('miniphotogallery_thumbnail_width', 'Thumbnail width', NULL, 'integer', 1, 1, @settingsCategoryId, @moduleId, NULL, NULL, 'return intval(''__value__'') > 0;', 'Value should be greater than 0');
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId, '384', NULL);

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('miniphotogallery_thumbnail_height', 'Thumbnail height', NULL, 'integer', 1, 2, @settingsCategoryId, @moduleId, NULL, NULL, 'return intval(''__value__'') > 0;', 'Value should be greater than 0');
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId, '384', NULL);

-- system pages and widgets

INSERT INTO `page_widget` (`name`, `module`, `type`, `description`, `duplicate`, `forced_visibility`, `depend_page_id`) VALUES
('miniPhotoGalleryWidget', @moduleId, 'public', 'Mini photo gallery', 1, NULL, NULL);
SET @widgetId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('miniphotogallery_category', @widgetId, 'Category', 'select', NULL, 1, 1, NULL, NULL, NULL, 'return MiniPhotoGallery\\Service\\MiniPhotoGallery::getAllCategories();');

INSERT INTO `page_widget_setting_category` (`name`, `module`) VALUES
('Display options', @moduleId);
SET @displaySettingCategoryId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('miniphotogallery_per_page', @widgetId, 'Count of photos per page', 'integer', 1, 1, @displaySettingCategoryId, NULL, 'return intval(''__value__'') > 0;', 'Value should be greater than 0', NULL);
SET @widgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@widgetSettingId, '10', NULL);

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('miniphotogallery_thumbs_width_medium', @widgetId, 'Thumbs width for medium devices desktops (<=992px)', 'select', 1, 2, @displaySettingCategoryId, NULL, NULL, NULL, NULL);
SET @widgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@widgetSettingId, 'col-md-3', NULL);

INSERT INTO `page_widget_setting_predefined_value` (`setting_id`, `value`) VALUES
(@widgetSettingId, 'col-md-3'),
(@widgetSettingId, 'col-md-4'),
(@widgetSettingId, 'col-md-6'),
(@widgetSettingId, 'col-md-12');

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('miniphotogallery_thumbs_width_small', @widgetId, 'Thumbs width for small devices tablets (<=768px)', 'select', 1, 3, @displaySettingCategoryId, NULL, NULL, NULL, NULL);
SET @widgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@widgetSettingId, 'col-sm-4', NULL);

INSERT INTO `page_widget_setting_predefined_value` (`setting_id`, `value`) VALUES
(@widgetSettingId, 'col-sm-3'),
(@widgetSettingId, 'col-sm-4'),
(@widgetSettingId, 'col-sm-6'),
(@widgetSettingId, 'col-sm-12');

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('miniphotogallery_thumbs_width_extra_small', @widgetId, 'Thumbs width for extra small devices phones (<768px)', 'select', 1, 4, @displaySettingCategoryId, NULL, NULL, NULL, NULL);
SET @widgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@widgetSettingId, 'col-xs-6', NULL);

INSERT INTO `page_widget_setting_predefined_value` (`setting_id`, `value`) VALUES
(@widgetSettingId, 'col-xs-3'),
(@widgetSettingId, 'col-xs-4'),
(@widgetSettingId, 'col-xs-6'),
(@widgetSettingId, 'col-xs-12');

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('miniphotogallery_title_type', @widgetId, 'Thumbs title type', 'select', 1, 5, @displaySettingCategoryId, NULL, NULL, NULL, NULL);
SET @widgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@widgetSettingId, 'inside', NULL);

INSERT INTO `page_widget_setting_predefined_value` (`setting_id`, `value`) VALUES
(@widgetSettingId, 'inside'),
(@widgetSettingId, 'float'),
(@widgetSettingId, 'outside'),
(@widgetSettingId, 'over');

-- delete content service integration
INSERT INTO `application_delete_content_service` (`path`, `module`) VALUES
('\\MiniPhotoGallery\\DeleteContentHandler\\MiniPhotoGalleryHandler', @moduleId);

-- module tables

CREATE TABLE IF NOT EXISTS `miniphotogallery_category` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `language` CHAR(2) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `category` (`name`, `language`),
    FOREIGN KEY (`language`) REFERENCES `localization_list`(`language`)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `miniphotogallery_image` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `category_id` INT(11) UNSIGNED NOT NULL,
    `image` VARCHAR(100) DEFAULT NULL,
    `created` INT(10) UNSIGNED NOT NULL,
    `order` SMALLINT(5) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    FOREIGN KEY (`category_id`) REFERENCES `miniphotogallery_category`(`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;