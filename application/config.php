<?php
date_default_timezone_set ( 'Europe/Paris' );
set_include_path ( ROOT_DIR 
					. PATH_SEPARATOR . ROOT_DIR . '../Core' 
					. PATH_SEPARATOR . ROOT_DIR . '../Core/Captcha' 
					. PATH_SEPARATOR . ROOT_DIR . 'library' 
					. PATH_SEPARATOR . ROOT_DIR . get_include_path () );
require_once (ROOT_DIR . '../Core/autoload.php');
Zend_Session::start();
Zend_Session::regenerateId();
$registry = new Zend_Registry(array(), ArrayObject::ARRAY_AS_PROPS);
Zend_Registry::setInstance($registry);

$configFull = new Zend_Config_Ini ( ROOT_DIR . 'application/config.ini' );
$registry->configWebservice = $configFull;

$config = $configFull->application;

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', $config->display_errors); 
define('DEBUG_ENABLED', $config->debug_enabled);

$frontendOptions = array ('lifetime' => $config->cache->life, 'automatic_serialization' => true );
$backendOptions = array ('cache_dir' => ROOT_DIR . 'application/views/cache' );

$cache = Zend_Cache::factory ( 'Core', 'File', $frontendOptions, $backendOptions );
$registry->cache = $cache;

if ($config->use_language) {
	Zend_Translate::setCache ( $cache );

	$tr = new Zend_Translate ( 'Zend_Translate_Adapter_Gettext', ROOT_DIR . 'application/languages/lang.en.mo', 'en' );
	$tr->addTranslation ( ROOT_DIR . 'application/languages/lang.fr.mo', 'fr' );

	try {
		$locale = new Zend_Locale ( Twindoo_User::getLocale() );
	} catch ( Zend_Locale_Exception $e ) {
		$locale = new Zend_Locale ( 'en' );
	}
	try {
		$tr->setLocale ( $locale->getLanguage () );
	} catch ( Exception $e ) {
		$locale = new Zend_Locale ( 'en' );
		$tr->setLocale ( $locale->getLanguage () );
	}

	Twindoo_User::setLocale ( $locale->getLanguage () );

	$registry->translate = $tr;

	if (! function_exists ( 't_' )) {
		function t_($text) {
			$tr = Zend_Registry::getInstance ()->translate;
			return $tr->_ ( $text );
		}
	}
} else {
	Twindoo_User::setLocale('en');
	if (! function_exists ( 't_' )) {
		function t_($text) {
			return $text;
		}
	}
}
// setup smtp


$tr = new Zend_Mail_Transport_Smtp ( $config->mail->smtp, $config->mail->config->toArray () );
Zend_Mail::setDefaultTransport ( $tr );

// array defining smarty script paths
$smartyPaths = array ('scriptPath' => ROOT_DIR . 'application/views/scripts',
						'compileDir' => ROOT_DIR . 'application/views/compile',
						'cacheDir' => ROOT_DIR . 'application/views/cache',
						'cache' => $config->template->cache,
						'cacheLife' => $config->template->cacheLife,
						'compileCheck' => $config->template->compileCheck,
						'configDir' => ROOT_DIR . 'application' );

$view = new Webservice_View_Smarty ( $smartyPaths );
//$view->setScriptPath(ROOT_DIR.'templates');


$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer ( );
$viewRenderer->setView ( $view );
// make viewRenderer use Webservice_View_Smarty


// make it search for .tpl files
$viewRenderer->setViewSuffix ( 'tpl' );
$registry->template = $view->getEngine ();


Zend_Controller_Action_HelperBroker::addHelper ( $viewRenderer );
Zend_Controller_Action_HelperBroker::addHelper ( new Twindoo_Controller_Action_Helper_Csrf ( ) );

if (!ROBOT_PROCESS) {
	// setup controller
	$frontController = Zend_Controller_Front::getInstance ();
	$frontController->registerPlugin(new Twindoo_Controller_Plugin_CsrfProtect());
	$frontController->throwExceptions ( false );
	$frontController->setControllerDirectory ( ROOT_DIR . '/application/controllers' );

	// load routing rules configuration
	/* To enable if you want to use routes.ini */
	/*
	$config = new Zend_Config_Ini ( ROOT_DIR . 'application/routes.ini', 'all' );
	$router = $frontController->getRouter ();
	$router->addConfig ( $config, 'routes' );
	
	$frontController->setRouter ( $router );
	/**/

	// run!
	$frontController->dispatch ();
}
