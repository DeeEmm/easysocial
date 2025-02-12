<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('fields:/user/textarea/textarea');

class SocialFieldsGroupType extends SocialFieldItem
{
	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegister(&$post)
	{
		$config = ES::config();

		$value = isset($post['group_type']) ? $post['group_type'] : $this->params->get('default');

		$form = $this->getDropdownForms($value, true);

		$this->set('form', $form);

		return $this->display();
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEdit(&$post, &$group)
	{
		$value = 1;

		if (isset($group)) {
			$value = $group->type;
		}

		$form = $this->getDropdownForms($value);

		$this->set('form', $form);

		return $this->display();
	}

	/**
	 * Get dropdown options
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	private function getDropdownForms($value = null, $registration = null)
	{
		$defaults = array(
				SOCIAL_GROUPS_PUBLIC_TYPE => array(
						SOCIAL_GROUPS_PUBLIC_TYPE, 'PLG_FIELDS_GROUP_TYPE_PUBLIC', 'PLG_FIELDS_GROUP_TYPE_PUBLIC_DESC', 'fa-globe-americas'
					),
				SOCIAL_GROUPS_SEMI_PUBLIC_TYPE => array(
						SOCIAL_GROUPS_SEMI_PUBLIC_TYPE, 'PLG_FIELDS_GROUP_TYPE_SEMI_PUBLIC', 'PLG_FIELDS_GROUP_TYPE_SEMI_PUBLIC_DESC', 'fa-globe-americas'
					),
				SOCIAL_GROUPS_PRIVATE_TYPE => array(
						SOCIAL_GROUPS_PRIVATE_TYPE, 'PLG_FIELDS_GROUP_TYPE_PRIVATE', 'PLG_FIELDS_GROUP_TYPE_PRIVATE_DESC', 'fa-lock'
					),
				SOCIAL_GROUPS_INVITE_TYPE => array(
						SOCIAL_GROUPS_INVITE_TYPE, 'PLG_FIELDS_GROUP_TYPE_INVITE_ONLY', 'PLG_FIELDS_GROUP_TYPE_INVITE_ONLY_DESC', 'fa-envelope'
					)
			);

		$themes = ES::themes();
		$dropdownOptions = $this->params->get('dropdown_options');

		$options = array();
		$keyValue = array();

		if (!$dropdownOptions) {
			$dropdownOptions = array(SOCIAL_GROUPS_PUBLIC_TYPE, SOCIAL_GROUPS_SEMI_PUBLIC_TYPE, SOCIAL_GROUPS_PRIVATE_TYPE, SOCIAL_GROUPS_INVITE_TYPE);
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

		$output = $themes->html('form.popdown', 'group_type', isset($keyValue[$value]) ? $value : $dropdownOptions[0], $options);

		return $output;
	}

	/**
	 * Executes before the group is edited
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onEditBeforeSave(&$data, &$group)
	{
		$type = isset($data['group_type']) ? $data['group_type'] : 1;

		$changed = $group->type != $type;

		if ($changed) {
			$data['group_type_changed'] = true;
		}

		// Set the title on the group
		$group->type = $type;
	}

	/**
	 * Executes after the group is edited
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function onEditAfterSave(&$data, &$group)
	{
		if (empty($data['group_type_changed'])) {
			return true;
		}

		// Need to manually change:
		// 1. Group events type
		// 2. Stream item privacy, including group events - cluster_access

		$db = ES::db();
		$sql = $db->sql();

		// First get all the group events first
		$sql->select('#__social_clusters', 'a');
		$sql->column('a.id');
		$sql->leftjoin('#__social_events_meta', 'b');
		$sql->on('b.cluster_id', 'a.id');
		$sql->where('b.group_id', $group->id);

		$db->setQuery($sql);
		$clusterids = $db->loadColumn();

		if (!empty($clusterids)) {
			$sql->clear();
			$sql->update('#__social_clusters');
			$sql->set('type', $group->type);
			$sql->where('id', $clusterids, 'IN');

			$db->setQuery($sql);
			$db->query();

			// Merge in the group id
			$clusterids[] = $group->id;

			$sql->clear();
			$sql->update('#__social_stream');
			$sql->set('cluster_access', $group->type);
			$sql->where('cluster_id', $clusterids, 'IN');

			$db->setQuery($sql);
			$db->query();
		}

		unset($data['group_type_changed']);
	}

	/**
	 * Executes before the group is created
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function onRegisterBeforeSave(&$data, &$group)
	{
		$type = isset($data['group_type']) ? $data['group_type'] : 1;

		// Set the title on the group
		$group->type = $type;
	}
}
