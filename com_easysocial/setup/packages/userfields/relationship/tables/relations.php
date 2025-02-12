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

class SocialFieldTableUserRelations extends JTable
{
	public $id = null;
	public $actor = null;
	public $target = null;
	public $type = null;
	public $state = null;
	public $created = null;

	// Extended data
	private $typeInfo = null;

	public function __construct(& $db)
	{
		parent::__construct('#__social_relationship_status', 'id', $db);
	}

	/**
	 * Stores the relationship data.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function store($updateNulls = false)
	{
		$db	= ES::db();

		if (is_null($this->created)) {
			$this->created	= ES::date()->toSql();
		}

		return parent::store($updateNulls);
	}

	/**
	 * Checks if the user is the actor in this relationship.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function isActor($userid = null)
	{
		if (is_null($userid)) {
			$userid = ES::user()->id;
		}

		return $this->actor == $userid;
	}

	/**
	 * Checks if the user is the target in this relationship.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function isTarget($userid = null)
	{
		if (is_null($userid)) {
			$userid = ES::user()->id;
		}

		return $this->target == $userid;
	}

	/**
	 * Returns the user object of the actor in this relationship.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function getActorUser()
	{
		// If no actor, then we use the current user
		if (empty($this->actor)) {
			return ES::user();
		}

		return ES::user($this->actor);
	}

	/**
	 * Returns the user object of the target in this relationship.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function getTargetUser()
	{
		// If no target, then we use guest user
		if (empty($this->target)) {
			return ES::user(0);
		}

		return ES::user($this->target);
	}

	/**
	 * Returns the opposite side based on the user id passed in is actor or target.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function getOppositeUser($userid = null)
	{
		$oppositeId = $this->isActor() ? $this->target : $this->actor;

		if (empty($oppositeId)) {
			return false;
		}

		$oppositeUser = ES::user($oppositeId);

		return $oppositeUser;
	}

	/**
	 * Checks if this relationship is in pending state.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function isPending()
	{
		return $this->state == 0;
	}

	/**
	 * Checks if this relationship has been approved.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function isApproved()
	{
		return !$this->isPending();
	}

	/**
	 * Checks if this relationship involves a 2nd party.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function isConnect()
	{
		$type = $this->getType();

		if (!$type) {
			return false;
		}

		return $type->connect;
	}

	/**
	 * Returns the label string of the relationship type if this relationship involves a 2nd party.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function getLabel()
	{
		$type = $this->getType();

		if (!$type) {
			return false;
		}

		return $type->label;
	}

	/**
	 * Retrieves the relationship sentence
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getSentence($perspective = 'edit', $searchable = false)
	{
		$targetUser = $this->getTargetUser();

		$text = 'PLG_FIELDS_RELATIONSHIP_SENTENCE_' . strtoupper($this->type) . '_' . strtoupper($perspective);

		$sentence = JText::sprintf($text, $targetUser->getName(), $targetUser->getPermalink());

		if ($searchable) {
			$text = 'PLG_FIELDS_RELATIONSHIP_SENTENCE_' . strtoupper($this->type) . '_' . strtoupper($perspective) . '_SEARCHABLE';
			$sentence = JText::sprintf($text, $searchable, $targetUser->getPermalink(), $targetUser->getName());
		}

		return $sentence;
	}

	/**
	 * Returns the connecting word of the relationship type if this relationship involves a 2nd party.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function getConnectWord()
	{
		$type = $this->getType();

		if (!$type)
		{
			return false;
		}

		return $type->connectword;
	}

	/**
	 * Returns the coupling table object of this relationship if it involves 2nd party.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function getOppositeTable()
	{
		if (!$this->isConnect() || empty($this->actor) || empty($this->target)) {
			return false;
		}

		$table = JTable::getInstance('relations', 'SocialFieldTableUser');
		$state = $table->load(array('actor' => $this->target, 'target' => $this->actor));

		if (!$state) {
			return false;
		}

		return $table;
	}

	/**
	 * Proxy function to store the relationship and carry out any necessary action on requesting a relationship.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function request($stepId = null)
	{
		$this->state = $this->isConnect() && !empty($this->target) ? 0 : 1;

		$state = $this->store();

		if (!$state) {
			return false;
		}

		// Send request notification if the relationship state is 0 (not approved)
		if (!$this->state) {
			$this->notify('request', $this->actor, $this->target, $stepId);
		}

		return true;
	}

	/**
	 * Deletes a relationship between target and actor (both rows)
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteRelationship($userId)
	{
		$db = ES::db();

		// Get a list of records if there are any
		$query = array();
		$query[] = 'SELECT `id` FROM ' . $db->qn($this->getTableName());
		$query[] = 'WHERE (';
		$query[] = $db->qn('actor') . '=' . $db->Quote($userId);
		$query[] = 'OR';
		$query[] = $db->qn('target') . '=' . $db->Quote($userId);
		$query[] = ')';
		$query[] = 'AND ' . $db->qn('id') . ' != ' . $db->Quote($this->id);

		$query = implode(' ', $query);
		$db->setQuery($query);
		$items = $db->loadColumn();

		if (!$items) {
			return false;
		}

		$query = array();
		$query[] = 'DELETE FROM ' . $db->qn($this->getTableName());
		$query[] = 'WHERE ' . $db->qn('id') . ' IN(' . implode(',', $items) . ')';
		$query = implode(' ', $query);

		$db->setQuery($query);
		$db->Query();

		// Remove from the stream
		foreach ($items as $id) {
			ES::stream()->delete($id, 'relationship');
		}

		return true;
	}

	/**
	 * Allows caller to approve a relationship request
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function approve($stepId = null)
	{
		$this->state = 1;

		$state = $this->store();

		if (!$state) {
			return false;
		}

		// Remove previous relationships if there are any
		$this->deleteRelationship($this->target);

		// After clearing all other relationship status, we need to create the same relationship status for target user as an actor
		$table = JTable::getInstance('relations', 'SocialFieldTableUser');
		$table->actor = $this->target;
		$table->target = $this->actor;
		$table->state = 1;
		$table->type = $this->type;

		$state = $table->store();

		if ($state) {
			// Send notification to the original actor
			$this->notify('approve', $this->target, $this->actor, $stepId);

			// Create a stream item
			// one stream for initiator
			$stream = ES::stream();
			$streamTemplate = $stream->getTemplate();

			$streamTemplate->setActor($this->actor, SOCIAL_TYPE_USER);
			$streamTemplate->setTarget($this->target, SOCIAL_TYPE_USER);
			$streamTemplate->setContext($this->id, 'relationship');
			$streamTemplate->setVerb('approve');
			$streamTemplate->setParams((object) array('type' => $this->type));

			$streamTemplate->setAccess('core.view');
			$stream->add($streamTemplate);


			// another stream for person who approve the relationship
			$streamTemplate = $stream->getTemplate();

			$streamTemplate->setActor($this->target, SOCIAL_TYPE_USER);
			$streamTemplate->setTarget($this->actor, SOCIAL_TYPE_USER);
			$streamTemplate->setContext($this->id, 'relationship');
			$streamTemplate->setVerb('approve');
			$streamTemplate->setParams((object) array('type' => $this->type));

			$streamTemplate->setAccess('core.view');
			$stream->add($streamTemplate);
		}

		return true;
	}

	/**
	 * Proxy function to store the relationship and carry out any necessary action on rejecting a relationship.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function reject($stepId = null)
	{
		// Once a request is rejected, just delete the request since there is no point keeping this record.
		$originalTarget = $this->target;

		$state = $this->delete();

		// Send reject notification to the original actor
		if ($state) {
			$this->notify('reject', $originalTarget, $this->actor, $stepId);
		}

		return true;
	}

	/**
	 * Proxy function to remove the relationship and carry out any necessary action on removing a relationship.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function remove()
	{
		$state = $this->delete();

		if (!$state) {
			return false;
		}

		// Send notification here

		// Delete stream
		// It is possible that there are 2 records that needs to be searched
		// TODO: need verb
		ES::stream()->delete($this->id, 'relationship');

		$opposite = $this->getOppositeTable();

		if ($opposite) {
			$opposite->delete();
			ES::stream()->delete($opposite->id, 'relationship');
		}

		return true;
	}

	/**
	 * Returns the information of this relationship type.
	 *
	 * @since  1.0
	 * @access public
	 * @return Object    Standard class that contains information regarding this relationship type.
	 */
	public function getType($type = null)
	{
		static $types = null;

		if (is_null($types)) {
			$app = ES::table('app');
			$state = $app->loadByElement('relationship', SOCIAL_APPS_GROUP_USER, SOCIAL_APPS_TYPE_FIELDS);

			if (!$state) {
				return false;
			}

			$types = $app->getManifest('config')->relationshiptype->option;

			$total = count($types);
			for ($i = 0; $i < $total; $i++) {
				$types[$i]->label = JText::_($types[$i]->label);
				$types[$i]->connectword = JText::_('PLG_FIELDS_RELATIONSHIP_CONNECT_WORD_' . strtoupper($types[$i]->value));
			}
		}

		// If $type is not empty means we are trying to manually get a type
		if (!empty($type)) {
			foreach ($types as $t) {
				if ($type == $t->value) {
					return $t;
				}
			}

			return false;
		}

		if (!isset($this->typeInfo)) {
			$this->typeInfo = false;

			foreach ($types as $t) {
				if ($this->type == $t->value) {
					$this->typeInfo = $t;
					break;
				}
			}
		}

		return $this->typeInfo;
	}

