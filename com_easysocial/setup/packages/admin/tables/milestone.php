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

class SocialTableMilestone extends SocialTable
{
	public $id = null;
	public $uid = null;
	public $type = null;
	public $owner_id = null;
	public $user_id = null;
	public $title = null;
	public $description = null;
	public $created = null;
	public $due = null;
	public $state = null;

	public function __construct(&$db)
	{
		parent::__construct('#__social_tasks_milestones', 'id', $db);
	}

	/**
	 * Get the owner of the milestone
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getOwner()
	{
		$user = ES::user($this->owner_id);

		return $user;
	}

	/**
	 * Get creation date
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getCreatedDate()
	{
		$date = ES::date($this->created);

		return $date;
	}

	/**
	 * Retrieves the assignee object
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getAssignee()
	{
		static $assignees = array();

		if (!isset($assignees[$this->id])) {
			$assignees[$this->id] = ES::user($this->user_id);
		}

		return $assignees[$this->id];
	}


	/**
	 * Check if this milestone has an assignee.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function hasAssignee()
	{
		$ass = $this->getAssignee();

		return !empty($ass->id);
	}

	/**
	 * Retrieves the total tasks this milestone has
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getTotalTasks()
	{
		static $tasks = array();

		if (!isset($tasks[$this->id])) {
			$tasks[$this->id] = ES::model('Tasks')->getTotalTasks($this->id);
		}

		return $tasks[$this->id];
	}

	/**
	 * Override parent's delete behavior
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function delete($pk = null)
	{
		$state = parent::delete($pk);

		if ($state) {
			ES::model('Tasks')->deleteTasks($this->id);

			$this->removeStream('createMilestone');
		}

		return $state;
	}

	/**
	 * Determines if there's a due date set for the mileston
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function hasDueDate()
	{
		return $this->due !== '0000-00-00 00:00:00';
	}

	/**
	 * Determines if the milestone is due
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function isDue()
	{
		if ($this->isCompleted()) {
			return false;
		}

		if (!$this->hasDueDate()) {
			return false;
		}

		$due = ES::date($this->due)->toUnix();
		$now = ES::date()->toUnix();

		return $now > $due;
	}

	/**
	 * Determines if the milestone is due
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function isCompleted()
	{
		return $this->state == 2;
	}

	/**
	 * Alias method for isCompleted().
	 * @since   2.0
	 * @access  public
	 */
	public function isResolved()
	{
		return $this->isCompleted();
	}


	/**
	 * Retrieves the permalink to the discussion
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getPermalink($xhtml = true, $external = false, $sef = true, $adminSef = false)
	{
		static $apps = array();

		$cluster = ES::cluster($this->type, $this->uid);

		if (!isset($apps[$this->type])) {
			$apps[$this->type] = $cluster->getApp('tasks');
		}

		$options = array();
		$options['layout'] = 'canvas';
		$options['customView'] = 'item';
		$options['uid'] = $cluster->getAlias();
		$options['type'] = $this->type;
		$options['id'] = $apps[$this->type]->getAlias();
		$options['milestoneId'] = $this->id;
		$options['external'] = $external;
		$options['sef'] = $sef;
		$options['adminSef'] = $adminSef;

		$permalink = ESR::apps($options, $xhtml);

		return $permalink;
	}

	/**
	 * Retrieves the permalink to the discussion
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getEditPermalink($xhtml = true, $external = false, $sef = true)
	{
		static $apps = array();

		$cluster = ES::cluster($this->type, $this->uid);

		if (!isset($apps[$this->type])) {
			$apps[$this->type] = $cluster->getApp('tasks');
		}

		$options = array();
		$options['layout'] = 'canvas';
		$options['customView'] = 'form';
		$options['uid'] = $cluster->getAlias();
		$options['type'] = $cluster->getType();
		$options['id'] = $apps[$this->type]->getAlias();
		$options['milestoneId'] = $this->id;
		$options['external'] = $external;
		$options['sef'] = $sef;

		$permalink = ESR::apps($options, $xhtml);

		return $permalink;
	}

	/**
	 * Retrieves a list of tasks for the milestone
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getTasks()
	{
		static $tasks = array();

		if (!isset($tasks[$this->id])) {
			$model = ES::model('Tasks');

			$tasks[$this->id] = $model->getTasks($this->id);
		}

		return $tasks[$this->id];
	}

	/**
	 * Generates a new stream item
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function createStream($verb, $actorId = null)
	{
		$stream = ES::stream();
		$tpl = $stream->getTemplate();
		$actor = ES::user($actorId);

		$registry = ES::registry();
		$registry->set('milestone', $this);

		if ($this->type == SOCIAL_TYPE_USER) {
			$user = ES::user($this->uid);

			// Cache the user data into the params
			$registry->set('user', $user);
		} else {
			// Get the cluster depending on the type
			$cluster = ES::cluster($this->type, $this->uid);

			// this is a cluster stream and it should be viewable in both cluster and user page.
			$tpl->setCluster($cluster->id, $this->type, $cluster->type);

			// Cache the cluster data into the params
			$registry->set($this->type, $cluster);
		}

		// Set the actor
		$tpl->setActor($actor->id, SOCIAL_TYPE_USER);

		// Set the context
		$tpl->setContext($this->id, 'tasks');

		// Set the verb
		$tpl->setVerb($verb);

		// Set the params to cache the group data
		$tpl->setParams($registry);

		// since this is a cluster and user stream, we need to call setPublicStream
		// so that this stream will display in unity page as well
		// This stream should be visible to the public
		$tpl->setAccess('core.view');

		$stream->add($tpl);
	}

	/**
	 * Central method to remove previously created stream.
	 * @param  string   $verb   The verb for the stream.
	 */
	public function removeStream($verb)
	{
		ES::stream()->delete($this->id, 'tasks', '', $verb);
	}

	/**
	 * Retrieves the content
	 *
	 * @since   2.2
	 * @access  public
	 */
	public function getContent()
	{
		// Apply e-mail replacements
		$content = ES::string()->replaceEmails($this->description);

		// Apply hyperlinks
		$content = ES::string()->replaceHyperlinks($content);

		// Apply bbcode
		$content = ES::string()->parseBBCode($content, array('code' => true, 'escape' => false, 'videos' => true));

		// Apply line break to the message
		$content = nl2br($content);

		return $content;
	}

	/**
	 * Allows caller to validate milestone
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function validate()
	{
		if (!$this->title) {
			$this->setError('APP_EVENT_TASKS_INVALID_TITLE');
			return false;
		}

		return true;
	}

	/**
	 * Export the milestone data
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function toExportData($viewer)
	{
		$properties = get_object_vars($this);

		$milestone = array();

		foreach ($properties as $key => $value) {
			if ($key[0] != '_') {
				$milestone[$key] = $value;
			}
		}

		return $milestone;
	}
}
