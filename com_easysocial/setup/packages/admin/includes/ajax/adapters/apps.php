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

class SocialAjaxAdapterApps extends SocialAjaxAdapterAbstract
{
	public function execute($namespace, $parts, $args, $method)
	{
		// We know the second segment is always the element.
		$group = $parts[0];
		$element = $parts[1];
		$type = $parts[2];

		// If this is a view call, it should use the method.
		$classType = $parts[3];

		if ($type == 'controllers') {
			$className = ucfirst($element) . 'Controller' . ucfirst($classType);
			$options = array('group' => $group, 'element' => $element);
			$obj = new $className($options);
		}

		if ($type == 'views') {
			// View calls needs to pass in the app id
			$id = $this->input->get('id', 0, 'int');

			$app = ES::table('App');
			$app->load($id);

			$options = array('app' => $app, 'classType' => $classType);

			$className = ucfirst($element) . 'View' . ucfirst($classType);
			$obj = new $className($options);
		}

		$ajax = ES::ajax();

		if (!method_exists($obj, $method)) {
			$ajax->reject(JText::sprintf('Method %1$s does not exist on the site', $method));
			return $ajax->send();
		}

		if (!empty($args)) {
			return call_user_func_array(array($obj, $method), json_decode($args));
		}

		return $obj->$method();
	}
}
