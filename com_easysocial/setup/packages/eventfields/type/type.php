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

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsEventType extends SocialFieldItem
{
	/**
	 * Displays the field for creation.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegister(&$post, &$session)
	{
		// Support for group event
		// If this is a group event, we do not allow user to change the type as the type follows the group
		$reg = ES::registry();
		$reg->load($session->values);

		if ($reg->exists('group_id')) {
			return;
		}

		if ($reg->exists('page_id')) {
			return;
		}

		$value = isset($post['event_type']) && $post['event_type'] ? $post['event_type'] : $this->params->get('default');

		$form = $this->getDropdownForms($value, true);

		$this->set('form', $form);

		return $this->display();
	}

	/**
	 * Displays the field for edit.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEdit(&$post, &$cluster, $error)
	{
		// Support for group/page event
		// If this is a group/page event, we do not allow user to change the type as the type follows the group/page
		if ($cluster->isClusterEvent()) {
			return;
		}

		$value = isset($post['event_type']) && $post['event_type'] ? $post['event_type'] : $cluster->type;

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
				SOCIAL_EVENT_TYPE_PUBLIC => array(
						SOCIAL_EVENT_TYPE_PUBLIC, 'FIELDS_EVENT_TYPE_PUBLIC', 'FIELDS_EVENT_TYPE_PUBLIC_DESC', 'fa-globe-americas'
					),
				SOCIAL_EVENT_TYPE_PRIVATE => array(
						SOCIAL_EVENT_TYPE_PRIVATE, 'FIELDS_EVENT_TYPE_PRIVATE', 'FIELDS_EVENT_TYPE_PRIVATE_DESC', 'fa-lock'
					),
				SOCIAL_EVENT_TYPE_INVITE => array(
						SOCIAL_EVENT_TYPE_INVITE, 'FIELDS_EVENT_TYPE_INVITE_ONLY', 'FIELDS_EVENT_TYPE_INVITE_ONLY_DESC', 'fa-envelope'
					)
			);

		$themes = ES::themes();
		$dropdownOptions = $this->params->get('dropdown_options');

		$options = array();
		$keyValue = array();

		if (!$dropdownOptions) {
			$dropdownOptions = array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE, SOCIAL_EVENT_TYPE_INVITE);
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

		$output = $themes->html('form.popdown', 'event_type', isset($keyValue[$value]) ? $value : $dropdownOptions[0], $options);

		return $output;
	}


	/**
	 * Executes before the event is created
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onRegisterBeforeSave(&$post, &$event)
	{
		$this->processBeforeSave($post, $event);
	}

	/**
	 * Executes before the event is created.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function onEditBeforeSave(&$post, &$event)
	{
		$this->processBeforeSave($post, $event);
	}

	/**
	 * Executes before the event is created at backend
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function onAdminBeforeSave(&$post, &$event)
	{
		$this->processBeforeSave($post, $event);
	}

	/**
	 * Process event type before save
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	private function processBeforeSave(&$post, &$event)
	{
		// Currently, the type always follow group/page type
		// There is a separate checking where user must be group/page member to join the event
		if ($event->isClusterEvent()) {
			$event->type = $event->getCluster()->type;
			unset($post['event_type']);

			return;
		}

		$defaultType = $this->params->get('default');

		// original type
		if ($event->id) {
			$defaultType = $event->type ? $event->type : $defaultType;
		}

		// If default type is still empty, we just assume the type is public
		if (!$defaultType) {
			$defaultType = SOCIAL_EVENT_TYPE_PUBLIC;
		}

		$type = isset($post['event_type']) && $post['event_type'] ? $post['event_type'] : $defaultType;

		$event->type = $type;

		unset($post['event_type']);
	}
}
