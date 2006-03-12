<?php
/* SVN FILE: $Id$ */

/**
 * Basic Cake functionality.
 *
 * Core functions for including other source files, loading models and so forth.
 *
 * PHP versions 4 and 5
 *
 * CakePHP :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright (c) 2006, Cake Software Foundation, Inc.
 *                     1785 E. Sahara Avenue, Suite 490-204
 *                     Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright    Copyright (c) 2006, Cake Software Foundation, Inc.
 * @link         http://www.cakefoundation.org/projects/info/cakephp CakePHP Project
 * @package      cake
 * @subpackage   cake.cake
 * @since        CakePHP v 0.2.9
 * @version      $Revision$
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
 */



/**
 * Configuration, directory layout and standard libraries
 */
if(!isset($bootstrap))
{
    require CORE_PATH.'cake'.DS.'basics.php';
    require APP_PATH.'config'.DS.'core.php';
    require CORE_PATH.'cake'.DS.'config'.DS.'paths.php';
}
require LIBS.'object.php';
require LIBS.'session.php';
require LIBS.'security.php';
require LIBS.'neat_array.php';
require LIBS.'inflector.php';

/**
 * Enter description here...
 */
if (empty($uri) && defined('BASE_URL'))
{
    $uri = setUri();
    if ($uri === '/' || $uri === '/index.php' || $uri === '/app/')
    {
        $_GET['url'] = '/';
        $url = '/';
    }
    else
    {
        $elements = explode('/index.php', $uri);
        if(!empty($elements[1]))
        {
            $_GET['url'] = $elements[1];
            $url = $elements[1];
        }
        else
        {
            $_GET['url'] = '/';
            $url = '/';
        }
    }
}
else
{
    if(empty($_GET['url']))
    {
        $url = null;
    }
    else
    {
        $url = $_GET['url'];
    }

}


if (strpos($url, 'ccss/') === 0)
{
    include WWW_ROOT.DS.'css.php';
    die();
}


if (DEBUG)
{
    error_reporting(E_ALL);

    if(function_exists('ini_set'))
    {
        ini_set('display_errors', 1);
    }
}
else
{
    error_reporting(0);
}

$TIME_START = getMicrotime();
if(defined('CACHE_CHECK') && CACHE_CHECK === true)
{
    if (empty($uri))
    {
        $uri = setUri();
    }

    $filename = CACHE.'views'.DS.'*'.str_replace('/', '_', $uri).'*';
    $files = glob($filename);

    if(isset($files[0]))
    {
        if (file_exists($files[0]))
        {
            if (preg_match('/(\\d+).php/', $files[0], $match))
            {
                if(time() >= $match['1'])
                {
                    @unlink($files[0]);
                    unset($out);
                }
                else
                {
                    ob_start();
                    include($files[0]);
                    if (DEBUG)
                    {
                        echo "<!-- Cached Render Time: ". round(getMicrotime() - $TIME_START, 4) ."s -->";
                    }
                    $out = ob_get_clean();
                    die(e($out));
                }
            }
        }
    }
}

require CAKE.'dispatcher.php';
require LIBS.'model'.DS.'connection_manager.php';

config('database');

if (!class_exists('AppModel'))
{
    require LIBS.'model'.DS.'model.php';
    loadModels();
}
?>