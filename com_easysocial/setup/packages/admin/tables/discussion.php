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

class SocialTableDiscussion extends SocialTable
{
	/**
	 * The unique id of the cluster
	 * @var int
	 */
	public $id = null;

	/**
	 * The category id of the cluster.
	 * @var string
	 */
	public $parent_id = null;

	/**
	 * Determines the cluster type
	 * @var string
	 */
	public $uid = null;

	/**
	 * The owner type of this cluster
	 * @var string
	 */
	public $type = null;

	/**
	 * If this discussion has been answered, it should store the discussion id.
	 * @var int
	 */
	public $answer_id = null;

	/**
	 * Determines the last replied discussion
	 * @var int
	 */
	public $last_reply_id = null;

	/**
	 * The title of this cluster
	 * @var string
	 */
	public $title = null;

	/**
	 * The content of this discussion
	 * @var string
	 */
	public $content = null;

	/**
	 * The content type, bbcode or html
	 * @var string
	 */
	public $content_type = null;

	/**
	 * The creator of this discussion
	 * @var int
	 */
	public $created_by = null;

	/**
	 * Total number of hits for this discussion
	 * @var int
	 */
	public $hits = null;

	/**
	 * The state of this discussion.
	 * @var string
	 */
	public $state = null;

	/**
	 * The creation date of this discussion
	 * @var datetime
	 */
	public $created = null;

	/**
	 * Determines the last replied date
	 * @var datetime
	 */
	public $last_replied = null;

	/**
	 * Determines the vote value of a discussion.
	 * @var string
	 */
	public $votes = null;

	/**
	 * Determines the total number of replies for a discussion
	 * @var string
	 */
	public $total_replies = 0;

	/**
	 * Determines if the discussion is locked.
	 * @var int
	 */
	public $lock = null;

	/**
	 * JSON string that is used as params
	 * @var string
	 */
	public $params = null;


	public function __construct(&$db)
	{
		parent::__construct('#__social_discussions', 'id', $db);
	}

