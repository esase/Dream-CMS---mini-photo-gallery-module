<?php
namespace MiniPhotoGallery\Test;

define('APPLICATION_ROOT', '../../../');
require_once APPLICATION_ROOT . 'init_tests_autoloader.php';

use UnitTestBootstrap;

class MiniPhotoGalleryBootstrap extends UnitTestBootstrap\UnitTestBootstrap
{}

MiniPhotoGalleryBootstrap::init();