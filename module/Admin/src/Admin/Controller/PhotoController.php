<?php
namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * PhotoController
 *
 * @author
 *
 * @version
 *
 */
class PhotoController extends AbstractActionController
{

    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
        // TODO Auto-generated WarningController::indexAction() default action
        return new ViewModel();
    }
}