	/**
	 * Saves a new discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function store($updateNulls = false)
	{
		$isNew = $this->id ? false : true;

		// Request the parent to store it first.
		$state = parent::store();

		if (!$state) {
			$error = parent::getError();
			$this->setError($error);

			return false;
		}

		// If it is a new discussion, we want to run some other stuffs here.
		if ($isNew) {

			$cluster = ES::cluster($this->type, $this->uid);

			// Determines the action
			$action = $this->parent_id ? 'reply' : 'create';

			// Assign points for creating a new discussion
			ES::points()->assign($cluster->getTypePlural() . '.discussion.' . $action, 'com_easysocial', $this->created_by);

			// Create a new stream item for this discussion
			$stream = ES::stream();

			// Get the context accordingly
			$contextId = $this->parent_id ? $this->parent_id : $this->id;
			$verb = $this->parent_id ? 'reply' : 'create';

			$discussion = $this;
			$reply = null;

			if ($this->parent_id) {
				$discussion = ES::table('Discussion');
				$discussion->load($this->parent_id);

				$reply = $this;

				// Update the parent's reply counter.
				$discussion->sync();
			}

			// Get the stream template
			$tpl = $stream->getTemplate();
			$tpl->setActor($this->created_by, SOCIAL_TYPE_USER);
			$tpl->setContext($contextId, 'discussions');
			$tpl->setCluster($cluster->id, $cluster->getType(), $cluster->type);
			$tpl->setVerb($verb);

			if ($this->type == SOCIAL_TYPE_PAGE && $cluster->isAdmin()) {
				$tpl->setPostAs(SOCIAL_TYPE_PAGE);
			}

			// Set the params to cache the group data
			$registry = ES::registry();
			$registry->set('cluster', $cluster);
			$registry->set('discussion', $discussion);
			$registry->set('reply', $reply);

			$tpl->setParams($registry);
			$tpl->setAccess('core.view');

			$stream->add($tpl);

			// Send notification to group members only if it is new discussion
			if (!$this->parent_id) {
				$options = [];
				$options['permalink'] = $this->getPermalink(false, true);
				$options['discussionId'] = $this->id;
				$options['discussionTitle'] = $this->title;
				$options['discussionContent'] = $this->getContent();
				$options['userId'] = $this->created_by;

				$cluster->notifyMembers('discussion.create', $options);
			} else {
				$options = [];
				$options['permalink'] = $discussion->getPermalink(false, true);
				$options['title'] = $discussion->title;
				$options['content'] = $this->getContent();
				$options['discussionId'] = $this->id;
				$options['userId'] = $this->created_by;
				$options['targets'] = $discussion->getParticipants([$this->created_by]);

				$cluster->notifyMembers('discussion.reply', $options);
			}
		}

		return $state;
	}

	/**
	 * Checks if the provided user is allowed to view this discussion
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isViewable($userId = null)
	{
		$cluster = ES::cluster($this->type, $this->uid);

		// Open clusters allows anyone to view the contents from the cluster
		if ($cluster->isOpen()) {
			return true;
		}

		// Allow cluster owner
		if (($cluster->isClosed() || $cluster->isInviteOnly()) && $cluster->isMember()) {
			return true;
		}

		// Allow cluster owner
		if ($cluster->isAdmin() || $cluster->isOwner()) {
			return true;
		}

		// Allow site admin
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Synchronizes the count for denormalized columns
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function sync()
	{
		$model = ES::model('Discussions');

		// Get the total number of replies
		$this->total_replies = $model->getTotalReplies($this->id);

		// Try to get the last reply item
		$reply = $model->getLastReply($this->id);

		// Default the last replier to none
		$this->last_reply_id = false;
		$this->last_replied = false;

		if ($reply) {
			$this->last_reply_id = $reply->id;
			$this->last_replied = $reply->created;
		}

		$this->store();
	}

	/**
	 * Allow caller to remove reply as anwser
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function removeAnswered()
	{
		$this->answer_id = 0;

		parent::store();
	}

	/**
	 * Allows caller to set a reply as an answer
	 *
	 * @since	1.2
	 * @access	public
	 * @return	bool	True if success false otherwise
	 */
	public function setAnswered(SocialTableDiscussion $reply, $actorId = null)
	{
		$previousReplyId = $this->answer_id;
		$this->answer_id = $reply->id;

		$state = parent::store();

		if ($state) {
			$cluster = ES::cluster($this->type, $this->uid);
			$actor = ES::user($actorId);

			// Deduct points if the discussion has been answered previously.
			if ($previousReplyId > 0) {
				$previousReply = ES::table('Discussion');
				$previousReply->load($previousReplyId);

				ES::points()->assign($cluster->getTypePlural() . '.discussion.rejectanswer', 'com_easysocial', $previousReply->created_by);
			}

			ES::points()->assign($cluster->getTypePlural() . '.discussion.answer', 'com_easysocial', $reply->created_by);

			// Synchronize the items
			$this->sync();

			// Create a new stream item for this discussion
			$stream = ES::stream();
			$tpl = $stream->getTemplate();
			$tpl->setActor($actor->id, SOCIAL_TYPE_USER);
			$tpl->setContext($this->id, 'discussions');
			$tpl->setCluster($cluster->id, $cluster->getType());
			$tpl->setVerb('answered');

			if ($this->type == SOCIAL_TYPE_PAGE) {
				$tpl->setPostAs(SOCIAL_TYPE_PAGE);
			}

			// Set the params to cache the event data
			$registry = ES::registry();
			$registry->set('cluster', $cluster);
			$registry->set('reply', $reply);
			$registry->set('discussion', $this);

			$tpl->setParams($registry);

			$stream->add($tpl);

			// Send notification to accepted answer user only if the reply is not the user who accepts it
			if ($actor->id != $reply->created_by) {
				$options = [];
				$options['permalink'] = $this->getPermalink(false, true);
				$options['title'] = $this->title;
				$options['content'] = $reply->getContent();
				$options['discussionId'] = $reply->id;
				$options['userId'] = $reply->created_by;
				$options['targets'] = [$reply->created_by];

				$cluster->notifyMembers('discussion.answered', $options);
			}
		}

		return $state;
	}

