<?php
namespace Slideshow\View\Helper;
 
use Zend\View\Helper\AbstractHelper;

class SlideshowImageUrl extends AbstractHelper
{
    /**
     * Images url
     * @var string
     */
    protected $imagesUrl;

    /**
     * Class constructor
     *
     * @param string $imagesUrl
     */
    public function __construct($imagesUrl)
    {
        $this->imagesUrl = $imagesUrl;
    }

    /**
     * Image url
     *
     * @param string $imageName
     * @return string
     */
    public function __invoke($imageName)
    {
        return $this->imagesUrl . $imageName;
    }
}