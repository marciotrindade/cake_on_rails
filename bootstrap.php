<?php
/* SVN FILE: $Id$ */
/**
 * Basic Cake functionality.
 *
 * Core functions for including other source files, loading models and so forth.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2007, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2007, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake
 * @since			CakePHP(tm) v 0.2.9
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
if (!defined('PHP5')) {
	define ('PHP5', (phpversion() >= 5));
}
/**
 * Configuration, directory layout and standard libraries
 */
	if (!isset($bootstrap)) {
		require CORE_PATH . 'cake' . DS . 'basics.php';
		require APP_PATH . 'config' . DS . 'core.php';
		require CORE_PATH . 'cake' . DS . 'config' . DS . 'paths.php';
	}
	$TIME_START = getMicrotime();
	require LIBS . 'object.php';
	require LIBS . 'session.php';
	require LIBS . 'security.php';
	require LIBS . 'inflector.php';
	require LIBS . 'configure.php';
	$paths = Configure::getInstance();
	Configure::store(null, 'class.paths');
	Configure::load('class.paths');
	Configure::write('debug', DEBUG);
/**
 * Verify that the application's salt has been changed from the default value
 */
	if (CAKE_SESSION_STRING == 'DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi') {
		trigger_error('Please change the value of CAKE_SESSION_STRING in app/config/core.php to a salt value specific to your application', E_USER_NOTICE);
	}
/**
 * Get the application path and request URL
 */
	if (empty($uri) && defined('BASE_URL')) {
		$uri = setUri();

		if ($uri === '/' || $uri === '/index.php' || $uri === '/app/') {
			$_GET['url'] = '/';
			$url = '/';
		} else {
			if (strpos($uri, 'index.php') !== false) {
				$uri = r('?', '', $uri);
				$elements = explode('/index.php', $uri);
			} else {
				$elements = explode('/?', $uri);
			}

			if (!empty($elements[1])) {
				$_GET['url'] = $elements[1];
				$url = $elements[1];
			} else {
				$_GET['url'] = '/';
				$url = '/';
			}
		}
	} else {
		if (empty($_GET['url'])) {
			$url = null;
		} else {
			$url = $_GET['url'];
		}
	}

	if (strpos($url, 'ccss/') === 0) {
		include WWW_ROOT . DS . 'css.php';
		exit();
	}

	$folders = array('js' => 'text/javascript', 'css' => 'text/css');
	$requestPath = explode('/', $url);

	if (in_array($requestPath[0], array_keys($folders))) {
		if (file_exists(VENDORS . join(DS, $requestPath))) {
			header('Content-type: ' . $folders[$requestPath[0]]);
			include (VENDORS . join(DS, $requestPath));
			exit();
		}
	}

	require CAKE . 'dispatcher.php';

	if (defined('CACHE_CHECK') && CACHE_CHECK === true) {
		if (empty($uri)) {
			$uri = setUri();
		}
		$filename = CACHE . 'views' . DS . convertSlash($uri) . '.php';

		if (file_exists($filename)) {
			uses('controller' . DS . 'component', DS . 'view' . DS . 'view');
			$v = null;
			$view = new View($v);
			$view->renderCache($filename, $TIME_START);
		} elseif(file_exists(CACHE . 'views' . DS . convertSlash($uri) . '_index.php')) {
			uses('controller' . DS . 'component', DS . 'view' . DS . 'view');
			$v = null;
			$view = new View($v);
			$view->renderCache(CACHE . 'views' . DS . convertSlash($uri) . '_index.php', $TIME_START);
		}
	}
?>