	/**
	 * Allow caller to reject the answer from the discussion
	 *
	 * @since	2.0.19
	 * @access	public
	 */
	public function rejectAnswer(SocialTableDiscussion $reply, $actorId = null)
	{
		// Set answer id as zero.
		$this->answer_id = 0;

		$state = parent::store();

		if ($state) {
			$cluster = ES::cluster($this->type, $this->uid);
			$actor = ES::user($actorId);

			ES::points()->assign($cluster->getTypePlural() . '.discussion.rejectanswer', 'com_easysocial', $reply->created_by);

			$this->sync();
		}
	}

	/**
	 * Allows caller to unlock a discussion
	 *
	 * @since	1.2
	 * @access	public
	 * @return	bool	True if success false otherwise
	 */
	public function unlock()
	{
		$this->lock = false;

		$state = parent::store();

		// @TODO: Should we remove the stream for "locked" status?

		return $state;
	}

	/**
	 * Allows caller to lock a discussion
	 *
	 * @since	1.2
	 * @access	public
	 * @return	bool	True if success false otherwise
	 */
	public function lock($actorId = null)
	{
		$this->lock = true;

		$state = parent::store();

		if ($state) {
			$actor = ES::user($actorId);
			$cluster = ES::cluster($this->type, $this->uid);

			// Create a new stream item for this discussion
			$stream = ES::stream();

			$tpl = $stream->getTemplate();
			$tpl->setActor($actor->id, SOCIAL_TYPE_USER);
			$tpl->setContext($this->id, 'discussions');
			$tpl->setCluster($this->uid, $this->type, $cluster->type);
			$tpl->setVerb('lock');

			if ($this->type == SOCIAL_TYPE_PAGE) {
				$tpl->setPostAs(SOCIAL_TYPE_PAGE);
			}

			// Set the params to cache the group data
			$registry = ES::registry();
			$registry->set('cluster', $cluster);
			$registry->set('discussion', $this);

			$tpl->setParams($registry);
			$tpl->setAccess('core.view');

			$stream->add($tpl);
		}
		return $state;
	}

