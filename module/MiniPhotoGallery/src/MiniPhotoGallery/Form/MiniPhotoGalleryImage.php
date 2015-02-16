<?php
namespace MiniPhotoGallery\Form;

use Application\Service\Application as ApplicationService;
use Application\Form\ApplicationAbstractCustomForm;
use Application\Form\ApplicationCustomFormBuilder;
use MiniPhotoGallery\Model\MiniPhotoGalleryBase as MiniPhotoGalleryBaseModel;

class MiniPhotoGalleryImage extends ApplicationAbstractCustomForm 
{
    /**
     * Name max string length
     */
    const NAME_MAX_LENGTH = 50;

    /**
     * Description max string length
     */
    const DESCRIPTION_MAX_LENGTH = 255;

    /**
     * Url max string length
     */
    const URL_MAX_LENGTH = 100;

    /**
     * Form name
     * @var string
     */
    protected $formName = 'miniphotogallery-image';

    /**
     * List of ignored elements
     * @var array
     */
    protected $ignoredElements = ['image'];

    /**
     * Image
     * @var string
     */
    protected $image;

    /**
     * Form elements
     * @var array
     */
    protected $formElements = [
        'name' => [
            'name' => 'name',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT,
            'label' => 'Name',
            'required' => true,
            'max_length' => self::NAME_MAX_LENGTH,
            'category' => 'General info'
        ],
        'image' => [
            'name' => 'image',
            'type' => ApplicationCustomFormBuilder::FIELD_IMAGE,
            'label' => 'Image',
            'required' => true,
            'extra_options' => [
                'file_url' => null,
                'preview' => false,
                'delete_option' => false
            ],
            'category' => 'General info'
        ],
        'description' => [
            'name' => 'description',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT_AREA,
            'label' => 'Description',
            'required' => false,
            'max_length' => self::DESCRIPTION_MAX_LENGTH,
            'category' => 'Miscellaneous info'
        ],
        'url' => [
            'name' => 'url',
            'type' => ApplicationCustomFormBuilder::FIELD_URL,
            'label' => 'Url',
            'required' => false,
            'max_length' => self::URL_MAX_LENGTH,
            'category' => 'Miscellaneous info'
        ],
        'csrf' => [
            'name' => 'csrf',
            'type' => ApplicationCustomFormBuilder::FIELD_CSRF
        ],
        'submit' => [
            'name' => 'submit',
            'type' => ApplicationCustomFormBuilder::FIELD_SUBMIT,
            'label' => 'Submit'
        ]
    ];

    /**
     * Get form instance
     *
     * @return object
     */
    public function getForm()
    {
        // get form builder
        if (!$this->form) {
            // add preview for the image
            if ($this->image) {
                $this->formElements['image']['required'] = false;
                $this->formElements['image']['extra_options']['preview'] = true;
                $this->formElements['image']['extra_options']['file_url'] =
                        ApplicationService::getResourcesUrl() . MiniPhotoGalleryBaseModel::getImagesDir() . $this->image;
            }

            $this->form = new ApplicationCustomFormBuilder($this->formName,
                        $this->formElements, $this->translator, $this->ignoredElements, $this->notValidatedElements, $this->method);
        }

        return $this->form;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return object fluent interface
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }
}