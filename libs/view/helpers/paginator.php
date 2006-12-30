<?php
/* SVN FILE: $Id$ */
/**
 * Pagination Helper class file.
 *
 * Generates pagination links
 *
 * CakePHP :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright (c)	2006, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP Project
 * @package			cake
 * @subpackage		cake.cake.libs.view.helpers
 * @since			CakePHP v 1.2.0
 * @version			$Revision$
 * @modifiedby		$LastChangedBy$
 * @lastmodified	$Date$
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Html Helper class for easy use of HTML widgets.
 *
 * HtmlHelper encloses all methods needed while working with HTML pages.
 *
 * @package		cake
 * @subpackage	cake.cake.libs.view.helpers
 */
class PaginatorHelper extends AppHelper {

/**
 * Helper dependencies
 *
 * @var array
 */
	var $helpers = array('Html', 'Ajax');

	var $Html = null;

	var $Ajax = null;
/**
 * Holds the default model for paged recordsets
 *
 * @var string
 */
	var $__defaultModel = null;

	function params($model = null) {
		if ($model == null) {
			$model = $this->defaultModel();
		}
		if (!isset($this->params['paging']) || empty($this->params['paging'][$model])) {
			return null;
		}
		return $this->params['paging'][$model];
	}
/**
 * Gets the current page of the in the recordset for the given model
 *
 * @param  string $model
 * @return string
 */
	function current($model = null) {
		if ($model == null) {
			$model = $this->defaultModel();
		}
		$params = $this->params[$model];
		if (isset($params['options']['page'])) {
			return $params['options']['page'];
		}
		return null;
	}
/**
 * Generates a "previous" link for a set of paged records
 *
 * @param  string $title
 * @param  array $options
 * @param  string $disabledTitle
 * @param  array $disabledOptions
 * @return string
 */
	function prev($title = '<< Previous', $options = array(), $disabledTitle = null, $disabledOptions = array()) {
		return $this->__pagingLink('Prev', $title, $options, $disabledTitle, $disabledOptions);
	}
/**
 * Generates a "next" link for a set of paged records
 *
 * @param  string $title
 * @param  array $options
 * @param  string $disabledTitle
 * @param  array $disabledOptions
 * @return string
 */
	function next($title = 'Next >>', $options = array(), $disabledTitle = null, $disabledOptions = array()) {
		return $this->__pagingLink('Next', $title, $options, $disabledTitle, $disabledOptions);
	}
/**
 * Protected method
 *
 */
	function __pagingLink($which, $title = null, $options = array(), $disabledTitle = null, $disabledOptions = array()) {
		$check = 'has' . $which;
		$options = am(
			array(
				'model' => $this->defaultModel(),
				'step' => 1,
				'url' => array(),
				'escape' => true
			),
			$options
		);
		$paging = $this->params($options['model']);

		if ($this->{$check}() || $disabledTitle !== null || !empty($disabledOptions)) {
			if (!$this->{$check}()) {
				$options = am($options, $disabledOptions);
				if (!empty($disabledTitle) && $disabledTitle !== true) {
					$title = $disabledTitle;
				}
			}
		} else {
			return null;
		}

		$keys = array('url', 'model', 'escape');
		foreach ($keys as $key) {
			${$key} = null;
			if (isset($options[$key])) {
				${$key} = $options[$key];
				unset($options[$key]);
			}
		}

		if (is_array($url)) {
			if ($which == 'Prev') {
				$options['step'] *= -1;
			}
			$url = am(
				array_filter(Set::diff($paging['options'], $paging['defaults'])),
				array('page' => ($paging['page'] + $options['step'])),
				$url
			);

			if (isset($url['order'])) {
				$sort = $direction = null;
				if (is_array($url['order'])) {
					list($sort, $direction) = array(preg_replace('/.*\./', '', key($url['order'])), current($url['order']));
				}
				unset($url['order']);
				$url = am($url, compact('sort', 'direction'));
			}
		} elseif (is_string($url)) {
			$url .= '/' . ($paging['page'] + $options['step']);
		}

		if ($this->{$check}()) {
			$obj = 'Html';
			if (isset($options['update'])) {
				$obj = 'Ajax';
			}
			return $this->{$obj}->link($title, $url, $options, false, $escape);
		} else {
			return $this->Html->div(null, $title, $options, $escape);
		}
	}
/**
 * Returns true if the given result set is not at the first page
 *
 * @param  string $model
 * @return boolean
 */
	function hasPrev($model = null) {
		return $this->__hasPage($model, 'prev');
	}
/**
 * Returns true if the given result set is not at the last page
 *
 * @param  string $model
 * @return boolean
 */
	function hasNext($model = null) {
		return $this->__hasPage($model, 'next');
	}
/**
 * Returns true if the given result set has the page number given by $page
 *
 * @param  string $model
 * @param  int $page
 * @return boolean
 */
	function hasPage($model = null, $page = 1) {
		if (is_numeric($model)) {
			$page = $model;
			$model = null;
		}
		if ($model == null) {
			$model = $this->defaultModel();
		}

		$paging = $this->params($model);
		return $page <= $paging['pageCount'];
	}
/**
 * Protected method
 *
 */
	function __hasPage($model, $page) {
		if ($model == null) {
			$model = $this->defaultModel();
		}
		if (is_array($this->params['paging'][$model])) {
			if ($this->params['paging'][$model]["{$page}Page"] == true) {
				return true;
			}
		}
		return false;
	}
/**
 * Gets the default model of the paged sets
 *
 * @return string
 */
	function defaultModel() {
		if ($this->__defaultModel != null) {
			return $this->__defaultModel;
		}
		$models = array_keys($this->params['paging']);
		$this->__defaultModel = $models[0];
		return $this->__defaultModel;
	}
/**
 * Returns a counter string for the paged result set
 *
 * @param  array $options
 * @return string
 */
	function counter($options = array()) {
		$options = am(
			array(
				'model' => $this->defaultModel(),
				'format' => 'pages',
				'separator' => ' of '
			),
			$options
		);

		$paging = $this->params($options['model']);
		$start = $paging['options']['page'] > 1 ? ($paging['options']['page'] - 1) * ($paging['options']['limit']) + 1 : '1';
		$end = ($paging['count'] < ($start + $paging['options']['limit'] - 1)) ? $paging['count'] : ($start + $paging['options']['limit'] - 1);

		switch ($options['format']) {
			case 'range':
				if (!is_array($options['separator'])) {
					$options['separator'] = array(' - ', $options['separator']);
				}
				$out = $start . $options['separator'][0] . $end . $options['separator'][1] . $paging['count'];
			break;
			case 'pages':
				$out = $paging['options']['page'] . $options['separator'] . $paging['pageCount'];
			break;
			default:
				$replace = array(
					'%page%' => $paging['options']['page'],
					'%pages%' => $paging['pageCount'],
					'%current%' => $paging['current'],
					'%count%' => $paging['count'],
					'%start%' => $start,
					'%end%' => $end
				);
				$out = r(array_keys($replace), array_values($replace), $options['format']);
			break;
		}
		return $this->output($out);
	}
}

?>