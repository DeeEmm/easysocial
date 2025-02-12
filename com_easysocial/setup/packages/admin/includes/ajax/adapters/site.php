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

class SocialAjaxAdapterSite extends SocialAjaxAdapterAbstract
{
	public function execute($namespace, $parts, $args, $method)
	{
		$allowed = array('views', 'controllers');

		$type = $parts[0];
		$name = $parts[1];

		if (!in_array($type, $allowed)) {
			throw ES::exception(JText::sprintf('Invalid AJAX request. Request of type %1$s is not supported.', $type), 500);
		}

		if ($type == 'views') {
			$className = 'EasySocialView' . preg_replace('/[^A-Z0-9_]/i', '', $name);
			$obj = new $className();
		}

		if ($type == 'controllers') {
			$className = 'EasySocialController' . preg_replace('/[^A-Z0-9_]/i', '', $name);
			$obj = new $className();
		}

		// When lock down is enabled, and the user isn't logged in, ensure that the user is really allowed to view ajax stuffs
		if ($this->config->get('general.site.lockdown.enabled') && $this->my->guest && method_exists($obj, 'lockdown') && $obj->lockdown()) {
			$this->ajax->script('EasySocial.login();');
			return $this->ajax->send();
		}

		// For controllers, use standard execute method
		if ($type == 'controllers') {
			return $obj->execute($method);
		}

		// If the method doesn't exist in this object, we know something is wrong.
		if (!method_exists($obj, $method)) {
			$this->ajax->reject(JText::sprintf('Method %1s does not exist', $method));
			return $this->ajax->send();
		}

		// When arguments are provided, we provide them as func arguments
		if (!empty($args)) {
			return call_user_func_array(array($obj, $method), json_decode($args));
		}

		return $obj->$method();
	}
}
