SET sql_mode='STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE';

SET @moduleId = __module_id__;

-- application admin menu

SET @maxOrder = (SELECT `order` + 1 FROM `application_admin_menu` ORDER BY `order` DESC LIMIT 1);

INSERT INTO `application_admin_menu_category` (`name`, `module`, `icon`) VALUES
('Slideshow', @moduleId, 'slideshow_menu_item.png');

SET @menuCategoryId = (SELECT LAST_INSERT_ID());
SET @menuPartId = (SELECT `id` FROM `application_admin_menu_part` WHERE `name` = 'Modules');

INSERT INTO `application_admin_menu` (`name`, `controller`, `action`, `module`, `order`, `category`, `part`) VALUES
('List of categories', 'slideshow-administration', 'list-categories', @moduleId, @maxOrder + 1, @menuCategoryId, @menuPartId);

-- acl resources

INSERT INTO `acl_resource` (`resource`, `description`, `module`) VALUES
('slideshow_administration_list_categories', 'ACL - Viewing slideshow categories in admin area', @moduleId),
('slideshow_administration_add_category', 'ACL - Adding slideshow categories in admin area', @moduleId),
('slideshow_administration_delete_categories', 'ACL - Deleting slideshow categories in admin area', @moduleId),
('slideshow_administration_edit_category', 'ACL - Editing slideshow categories in admin area', @moduleId),
('slideshow_administration_browse_images', 'ACL - Browsing slideshow images in admin area', @moduleId),
('slideshow_administration_add_image', 'ACL - Adding slideshow images in admin area', @moduleId),
('slideshow_administration_edit_image', 'ACL - Editing slideshow images in admin area', @moduleId),
('slideshow_administration_delete_images', 'ACL - Deleting slideshow images in admin area', @moduleId);

INSERT INTO `acl_resource` (`resource`, `description`, `module`) VALUES
('slideshow_view', 'ACL - Viewing slideshow', @moduleId);
SET @viewSlideshowResourceId = (SELECT LAST_INSERT_ID());

INSERT INTO `acl_resource_connection` (`role`, `resource`) VALUES
(3, @viewSlideshowResourceId),
(2, @viewSlideshowResourceId);

-- application events

INSERT INTO `application_event` (`name`, `module`, `description`) VALUES
('slideshow_delete_category', @moduleId, 'Event - Deleting slideshow categories'),
('slideshow_add_category', @moduleId, 'Event - Adding slideshow categories'),
('slideshow_edit_category', @moduleId, 'Event - Editing slideshow categories'),
('slideshow_add_image', @moduleId, 'Event - Adding slideshow images'),
('slideshow_edit_image', @moduleId, 'Event - Editing slideshow images'),
('slideshow_delete_image', @moduleId, 'Event - Deleting slideshow images');

-- system pages and widgets

INSERT INTO `page_widget` (`name`, `module`, `type`, `description`, `duplicate`, `forced_visibility`, `depend_page_id`) VALUES
('slideshowWidget', @moduleId, 'public', 'Slideshow', NULL, NULL, NULL);
SET @widgetId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('slideshow_category', @widgetId, 'Category', 'select', NULL, 1, 1, NULL, NULL, NULL, 'return Slideshow\\Service\\Slideshow::getAllCategories();');

INSERT INTO `page_widget_setting_category` (`name`, `module`) VALUES
('Animation', @moduleId);
SET @animationSettingCategoryId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('slideshow_on', @widgetId, 'Enable slideshow', 'checkbox', NULL, 2, @animationSettingCategoryId, NULL, NULL, NULL, NULL);
SET @widgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@widgetSettingId, '1', NULL);

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('slideshow_image_width', @widgetId, 'Images width', 'integer', NULL, 3, @animationSettingCategoryId, NULL, 'return intval(''__value__'') > 0;', 'Value should be greater than 0', NULL);

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('slideshow_image_height', @widgetId, 'Images height', 'integer', NULL, 4, @animationSettingCategoryId, NULL, 'return intval(''__value__'') > 0;', 'Value should be greater than 0', NULL);

-- module tables

CREATE TABLE IF NOT EXISTS `slideshow_category` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `language` CHAR(2) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `category` (`name`, `language`),
    FOREIGN KEY (`language`) REFERENCES `localization_list`(`language`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `slideshow_image` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `category_id` INT(11) UNSIGNED NOT NULL,
    `image` VARCHAR(100) DEFAULT NULL,
    `url` VARCHAR(100) DEFAULT NULL,
    `created` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`category_id`) REFERENCES `slideshow_category`(`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;