	/**
	 * Shorthand function to send notification for various actions.
	 *
	 * @since   2.1
	 * @access  public
	 */
	private function notify($verb, $actor, $target, $stepId = null)
	{
		$actor = ES::user($actor);
		$target = ES::user($target);

		$linkOptions = [
			'layout' => 'edit',
			'external' => true
		];

		if ($stepId) {
			$linkOptions['activeStep'] = $stepId;
		}

		$emailOptions = [
			'title' => 'PLG_FIELDS_RELATIONSHIP_EMAIL_TITLE_' . strtoupper($verb),
			'template' => 'fields/user/relationship/' . $verb,
			'actor' => $actor->getName(),
			'posterName' => $actor->getName(),
			'posterAvatar' => $actor->getAvatar(),
			'posterLink' => $actor->getPermalink(true, true),
			'recipientName' => $target->getName(),
			'type' => $this->type,
			'link' => ESR::profile($linkOptions, true)
		];

		$systemLinkOptions = [
			'layout' => 'edit',
			'sef' => false
		];

		if ($stepId) {
			$systemLinkOptions['activeStep'] = $stepId;
		}

		$systemOptions = [
			'uid' => $this->id,
			'actor_id' => $actor->id,
			'type' => 'relationship',
			'title' => JText::sprintf('APP_USER_RELATIONSHIP_NOTIFICATION_TITLE_' . strtoupper($verb), $actor->getName()),
			'url' => ESR::profile($systemLinkOptions),
			'image' => $actor->getAvatar(SOCIAL_AVATAR_LARGE),
			'context_type' => 'apps.user.relationship.' . $verb
		];

		// relationship.request
		// relationship.approve
		// relationship.reject
		ES::notify('relationship.' . $verb, [$target->id], $emailOptions, $systemOptions);
	}
}
