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

ES::import('fields:/user/textarea/textarea');

class SocialFieldsPageType extends SocialFieldItem
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

		$value = isset($post['page_type']) ? $post['page_type'] : $this->params->get('default');

		$form = $this->getDropdownForms($value, true);

		$this->set('form', $form);

		return $this->display();
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onEdit(&$post , &$page)
	{
		$value = 1;

		if (isset($page)) {
			$value = $page->type;
		}

		$form = $this->getDropdownForms($value);

		$this->set('form', $form);

		return $this->display();
	}

	/**
	 * Get dropdown optsions
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	private function getDropdownForms($value = null, $registration = null)
	{
		$defaults = array(
				SOCIAL_PAGES_PUBLIC_TYPE => array(
						SOCIAL_PAGES_PUBLIC_TYPE, 'PLG_FIELDS_PAGE_TYPE_PUBLIC', 'PLG_FIELDS_PAGE_TYPE_PUBLIC_DESC', 'fa-globe-americas'
					),
				SOCIAL_PAGES_PRIVATE_TYPE => array(
						SOCIAL_PAGES_PRIVATE_TYPE, 'PLG_FIELDS_PAGE_TYPE_PRIVATE', 'PLG_FIELDS_PAGE_TYPE_PRIVATE_DESC', 'fa-lock'
					),
				SOCIAL_PAGES_INVITE_TYPE => array(
						SOCIAL_PAGES_INVITE_TYPE, 'PLG_FIELDS_PAGE_TYPE_INVITE_ONLY', 'PLG_FIELDS_PAGE_TYPE_INVITE_ONLY_DESC', 'fa-envelope'
					)
			);

		$themes = ES::themes();
		$dropdownOptions = $this->params->get('dropdown_options');

		$options = array();
		$keyValue = array();

		if (!$dropdownOptions) {
			$dropdownOptions = array(SOCIAL_PAGES_PUBLIC_TYPE, SOCIAL_PAGES_PRIVATE_TYPE, SOCIAL_PAGES_INVITE_TYPE);
		}

		// Merge the value options for existing group
		if ($value && $value !== null && !$registration) {
			$exists = false;

			foreach ($dropdownOptions as $data) {

				// Options exists
				if ($value == $data) {
					$exists = true;
					break;
				}
			}

			if (!$exists) {
				$dropdownOptions[] = $value;
			}
		}

		foreach ($dropdownOptions as $option) {
			$params = $defaults[$option];
			$i = 0;

			foreach ($params as $key => $param) {
				$arg[$i] = $param;
				$i++;
			}

			$keyValue[$option] = true;

			$options[] = $themes->html('form.popdownOption', $arg[0], $arg[1], $arg[2], $arg[3], [], 'javascript:void(0);', '', ['increasedPadding' => true]);
		}

		$output = $themes->html('form.popdown', 'page_type', isset($keyValue[$value]) ? $value : $dropdownOptions[0], $options);

		return $output;
	}

	/**
	 * Executes before the page is created
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onEditBeforeSave(&$data , &$page)
	{
		$type = isset($data['page_type']) ? $data['page_type'] : 1;

		$changed = $page->type != $type;

		if ($changed) {
			$data['page_type_changed'] = true;
		}

		// Set the title on the page
		$page->type = $type;
	}

	public function onEditAfterSave(&$data, &$page)
	{
		if (empty($data['page_type_changed'])) {
			return true;
		}

		// Need to manually change:
		// 1. page events type
		// 2. Stream item privacy, including page events - cluster_access

		// Put on hold for this first
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
			$sql->set('type', $page->type);
			$sql->where('id', $clusterids, 'IN');

			$db->setQuery($sql);
			$db->query();

			// Merge in the page id
			$clusterids[] = $page->id;

			$sql->clear();
			$sql->update('#__social_stream');
			$sql->set('cluster_access', $page->type);
			$sql->where('cluster_id', $clusterids, 'IN');

			$db->setQuery($sql);
			$db->query();
		}

		unset($data['page_type_changed']);
	}

	/**
	 * Executes before the page is created
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onRegisterBeforeSave(&$data , &$page)
	{
		$type = isset($data['page_type']) ? $data['page_type'] : $this->params->get('default');

		// Set the title on the page
		$page->type = $type;
	}

	/**
	 * Perform necessary fixes to the page type if it isn't visible on the page
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function onCustomPageBeforeSave(&$data, &$page)
	{
		// This field is already visible on the creation form, we shouldn't fiddle with the value
		if ($this->params->get('visible_registration')) {
			return;
		}

		$type = isset($data['page_type']) ? $data['page_type'] : $this->params->get('default');
		$page->type = $type;
	}
}
