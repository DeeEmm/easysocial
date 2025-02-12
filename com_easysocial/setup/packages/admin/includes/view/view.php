<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialView
{
	static $views = array();

	public static function getView($name, $backend = true)
	{
		$class = 'EasySocialView' . ucfirst($name);
		$doc = JFactory::getDocument();

		if (!isset(self::$views[$class]) || (!self::$views[$class] instanceof EasySocialView)) {
			$path = $backend ? SOCIAL_ADMIN : SOCIAL_SITE;

			if (!class_exists($class)) {
				$path = $path . '/views/' . strtolower( $name ) . '/view.' . $doc->getType() . '.php';

				if (!JFile::exists($path)) {
					return false;
				}

				require_once($path);
			}

			$config = [
				'base_path' => $path . '/views/' . strtolower($name)
			];

			self::$views[$class] = new $class($config);
		}

		return self::$views[$class];
	}


	public static function getInstance($viewName, $backend = true)
	{
		if (!isset(self::$views[$viewName][$backend])) {
			$view = self::getView($viewName, $backend);

			self::$views[$viewName][$backend] = $view;
		}

		return self::$views[$viewName][$backend];
	}
}
