<?php
namespace MiniPhotoGallery\Controller;

use Application\Controller\ApplicationAbstractAdministrationController;
use Zend\View\Model\ViewModel;

class MiniPhotoGalleryAdministrationController extends ApplicationAbstractAdministrationController
{
    /**
     * Model instance
     * @var object  
     */
    protected $model;

    /**
     * Get model
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('MiniPhotoGallery\Model\MiniPhotoGalleryAdministration');
        }

        return $this->model;
    }

    /**
     * Delete selected categories
     */
    public function deleteCategoriesAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            if (null !== ($categoriesIds = $request->getPost('categories', null))) {
                // delete selected categories
                $deleteResult = false;
                $deletedCount = 0;

                foreach ($categoriesIds as $categoryId) {
                    // get category info
                    if (null == ($categoryInfo = $this->getModel()->getCategoryInfo($categoryId))) { 
                        continue;
                    }

                    // check the permission and increase permission's actions track
                    if (true !== ($result = $this->aclCheckPermission(null, true, false))) {
                        $this->flashMessenger()
                            ->setNamespace('error')
                            ->addMessage($this->getTranslator()->translate('Access Denied'));

                        break;
                    }

                    // delete the category
                    if (true !== ($deleteResult = $this->getModel()->deleteCategory($categoryInfo))) {
                        $this->flashMessenger()
                            ->setNamespace('error')
                            ->addMessage(($deleteResult ? $this->getTranslator()->translate($deleteResult)
                                : $this->getTranslator()->translate('Error occurred')));

                        break;
                    }

                    $deletedCount++;
                }

                if (true === $deleteResult) {
                    $message = $deletedCount > 1
                        ? 'Selected categories have been deleted'
                        : 'The selected category has been deleted';

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate($message));
                }
            }
        }

        // redirect back
        return $request->isXmlHttpRequest()
            ? $this->getResponse()
            : $this->redirectTo('miniphotogallery-administration', 'list-categories', [], true);
    }

    /**
     * Categories list 
     */
    public function listCategoriesAction()
    {
        // check the permission and increase permission's actions track
        if (true !== ($result = $this->aclCheckPermission())) {
            return $result;
        }

        $request = $this->getRequest();

        $filters = [];

        // get a filter form
        $filterForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('MiniPhotoGallery\Form\MiniPhotoGalleryCategoryFilter');

        $request = $this->getRequest();
        $filterForm->getForm()->setData($request->getQuery(), false);

        // check the filter form validation
        if ($filterForm->getForm()->isValid()) {
            $filters = $filterForm->getForm()->getData();
        }

        // get data
        $paginator = $this->getModel()->getCategories($this->getPage(),
                $this->getPerPage(), $this->getOrderBy(), $this->getOrderType(), $filters);

        return new ViewModel([
            'filter_form' => $filterForm->getForm(),
            'paginator' => $paginator,
            'order_by' => $this->getOrderBy(),
            'order_type' => $this->getOrderType(),
            'per_page' => $this->getPerPage()
        ]);
    }

    /**
     * Browse images 
     */
    public function browseImagesAction()
    {
        // check the permission and increase permission's actions track
        if (true !== ($result = $this->aclCheckPermission())) {
            return $result;
        }

        // get the category info
        if (null == ($category = $this->
                getModel()->getCategoryInfo($this->getSlug()))) {

            return $this->redirectTo('miniphotogallery-administration', 'list-categories');
        }

        // get data
        $paginator = $this->getModel()->getImages($category['id'], 
                $this->getPage(), $this->getPerPage(), $this->getOrderBy(), $this->getOrderType());

        return new ViewModel([
            'category' => $category,
            'paginator' => $paginator,
            'order_by' => $this->getOrderBy(),
            'order_type' => $this->getOrderType(),
            'per_page' => $this->getPerPage()
        ]);
    }

    /**
     * Edit an image action
     */
    public function editImageAction()
    {
        // get the image info
        if (null == ($image = $this->
                getModel()->getImageInfo($this->getSlug()))) {

            return $this->redirectTo('miniphotogallery-administration', 'list-categories');
        }

        // get an image form
        $imageForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('MiniPhotoGallery\Form\MiniPhotoGalleryImage')
            ->setImage($image['image']);

        // fill the form with default values
        $imageForm->getForm()->setData($image);
        $request = $this->getRequest();

        // validate the form
        if ($request->isPost()) {
            // make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            // fill the form with received values
            $imageForm->getForm()->setData($post, false);

            // save data
            if ($imageForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                // edit the image
                if (true === ($result = $this->getModel()->
                        editImage($image, $imageForm->getForm()->getData(), $this->params()->fromFiles('image')))) {

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('Image has been edited'));
                }
                else {
                    $this->flashMessenger()
                        ->setNamespace('error')
                        ->addMessage($this->getTranslator()->translate($result));
                }

                return $this->redirectTo('miniphotogallery-administration', 'edit-image', [
                    'slug' => $image['id']
                ]);
            }
        }

        return new ViewModel([
            'image_form' => $imageForm->getForm(),
            'image' => $image
        ]);
    }

    /**
     * Delete selected images
     */
    public function deleteImagesAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            if (null !== ($imagesIds = $request->getPost('images', null))) {
                // delete selected images
                $deleteResult = false;
                $deletedCount = 0;

                foreach ($imagesIds as $imageId) {
                    // get image info
                    if (null == ($imageInfo = $this->getModel()->getImageInfo($imageId))) { 
                        continue;
                    }

                    // check the permission and increase permission's actions track
                    if (true !== ($result = $this->aclCheckPermission(null, true, false))) {
                        $this->flashMessenger()
                            ->setNamespace('error')
                            ->addMessage($this->getTranslator()->translate('Access Denied'));

                        break;
                    }

                    // delete the image
                    if (true !== ($deleteResult = $this->getModel()->deleteImage($imageInfo))) {
                        $this->flashMessenger()
                            ->setNamespace('error')
                            ->addMessage(($deleteResult ? $this->getTranslator()->translate($deleteResult)
                                : $this->getTranslator()->translate('Error occurred')));

                        break;
                    }

                    $deletedCount++;
                }

                if (true === $deleteResult) {
                    $message = $deletedCount > 1
                        ? 'Selected images have been deleted'
                        : 'The selected image has been deleted';

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate($message));
                }
            }
        }

        // redirect back
        return $request->isXmlHttpRequest()
            ? $this->getResponse()
            : $this->redirectTo('miniphotogallery-administration', 'browse-images', [], true);
    }

    /**
     * Add an image action
     */
    public function addImageAction()
    {
        // get the category info
        if (null == ($category = $this->
                getModel()->getCategoryInfo($this->params()->fromQuery('category', -1)))) {

            return $this->redirectTo('miniphotogallery-administration', 'list-categories');
        }

        // get an image form
        $imageForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('MiniPhotoGallery\Form\MiniPhotoGalleryImage');

        $request  = $this->getRequest();

        // validate the form
        if ($request->isPost()) {
            // make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            // fill the form with received values
            $imageForm->getForm()->setData($post, false);

            // save data
            if ($imageForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                // add the image
                if (true === ($result = $this->getModel()->addImage($category['id'], 
                        $imageForm->getForm()->getData(), $this->params()->fromFiles('image')))) {

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('Image has been added'));
                }
                else {
                    $this->flashMessenger()
                        ->setNamespace('error')
                        ->addMessage($this->getTranslator()->translate($result));
                }

                return $this->redirectTo('miniphotogallery-administration', 'add-image', [], false, [
                    'category' => $category['id']
                ]);
            }
        }

        return new ViewModel([
            'category' => $category,
            'image_form' => $imageForm->getForm()
        ]);
    }

    /**
     * Add a new category action
     */
    public function addCategoryAction()
    {
        // get a category form
        $categoryForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('MiniPhotoGallery\Form\MiniPhotoGalleryCategory')
            ->setModel($this->getModel());

        $request  = $this->getRequest();

        // validate the form
        if ($request->isPost()) {
            // fill the form with received values
            $categoryForm->getForm()->setData($request->getPost(), false);

            // save data
            if ($categoryForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                // add a new category
                if (true === ($result = $this->getModel()->addCategory($categoryForm->getForm()->getData()))) {
                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('Category has been added'));
                }
                else {
                    $this->flashMessenger()
                        ->setNamespace('error')
                        ->addMessage($this->getTranslator()->translate($result));
                }

                return $this->redirectTo('miniphotogallery-administration', 'add-category');
            }
        }

        return new ViewModel([
            'category_form' => $categoryForm->getForm()
        ]);
    }

    /**
     * Edit a category action
     */
    public function editCategoryAction()
    {
        // get the category info
        if (null == ($category = $this->
                getModel()->getCategoryInfo($this->getSlug()))) {

            return $this->redirectTo('miniphotogallery-administration', 'list-categories');
        }

        // get the category form
        $categoryForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('MiniPhotoGallery\Form\MiniPhotoGalleryCategory')
            ->setModel($this->getModel())
            ->setCategoryId($category['id']);

        $categoryForm->getForm()->setData($category);

        $request = $this->getRequest();

        // validate the form
        if ($request->isPost()) {
            // fill the form with received values
            $categoryForm->getForm()->setData($request->getPost(), false);

            // save data
            if ($categoryForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                // edit the category
                if (true === ($result = $this->
                        getModel()->editCategory($category['id'], $categoryForm->getForm()->getData()))) {

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('Category has been edited'));
                }
                else {
                    $this->flashMessenger()
                        ->setNamespace('error')
                        ->addMessage($this->getTranslator()->translate($result));
                }

                return $this->redirectTo('miniphotogallery-administration', 'edit-category', [
                    'slug' => $category['id']
                ]);
            }
        }

        return new ViewModel([
            'category' => $category,
            'category_form' => $categoryForm->getForm()
        ]);
    }
}