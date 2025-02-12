<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsEventConfigAllowMaybe extends SocialFieldItem
{
	/**
	 * Displays the field for creation.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegister(&$post, &$session)
	{
		// Get any previously submitted data
		$value = isset($post['allowmaybe']) ? (bool) $post['allowmaybe'] : $this->params->get('default', true);

		// Detect if there's any errors
		$error = $session->getErrors($this->inputName);

		$this->set('error', $error);
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Displays the field for edit.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEdit(&$post, &$cluster, $errors)
	{
		$value = isset($post['allowmaybe']) ? (bool) $post['allowmaybe'] : $cluster->getParams()->get('allowmaybe', $this->params->get('default', true));
		$error = $this->getError($errors);

		$this->set('error', $error);
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Executes before the event is created.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$post, &$cluster)
	{
		return $this->beforeSave($post, $cluster);
	}

	/**
	 * Executes before the event is saved.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, &$cluster)
	{
		return $this->beforeSave($post, $cluster);
	}

	public function beforeSave(&$post, &$cluster)
	{
		// Get the posted value
		$value = isset($post['allowmaybe']) ? (bool) $post['allowmaybe'] : $this->params->get('default', true);

		$registry = $cluster->getParams();
		$registry->set('allowmaybe', $value);

		$cluster->params = $registry->toString();

		unset($post['allowmaybe']);

		return true;
	}
}
