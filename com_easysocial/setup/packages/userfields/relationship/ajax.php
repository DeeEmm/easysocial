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

ES::import('admin:/includes/fields/dependencies');

class SocialFieldsUserRelationship extends SocialFieldItem
{
	/**
	 * Approves a request
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function approve()
	{
		$id = $this->input->get('relid', 0, 'int');
		$relation = $this->table('relations');
		$state = $relation->load($id);

		$inputName = $this->input->get('inputName', '', 'default');

		if (!$state) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_RELATIONSHIP_NOT_FOUND'));
		}

		if (!$relation->isTarget()) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_RELATIONSHIP_NOT_TARGET_TO_PERFORM_ACTION'));
		}

		// Need to provide activestep id for notification link
		$stepId = $this->field->step_id;

		$state = $relation->approve($stepId);

		if (!$state) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_RELATIONSHIP_APPROVE_ERROR'));
		}

		$table = $relation->getOppositeTable();
		$targetUser = $relation->getTargetUser();

		$theme = ES::themes();
		$theme->set('targetUser', $targetUser);
		$theme->set('relation', $table);
		$theme->set('inputName', $inputName);
		$output = $theme->output('themes:/fields/user/relationship/relation');

		return $this->ajax->resolve($output);
	}

	/**
	 * Rejects a relationship request 
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function reject()
	{
		$id = $this->input->get('relid', 0, 'int');

		$relation = $this->table('relations');
		$state = $relation->load($id);

		if (!$state) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_RELATIONSHIP_NOT_FOUND'));
		}

		if (!$relation->isTarget()) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_RELATIONSHIP_NOT_TARGET_TO_PERFORM_ACTION'));
		}

		// Need to provide activestep id for notification link
		$stepId = $this->field->step_id;

		$state = $relation->reject($stepId);

		if (!$state) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_RELATIONSHIP_REJECT_ERROR'));
		}

		return $this->ajax->resolve();
	}

	/**
	 * Allows caller to delete a request
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function delete()
	{
		$model = $this->model('relations');
		$userid = $this->input->get('userid', 0, 'int');

		$user = ES::user($userid);

		$relation = $model->getActorRelationship($user->id);

		if (!$relation) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_RELATIONSHIP_NOT_FOUND'));
		}

		if (!$relation->isActor($user->id) && !$relation->isTarget($user->id)) {
			return $this->ajax->reject();
		}

		$state = $relation->remove();

		if (!$state) {
			return $this->ajax->reject(JText::_('PLG_FIELDS_RELATIONSHIP_DELETE_ERROR'));
		}

		return $this->ajax->resolve();
	}
}
