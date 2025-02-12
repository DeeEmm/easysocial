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
	public function onValidate($post)
	{
		return true;
	}

	/**
	 * Renders when user edits their profile
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onEdit(&$post, &$user, $errors)
	{
		if (!$user->id) {
			return false;
		}

		$model = $this->model('relations');
		$relation = $model->getActorRelationship($user->id);
		$requests = $model->getTargetRelationship($user->id, array('state' => 0));
		$types = $this->getRelationshipTypes($relation);

		// Defines the target avatar picture
		$targetUser = '';

		if ($relation && $relation->id) {
			$relationshipType = $types[$relation->type];

			$targetUser = $relation->getActorUser();
		}

		// Get field error
		$error = $this->getError($errors);

		$this->set('error', $error);
		$this->set('targetUser', $targetUser);
		$this->set('user', $user);
		$this->set('relation', $relation);
		$this->set('requests', $requests);
		$this->set('types', $types);

		return $this->display();
	}

	/**
	 * Triggered before saving
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onEditBeforeSave(&$post, &$user)
	{
		$params = json_decode($post[$this->inputName]);

		// Check if the type is exists from the form
		if (!isset($params->type)) {
			if (isset($params->typeRelation)) {
				$params->type = $params->typeRelation;
			}
		}

		// If stil not exists, try to get from the target
		if (!isset($params->type)) {

			if (!isset($params->target)) {
				if (isset($params->targetRelation)) {
					$params->target = $params->targetRelation;
				} else {
					return false;
				}
			}

			// Try to get the relation type so we can save the data correctly.
			$model = $this->model('relations');
			$relation = $model->getActorRelationship($user->id);

			if ($relation === false) {
				return false;
			}

			// assign relation type
			$params->type = $relation->type;

			$post[$this->inputName] = json_encode($params);
		}
	}

	/**
	 * Triggered after saving a user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onEditAfterSave(&$post, &$user)
	{
		// post data not found. skip.
		if (!isset($post[$this->inputName])) {
			return false;
		}

		$params = json_decode($post[$this->inputName]);

		if (!isset($params->type)) {
			return false;
		}

		// We set it as array here to follow the standard of textboxlist passing in target as array even for single target
		if (!isset($params->target)) {
			$params->target = array(0);
		}

		$model = $this->model('relations');
		$relation = $model->getActorRelationship($user->id);

		// If no relationship data is found, then we init a new one
		if ($relation === false) {
			$relation = $this->table('relations');
		}

		$origType = $relation->type;
		$origTarget = $relation->getTargetUser()->id;
		$currentType = $params->type;

		// Do not use $relation->isConnect because the type might change
		$typeInfo = $relation->getType($currentType);

		$currentTarget = $typeInfo && $typeInfo->connect ? $params->target[0] : 0;

		$relationshipTypes = $this->getRelationshipTypes();
		$relationshipType = $relationshipTypes[$currentType];

		// If the current target is 0, we should skip this altogether
		if (!$currentTarget && $relationshipType->connect) {
			return false;
		}

		// Only process if there is a change in type or target
		if ($origType !== $currentType || $origTarget !== $currentTarget) {

			// If original target is not empty, we need to find the target's relationship and change it to empty target since this person is no longer tied to that target
			if (!empty($origTarget)) {
				$targetRel = $model->getActorRelationship($origTarget, array('target' => $user->id));

				if ($targetRel) {
					$targetRel->target = 0;
					$targetRel->state = 1;
					$targetRel->store();
				}
			}

			// If this relationship has an id, means it is from an existing record.
			// We need to delete and recreate it in order to have a new id.
			// When the target approves, the genereted stream needs to use the new id instead of the old id.

			if (!empty($relation->id)) {
				$relation->remove();
				$relation = $this->table('relations');
			}

			$relation->actor = $user->id;
			$relation->type = $currentType;
			$relation->target = $currentTarget;

			// Need to provide activestep id for notification link
			$stepId = $this->field->step_id;

			$state = $relation->request($stepId);

			if (!$state) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Executes before a user's edit is save in admin more
	 *
	 * @since	2.0.11
	 * @access	public
	 */
	public function onAdminEditBeforeSave(&$post, &$user)
	{
		// Regardless of readonly parameter, we allow admin to edit this field
		return true;
	}

	/**
	 * Generates the output when a user's profile info is being viewed
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function onDisplay($user)
	{
		if (!$this->allowedPrivacy($user)) {
			return;
		}

		$model = $this->model('relations');
		$relation = $model->getActorRelationship($user->id);

		if (!$relation) {
			return;
		}

		// Don't display anything when it's pending
		if ($relation->isPending()) {
			return;
		}

		// For "na" type, we shouldn't display anything
		if ($relation && $relation->type == 'na') {
			return;
		}

		// Linkage to advanced search page.
		$field = $this->field;
		$search = false;

		if ($field->type == SOCIAL_FIELDS_GROUP_USER && $field->searchable) {
			$params = array( 'layout' => 'advanced' );
			$params['criterias[]'] = $field->unique_key . '|' . $field->element;
			$params['operators[]'] = 'equal';
			$params['conditions[]'] = $relation->getType()->value;

			$search = ESR::search($params);
		}

		$this->set('search', $search);
		$this->set('relation', $relation);

		return $this->display();
	}

	/**
	 * Returns formatted value for GDPR
	 *
	 * @since  2.2
	 * @access public
	 */
	public function onGDPRExport($user)
	{
		$model = $this->model('relations');
		$relation = $model->getActorRelationship($user->id);

		if (!$relation) {
			return '';
		}

		// Don't display anything when it's pending
		if ($relation->isPending()) {
			return '';
		}

		// For "na" type, we shouldn't display anything
		if ($relation && $relation->type == 'na') {
			return '';
		}

		// retrieve field data
		$field = $this->field;

		// retrieve formatted value
		$formattedValue = $relation->getSentence('view');

		$data = new stdClass;
		$data->fieldId = $field->id;
		$data->value = strip_tags($formattedValue);

		return $data;
	}

	/**
	 * Generates a list of relationship types
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function getRelationshipTypes($currentRelation = null)
	{
		// Get all the relationship types and key it with the name
		$allowedTypes = $this->params->get('relationshiptype', array());
		$types = $this->field->getApp()->getManifest('config')->relationshiptype->option;

		$result = array();


		foreach ($types as $type) {

			if (empty($allowedTypes) || (!empty($allowedTypes) && in_array($type->value, $allowedTypes))) {

				$type->selected = $currentRelation && $currentRelation->type == $type->value ? true : false;
				$type->label = JText::_($type->label);
				$type->connectword = JText::_('PLG_FIELDS_RELATIONSHIP_CONNECT_WORD_' . strtoupper($type->value));

				$result[$type->value] = $type;
			}
		}

		return $result;
	}

	public function onOAuthGetMetaFields(&$fields)
	{
		$fields[] = 'relationship_status';
	}

	public function onEditValidate(&$post, &$user)
	{
		$data = !empty($post[$this->inputName]) ? $post[$this->inputName] : '';

		return $this->validateRelation($data, $user);
	}

	public function validateRelation($data, $user)
	{
		$data = json_decode($data);
		$allowed = array('na', 'single', 'widowed', 'separated', 'divorced', 'relationshipnotarget', 'engagednotarget', 'marriednotarget', 'complicatednotarget');

		// User did not set any relationship status
		if (!isset($data)) {
			return true;
		}

		// Check if the type is exists from the form
		if (!isset($data->type)) {
			if (isset($data->typeRelation)) {
				$data->type = $data->typeRelation;
			}
		}

		if (!isset($data->type)) {
			return true;
		}

		// single and NA is not require to further check on the target.
		if (in_array($data->type, $allowed)) {
			return true;
		}

		// If reached here means the type is exists but there is no target
		if (!isset($data->target)) {
			$this->setError(JText::_('COM_EASYSOCIAL_FIELD_RELATIONSHIP_TARGET_INVALID'));
			return false;
		}

		// check if target is exists on the site.
		$user = ES::user($data->target[0]);

		// Target not exists. Let's tell the user
		if (!$user->id) {
			$this->setError(JText::_('COM_EASYSOCIAL_FIELD_RELATIONSHIP_TARGET_INVALID'));
			return false;
		}

		return true;
	}
}
