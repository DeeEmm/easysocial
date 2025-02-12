<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('fields:/user/boolean/boolean');

class SocialFieldsPagePhotos extends SocialFieldsUserBoolean
{
	private function canUsePhotos($access)
	{
		if (!$this->appEnabled(SOCIAL_APPS_GROUP_PAGE) || !$access->get('photos.enabled', true) || !$this->config->get('photos.enabled')) {
			return false;
		}

		return true;
	}

	/**
	 * Displays the output form when someone tries to create a page.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onRegister(&$data, &$session)
	{
		$access = ES::access($session->uid, SOCIAL_TYPE_CLUSTERS);

		if (!$this->canUsePhotos($access)) {
			return;
		}

		// Allow author modification during creation
		$allowModify = $this->params->get('allow_modification', true);

		if (!$allowModify) {
			return;
		}
		
		// Get any previously submitted data
		$value = isset($data['photo_albums']) ? $data['photo_albums'] : $this->params->get('default', true);
		$value = (bool) $value;

		// Detect if there's any errors
		$error = $session->getErrors($this->inputName);

		$this->set('error', $error);
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Executes after the page is created
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterBeforeSave(&$data, &$page)
	{
		$access = $page->getAccess();

		if (!$this->canUsePhotos($access)) {
			return;
		}

		// Get the posted value
		$value = isset($data['photo_albums']) ? $data['photo_albums'] : $this->params->get('default', true);
		$value = (bool) $value;

		$registry = $page->getParams();
		$registry->set('photo.albums', $value);

		$page->params = $registry->toString();
	}

	/**
	 * Displays the output form when someone tries to edit a page.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onEdit(&$data, &$page, $errors)
	{
		$access = $page->getAccess();

		if (!$this->canUsePhotos($access)) {
			return;
		}

		$params = $page->getParams();
		$value = $page->getParams()->get('photo.albums', $this->params->get('default', true));
		$error = $this->getError($errors);

		$this->set('error', $error);
		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Executes after the page is created
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditBeforeSave(&$data, &$page)
	{
		$access = $page->getAccess();

		if (!$this->canUsePhotos($access)) {
			return;
		}

		// Get the posted value
		$value = isset($data['photo_albums']) ? $data['photo_albums'] : $page->getParams()->get('photo.albums', $this->params->get('default', true));
		$value = (bool) $value;

		$registry = $page->getParams();
		$registry->set('photo.albums', $value);

		$page->params = $registry->toString();
	}

	/**
	 * Override the parent's onDisplay
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onDisplay($page)
	{
		return;
	}
}
