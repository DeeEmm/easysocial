<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsEventPhotos extends SocialFieldItem
{
	private function canUsePhotos($access)
	{
		if (!$this->appEnabled(SOCIAL_APPS_GROUP_EVENT) || !$access->allowed('photos.enabled', true) || !$this->config->get('photos.enabled')) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if the app published or unpublished
	 *
	 * @since   2.1.8
	 * @access  public
	 */
	private function appEnabled($groupType)
	{
		// We need to know if the app is published
		$app = ES::table('App');
		$app->load( array('group' => $groupType, 'element' => 'photos', 'type' => 'apps'));

		// If app has been unpublished, skip this field altogether
		if (!$app->id || !$app->state) {
			return false;
		}

		return true;
	}

	/**
	 * Displays the field for creation.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegister(&$post, &$session, SocialTableEventCategory $category)
	{
		$access = $category->getAcl();

		if (!$this->canUsePhotos($access)) {
			return;
		}

		// Check if they are allowed to create albums in this event's category
		$acl = $category->getAcl();

		if (!$acl->get('photos.enabled')) {
			return;
		}

		// Get any previously submitted data
		$value = isset($post['photo_albums']) ? $post['photo_albums'] : $this->params->get('default');
		$value = (bool) $value;

		// Detect if there's any errors
		$error = $session->getErrors($this->inputName);

		$this->set('error'  , $error);
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
		$access = $cluster->getAccess();
		
		if (!$this->canUsePhotos($access)) {
			return;
		}

		// Get the posted value
		$value = isset($post['photo_albums']) ? $post['photo_albums'] : $this->params->get('default');
		$value = (bool) $value;

		$registry = $cluster->getParams();
		$registry->set('photo.albums', $value);

		$cluster->params = $registry->toString();

		unset($post['photo_albums']);
	}

	/**
	 * Displays the field for edit.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEdit(&$post, &$cluster, $errors)
	{
		$access = $cluster->getAccess();

		if (!$this->canUsePhotos($access)) {
			return;
		}

		$value = isset($post['photo_albums']) ? $post['photo_albums'] : $cluster->getParams()->get('photo.albums', $this->params->get('default'));
		$error = $this->getError($errors);

		$this->set('error'  , $error);
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Executes before the event is saved.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, &$cluster)
	{
		$access = $cluster->getAccess();

		if (!$this->canUsePhotos($access)) {
			return;
		}

		// Get the posted value
		$value = isset($post['photo_albums']) ? $post['photo_albums'] : $cluster->getParams()->get('photo.albums', $this->params->get('default'));
		$value = (bool) $value;

		$registry = $cluster->getParams();
		$registry->set('photo.albums', $value);

		$cluster->params = $registry->toString();

		unset($post['photo_albums']);
	}
	
	/**
	 * Override the parent's onDisplay to not show this field.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onDisplay($cluster)
	{
		return;
	}
}
