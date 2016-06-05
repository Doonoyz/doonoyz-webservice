<?php
/**
 * Indexcontroller to redirect to the documentation
 */

class IndexController extends Zend_Controller_Action {
	public function indexAction() {
		$this->_redirect('/doc');
	}
}