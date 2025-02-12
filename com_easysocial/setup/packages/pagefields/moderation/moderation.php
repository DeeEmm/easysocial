<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('fields:/user/header/header');

class SocialFieldsPageModeration extends SocialFieldItem
{
	/**
	 * Displays the form for page owner to define permissions
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onRegister(&$post, &$session)
	{
		// Get the posted value if there's any
		$value = !empty($post['stream_moderation']) ? $post['stream_moderation'] : '';

		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Displays the form for page owner to define permissions when page is being edited
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onEdit(&$post, SocialPage &$page, $errors)
	{
		$moderation = $page->getParams()->get('stream_moderation', $this->params->get('stream_moderation', $this->params->get('default', true)));

		$value = !empty($post['stream_moderation']) ? $post['stream_moderation'] : $moderation;
		$value = (bool) $value;

		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Processes the save for new page creation
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$post, &$page)
	{
		return $this->onBeforeSave($post, $page);
	}

	/**
	 * Processes the save for page editing
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, SocialPage &$page)
	{
		return $this->onBeforeSave($post, $page);
	}

	/**
	 * Before the form is saved, we need to store these data into the page properties
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function onBeforeSave(&$post, SocialPage &$page)
	{
		$value = !empty($post['stream_moderation']) ? $post['stream_moderation'] : '';
		$value = (bool) $value;

		// Set it into the page params so that we can retrieve this later
		$params = $page->getParams();
		$params->set('stream_moderation', $value);

		$notification = $this->params->get('notification', false);
		$params->set('stream_moderation_notification', $notification);


		$page->params = $params->toString();

		unset($post['stream_moderation']);
	}
}
