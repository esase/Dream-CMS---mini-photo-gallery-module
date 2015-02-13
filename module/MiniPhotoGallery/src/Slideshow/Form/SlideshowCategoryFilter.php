<?php
namespace Slideshow\Form;

use Application\Form\ApplicationCustomFormBuilder;
use Application\Form\ApplicationAbstractCustomForm;

class SlideshowCategoryFilter extends ApplicationAbstractCustomForm 
{
    /**
     * Form name
     * @var string
     */
    protected $formName = 'slideshow-category-filter';

    /**
     * Form method
     * @var string
     */
    protected $method = 'get';

    /**
     * List of not validated elements
     * @var array
     */
    protected $notValidatedElements = ['submit'];

    /**
     * Form elements
     * @var array
     */
    protected $formElements = [
        'name' => [
            'name' => 'name',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT,
            'label' => 'Name'
        ],
        'submit' => [
            'name' => 'submit',
            'type' => ApplicationCustomFormBuilder::FIELD_SUBMIT,
            'label' => 'Search'
        ]
    ];
}