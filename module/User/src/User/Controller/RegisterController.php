<?php

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Form\RegisterForm;

/**
 * RegisterController
 *
 * @author
 *
 * @version
 *
 */
class RegisterController extends AbstractActionController {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		// TODO Auto-generated RegisterController::indexAction() default action
		
		$form = new RegisterForm();
		
		$data = array(
			'form'=>$form,
		);
		
		return new ViewModel ($data);
	}
}