	/**
	 * Override parent's behavior to delete this discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function delete($pk = null)
	{
		$state = parent::delete($pk);

		if ($state) {

			// Get the cluster
			$cluster = ES::cluster($this->type, $this->uid);

			// Deduct points from the discussion creator when the discussion is deleted
			$action = 'delete';
			if ($this->isReply()) {
				$action = 'deletereply';
			}

			ES::points()->assign($cluster->getTypePlural() . '.discussion.' . $action, 'com_easysocial', $this->created_by);

			// Delete all the replies
			$model = ES::model('Discussions');
			$model->deleteReplies($this->id);

			// Delete all stream items related to this discussion.
			ES::stream()->delete($this->id, 'discussions');

			// Delete any files associated in #__social_discussions_files
			$model->deleteFiles($this->id);

			// Sync the counts
			if ($this->parent_id) {
				$discussion = $this->getParent();
				$discussion->sync();
			}
		}

		return $state;
	}

	/**
	 * Retrieves the parent discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getParent()
	{
		static $items = [];

		if (!$this->parent_id) {
			return false;
		}

		if (!isset($items[$this->parent_id])) {
			$parent = ES::table('Discussion');
			$parent->load($this->parent_id);

			$items[$this->parent_id] = $parent;
		}

		return $items[$this->parent_id];
	}

	/**
	 * Retrieves the permalink to the discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getEditPermalink($xhtml = true, $external = false, $sef = true)
	{
		static $apps = [];

		$cluster = ES::cluster($this->type, $this->uid);

		if (!isset($apps[$this->type])) {
			$apps[$this->type] = $cluster->getApp('discussions');
		}

		$options = [];
		$options['layout'] = 'canvas';
		$options['customView'] = 'edit';
		$options['uid'] = $cluster->getAlias();
		$options['type'] = $cluster->getType();
		$options['id'] = $apps[$this->type]->getAlias();
		$options['discussionId'] = $this->id;

		$permalink = ESR::apps($options, $xhtml);

		return $permalink;
	}

	/**
	 * Retrieves the permalink to the discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPermalink($xhtml = true, $external = false, $sef = true, $adminSef = false)
	{
		static $apps = [];

		$cluster = ES::cluster($this->type, $this->uid);

		if (!isset($apps[$this->type])) {
			$apps[$this->type] = $cluster->getApp('discussions');
		}

		$options = [];
		$options['layout'] = 'canvas';
		$options['customView'] = 'item';
		$options['uid'] = $cluster->getAlias();
		$options['type'] = $this->type;
		$options['id'] = $apps[$this->type]->getAlias();
		$options['discussionId'] = $this->getAlias();
		$options['external'] = $external;
		$options['sef'] = $sef;
		$options['adminSef'] = $adminSef;

		$permalink = ESR::apps($options, $xhtml);

		return $permalink;
	}

	/**
	 * Constructs the alias for this discussion
	 *
	 * @since   3.2
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getAlias($withId = true)
	{
		$title = $this->title;
		$alias = JFilterOutput::stringURLSafe($title);

		if (!$alias) {
			$alias = JFilterOutput::stringURLUnicodeSlug($title);
		}

		if ($withId) {
			$alias = $this->id . ':' . $alias;
		}

		return $alias;
	}

	/**
	 * Determine if this is a reply
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function isReply()
	{
		return $this->parent_id > 0 ? true : false;
	}

	/**
	 * Determines if this current discussion can be replied to
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canReply()
	{
		$cluster = ES::cluster($this->type, $this->uid);
		$my = ES::user();

		if ($cluster->isAdmin()) {
			return true;
		}

		if ($this->lock) {
			$this->setError('APP_GROUP_DISCUSSIONS_DISCUSSION_IS_LOCKED');
			return false;
		}

		if ($cluster->isMember()) {
			return true;
		}

		// Allow site admin
		if ($my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether current user are allowed to edit the discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canEdit()
	{
		$cluster = ES::cluster($this->type, $this->uid);
		$my = ES::user();

		// No one can edit the question if the question is locked
		if ($this->lock) {
			return false;
		}

		// Admin always have the permission to edit the question
		if ($cluster->isAdmin()) {
			return true;
		}

		// Allow the creator of the question to edit their question
		if ($this->created_by == $my->id) {
			return true;
		}

		// Allow site admin
		if ($my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if user has permission to edit the reply
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canEditReply()
	{
		if (!$this->isReply()) {
			return false;
		}

		// Get the question of this reply.
		$question = $this->getParent();
		$cluster = ES::cluster($question->type, $question->uid);
		$my = ES::user();

		// No one can edit the reply once the question is locked
		if ($question->lock) {
			return false;
		}

		// Admin can edit any reply
		if ($cluster->isAdmin()) {
			return true;
		}

		// Owner of the reply itself can edit the reply
		if ($this->created_by == $my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether user has permission to delete the discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canDelete()
	{
		$cluster = ES::cluster($this->type, $this->uid);
		$my = ES::user();

		if ($this->lock) {
			return false;
		}

		if ($cluster->isAdmin()) {
			return true;
		}

		if ($this->created_by == $my->id) {
			return true;
		}

		// Allow site admin
		if ($my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if use has permission to delete the reply
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canDeleteReply()
	{
		if (!$this->isReply()) {
			return false;
		}

		$question = $this->getParent();
		$cluster = ES::cluster($question->type, $question->uid);
		$my = ES::user();

		if ($question->lock) {
			return false;
		}

		if ($cluster->isAdmin()) {
			return true;
		}

		if ($this->created_by == $my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determine whether user has permission to accept reply as answer
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canAcceptAnswer($answer = null)
	{
		// Only proceed if this is reply
		if (!$this->isReply()) {
			return false;
		}

		// Do not display the accept button if this reply already marked as answer.
		if ($answer && $this->id == $answer->id) {
			return false;
		}

		$question = $this->getParent();
		$cluster = ES::cluster($this->type, $this->uid);
		$my = ES::user();

		// The discussion must be unlocked first in order to accept the reply as answer.
		if ($question->lock) {
			return false;
		}

		// Admin can accept the answers from any discussion
		if ($cluster->isAdmin()) {
			return true;
		}

		// Discussion owner can accept reply as answer
		if ($question->created_by == $my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determine wheter user has permission to reject the answer
	 *
	 * @since	2.0.19
	 * @access	public
	 */
	public function canRejectAnswer($answer = null)
	{
		if (!$this->isReply()) {
			return false;
		}

		// There are no answer provided means nothing to reject
		if (!$answer) {
			return false;
		}

		// Only check if this reply is the same as answer id
		if ($this->id != $answer->id) {
			return false;
		}

		$question = $this->getParent();
		$cluster = ES::cluster($this->type, $this->uid);
		$my = ES::user();

		// The discussion must be unlocked first in order to accept the reply as answer.
		if ($question->lock) {
			return false;
		}

		// Admin can accept the answers from any discussion
		if ($cluster->isAdmin()) {
			return true;
		}

		// Discussion owner can accept reply as answer
		if ($question->created_by == $my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if user has permission to lock discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canLock()
	{
		$cluster = ES::cluster($this->type, $this->uid);
		$my = ES::user();

		if ($cluster->isAdmin()) {
			return true;
		}

		// Allow site admin
		if ($my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if user has permission to access dropdown action
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canAccessDropdownAction()
	{
		$my = ES::user();

		if ($this->canEdit()) {
			return true;
		}

		if ($this->canDelete()) {
			return true;
		}

		if ($this->canLock()) {
			return true;
		}

		// Allow site admin
		if ($my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Get participants in a discussion
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getParticipants($exclude = [])
	{
		$model = ES::model('Discussions');

		$participants = $model->getParticipants($this->id, ['exclude' => $exclude]);

		return $participants;
	}

	/**
	 * Generates the content of a discussion
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getContent($isStreamView = false)
	{
		// Escape html codes
		$content = $this->content;

		$lib = ES::string();

		// Apply gist replacements
		$content = $lib->replaceGist($content);

		// Apply bbcode
		$options = ['code' => true, 'escape' => true, 'video' => true];

		if ($this->content_type == 'bbcode') {
			$content = $lib->parseBBCode($content, $options);

			// Replace video bbcode
			$content = ES::bbcode()->replaceVideo($content);

			// Apply e-mail replacements
			$content = $lib->replaceEmails($content);

			// Apply hyperlinks only after the content is parse
			$content = $lib->replaceHyperlinks($content);

			if ($isStreamView) {
				// Remove [file] from contents
				$content = $this->removeFiles($content);

			} else {
				// Remove [file] from the content then populate the download link in the content
				$content = $this->replaceFiles($content);
			}
		}

		return $content;
	}

	/**
	 * Allows caller to validate the current discussion object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function validate()
	{
		if (!$this->title) {
			$this->setError('APP_GROUP_DISCUSSIONS_INVALID_TITLE');
			return false;
		}

		return true;
	}

	public function removeFiles($content)
	{
		$pattern = '/\[file(.*?)\](.*?)\[\/file\]/is';

		$content = preg_replace($pattern, '', $content);

		return $content;
	}

	/**
	 * Stores the list of files for this particular discussion
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function mapFiles()
	{
		// Get a list of files from the content first.
		$files = $this->getFiles();

		if (!$files) {
			return false;
		}

		foreach ($files as $file) {
			$table = ES::table('DiscussionFile');
			$table->file_id = $file->id;
			$table->discussion_id = $this->id;

			$table->store();
		}

		return true;
	}


	/**
	 * Replaces the files in the discussion with images
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function replaceFiles($content)
	{
		// due to the htmlentities, now the quotes become &quot; and we need to change it back.
		// $content = ESJString::str_ireplace('&quot;', '"', $content);

		$pattern = '/\[file id="(.*?)"\](.*?)\[\/file\]/is';
		preg_match_all($pattern, $this->content, $matches);

		// If there are no matches, skip this altogether.
		if (!$matches && !$matches[0]) {
			return $content;
		}

		// Now we need to do a proper search / replace
		$total = count($matches[0]);

		for ($i = 0; $i < $total; $i++) {

			$search = $matches[0][$i];
			$fileId = $matches[1][$i];
			$title = $matches[2][$i];

			$file = ES::table('File');
			$file->load($fileId);

			// Perhaps the user is trying to exploit the system?
			if (!$file->id || ($file->uid != $this->uid && $file->type != $this->type)) {
				continue;
			}

			$theme = ES::themes();
			$theme->set('file', $file);

			if ($file->hasPreview()) {
				$replace = $theme->output('site/discussions/item.image');
			} else {
				$replace = $theme->output('site/discussions/item.file');
			}

			$content = ESJString::str_ireplace($search, $replace, $content);
		}

		return $content;
	}

	/**
	 * Determines if there are files in a discusison
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function hasFiles()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_discussions_files');
		$sql->column('COUNT(1)', 'total');
		$sql->where('discussion_id', $this->id);

		$db->setQuery($sql);
		$total = $db->loadResult();

		return $total > 0;
	}

	/**
	 * Retrieves a list of files posted in this discussion
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getFiles()
	{
		static $data = [];

		if (!isset($data[$this->id])) {
			$pattern = '/\[file id="(.*?)"\](.*?)\[\/file\]/is';

			preg_match_all($pattern, $this->content, $matches);

			if (!isset($matches[1]) || empty($matches[1])) {
				return false;
			}

			$ids = $matches[1];
			$files = [];

			foreach($ids as $id) {
				$file 	= ES::table('File');
				$file->load($id);

				// Perhaps the user is trying to exploit the system?
				if (!$file->id || ($file->uid != $this->uid && $file->type != $this->type)) {
					continue;
				}

				// If the user tries to use the same files twice, ignore this
				if (isset($files[$file->id])) {
					continue;
				}

				$files[$file->id] = $file;
			}

			$data[$this->id] = $files;
		}

		return $data[$this->id];
	}

	/**
	 * Retrieve the  author of the discussion
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getAuthor()
	{
		// Load the cluster
		$cluster = ES::cluster($this->type, $this->uid);

		// If the author is the admin of the page, let the Page be the author
		if ($cluster && $cluster->getType() == SOCIAL_TYPE_PAGE && $cluster->isAdmin($this->created_by)) {
			return $cluster;
		}

		return ES::user($this->created_by);
	}

	/**
	 * Export the discussion data
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer, $options = [])
	{
		static $cache = [];

		$key = $this->id . $viewer->id;

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$extended = ES::normalize($options, 'extended', true);

		$autor = $this->getAuthor();

		$result = [
			'id' => $this->id,
			'title' => $this->title,
			'content' => $this->getContent(),
			'created_by' => $this->created_by,
			'permalink' => $this->getPermalink(),
			'isOwner' => $autor->id === $viewer->id,
			'author' => $this->getAuthor()->toExportData($viewer),
			'hits' => $this->hits,
			'createdString' => ES::date($this->created)->format(JText::_('DATE_FORMAT_LC1')),
			'created' => $this->created,
			'timestamp' => ES::date($this->created)->toLapsed()
		];

		// if this is a question
		if (!$this->parent_id) {
			$result['totalReplies'] = $this->total_replies;
			$result['answer_id'] = $this->answer_id;
			$result['isResolved'] = $this->answer_id ? true : false;
			$result['isUnanswered'] = !$this->total_replies ? true : false;
			$result['isLocked'] = $this->lock;
			$result['canLock'] = $this->canLock();
			$result['canEdit'] = $this->canEdit();
			$result['canDelete'] = $this->canDelete();

			$verb = $this->type === SOCIAL_TYPE_GROUP ? 'create' : 'post';
			$result['likes'] = ES::likes($this->id, 'discussion', $verb, $this->type);
		} else {
			$result['canEdit'] = $this->canEditReply();
			$result['canDelete'] = $this->canDeleteReply();
			$result['canAcceptAnswer'] = $this->canAcceptAnswer();
			$result['canRejectAnswer'] = $this->canRejectAnswer();
			$result['likes'] = ES::likes($this->id, 'discussion', 'reply', $this->type);
		}

		if ($extended) {
			$model = ES::model('Discussions');
			$replies = $model->getReplies($this->id, ['ordering' => 'created']);

			$result['replies'] = [];

			foreach ($replies as $reply) {
				$result['replies'][] = $reply->toExportData($viewer);
			}

			$participants = $model->getParticipants($this->id);

			$result['participants'] = [];

			foreach ($participants as $participant) {
				$result['participants'][] = $participant->toExportData($viewer);
			}

			$result['canReply'] = $this->canReply();
		}

		$result = (object) $result;

		$cache[$key] = $result;

		return $cache[$key];
	}
}
