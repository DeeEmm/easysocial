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

ES::import('fields:/user/textbox/textbox');

class SocialFieldsPageTitle extends SocialFieldsUserTextbox
{
	/**
	 * Executes before the page is created.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$data, &$cluster)
	{
		$title = !empty($data[$this->inputName]) ? $data[$this->inputName] : '';

		// Set the title on the page
		$model = ES::model('Clusters');
		$cluster->title = $model->getUniqueTitle($title, SOCIAL_TYPE_PAGE);

		unset($data[$this->inputName]);
	}

	/**
	 * Executes before the page is save.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onEditBeforeSave(&$data, &$cluster)
	{
		$title = !empty($data[$this->inputName]) ? $data[$this->inputName] : '';

		// Set the title on the page
		$model = ES::model('Clusters');
		$cluster->title = $model->getUniqueTitle($title, SOCIAL_TYPE_PAGE, $cluster->id);

		unset($data[$this->inputName]);
	}

	/**
	 * Executes before the page is save.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onAdminEditBeforeSave(&$data, &$cluster)
	{
		$title = !empty($data[$this->inputName]) ? $data[$this->inputName] : '';

		// Set the title on the page
		$model = ES::model('Clusters');
		$cluster->title = $model->getUniqueTitle($title, SOCIAL_TYPE_PAGE, $cluster->id);

		unset($data[$this->inputName]);
	}

	/**
	 * Displays the page title textbox.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onEdit(&$post, &$cluster, $errors)
	{
		// The value will always be the page title
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $cluster->getName();

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Displays the page description textbox.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onAdminEdit(&$post, &$cluster, $errors)
	{
		$clusterName = JText::_($this->params->get('default'), true);

		if ($cluster->id) {
			$clusterName = $cluster->getName();
		}

		// The value will always be the page title
		$value = !empty($post[$this->inputName]) ? $post[$this->inputName] : $clusterName;

		// Get the error.
		$error = $this->getError($errors);

		// Set the value.
		$this->set('value', $this->escape($value));
		$this->set('error', $error);

		return $this->display();
	}

	/**
	 * Responsible to output the html codes that is displayed to a user.
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onDisplay($cluster)
	{
		$this->value = $cluster->getName();

		return parent::onDisplay($cluster);
	}

	/**
	 * Trigger to get this field's value for various purposes.
	 *
	 * @since  3.1.0
	 * @access public
	 */
	public function onGetValue($cluster)
	{
		return $cluster->getName();
	}
}
