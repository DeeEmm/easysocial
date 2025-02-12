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

class SocialFieldsGroupModeration extends SocialFieldItem
{
	/**
	 * Displays the form for group owner to define permissions
	 *
	 * @since   1.3
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
	 * Displays the form for group owner to define permissions when group is being edited
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEdit(&$post, SocialGroup &$group, $errors)
	{
		$moderation = $group->getParams()->get('stream_moderation', $this->params->get('stream_moderation', $this->params->get('default', true)));

		$value = !empty($post['stream_moderation']) ? $post['stream_moderation'] : $moderation;
		$value = (bool) $value;

		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Processes the save for new group creation
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$post, &$group)
	{
		return $this->onBeforeSave($post, $group);
	}

	/**
	 * Processes the save for group editing
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, SocialGroup &$group)
	{
		return $this->onBeforeSave($post, $group);
	}

	/**
	 * Before the form is saved, we need to store these data into the group properties
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onBeforeSave(&$post, SocialGroup &$group)
	{
		$value = !empty($post['stream_moderation']) ? $post['stream_moderation'] : '';
		$value = (bool) $value;

		// Set it into the group params so that we can retrieve this later
		$params = $group->getParams();
		$params->set('stream_moderation', $value);

		$notification = $this->params->get('notification', false);
		$params->set('stream_moderation_notification', $notification);

		$group->params = $params->toString();

		unset($post['stream_moderation']);
	}
}
