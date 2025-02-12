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

ES::import('fields:/user/textarea/textarea');

class SocialFieldsPageNotification extends SocialFieldItem
{
	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onRegister(&$post)
	{
		$config = ES::config();

		$value = isset($post['page_notification']) ? $post['page_notification'] : $this->params->get('default');

		$this->set('value', $value);

		return $this->display();
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onEdit(&$post, &$page)
	{
		$value = 1;

		if (isset($page) && $page->notification == 1) {
			$value = 1;
		}
		if (isset($page) && $page->notification == 2) {
			$value = 2;
		}
		if (isset($page) && $page->notification == 3) {
			$value = 3;
		}
		if (isset($page) && $page->notification == 4) {
			$value = 4;
		}

		$this->set('value', $value);
		return $this->display();
	}

	/**
	 * Executes before the page is created
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onEditBeforeSave(&$data , &$page)
	{
		$notification = isset($data['page_notification']) ? $data['page_notification'] : 1;

		$changed = $page->notification != $notification;

		if ($changed) {
			$data['page_notification_changed'] = true;
		}

		// Set the title on the page
		$page->notification = $notification;
	}

	public function onEditAfterSave(&$data, &$page)
	{
		if (empty($data['page_notification_changed'])) {
			return true;
		}

		$db = ES::db();
		$sql = $db->sql();

		// First get all the page events first
		$sql->select('#__social_clusters', 'a');
		$sql->column('a.id');
		$sql->leftjoin('#__social_events_meta', 'b');
		$sql->on('b.cluster_id', 'a.id');
		$sql->where('b.page_id', $page->id);

		$db->setQuery($sql);
		$clusterids = $db->loadColumn();

		if (!empty($clusterids)) {
			$sql->clear();
			$sql->update('#__social_clusters');
			$sql->set('notification', $page->notification);
			$sql->where('id', $clusterids, 'IN');

			$db->setQuery($sql);
			$db->query();

			// Merge in the page id
			$clusterids[] = $page->id;

			$sql->clear();
		}

		unset($data['page_notification_changed']);
	}

	/**
	 * Executes before the page is created
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onRegisterBeforeSave(&$data , &$page)
	{
		$notification = isset($data['page_notification']) ? $data['page_notification'] : 1;

		// Set the title on the page
		$page->notification = $notification;
	}
}
