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

class SocialTableClusterNews extends SocialTable
{
	public $id = null;
	public $cluster_id	= null;
	public $title = null;
	public $content = null;
	public $content_type = null;
	public $created = null;
	public $created_by = null;
	public $state = null;
	public $comments = null;
	public $hits = null;
	public $_stream_date = null;

	public function __construct(&$db)
	{
		parent::__construct( '#__social_clusters_news' , 'id' , $db );
	}

	/**
	 * Allows the caller to check on some of the required items
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function check()
	{
		if (empty($this->title)) {
			$this->setError(JText::_('APP_NEWS_PLEASE_ENTER_TITLE'));
			return false;
		}

		if (empty($this->content)) {
			$this->setError(JText::_('APP_NEWS_PLEASE_ENTER_CONTENT'));
			return false;
		}

		if (empty($this->cluster_id)) {
			$this->setError(JText::_('APP_NEWS_PLEASE_SPECIFY_OWNER'));
			return false;
		}

		if (empty($this->created_by)) {
			$this->setError(JText::_('APP_NEWS_PLEASE_SPECIFY_AUTHOR'));
			return false;
		}

		return true;
	}

	/**
	 * Override parent's store behavior
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function store($updateNulls = [])
	{
		$isNew = !$this->id;
		$state = parent::store();

		// If it is a new item, we want to run some other stuffs here.
		if ($isNew && $state) {

			// Get the cluster
			$cluster = ES::cluster($this->cluster_id);

			// Assign points for creating a new news item
			ES::points()->assign($cluster->getTypePlural() . '.news.create', 'com_easysocial', $this->created_by);

			// Get the permalink of this news item
			$permalink = $this->getPermalink(false, true);

			$options = [
				'userId' => $this->created_by, 
				'permalink' => $permalink, 
				'newsId' => $this->id, 
				'newsTitle' => $this->title, 
				'newsContent' => $this->getContent()
			];

			$cluster->notifyMembers('news.create', $options);

			// Create a new stream item for this discussion
			$stream = ES::stream();

			// Get the stream template
			$tpl = $stream->getTemplate();
			$tpl->setActor($this->created_by, SOCIAL_TYPE_USER);
			$tpl->setContext($this->id, 'news');
			$tpl->setCluster($this->cluster_id, $cluster->getType(), $cluster->type);
			$tpl->setVerb('create');

			$registry = ES::registry();
			$registry->set('news', $this);

			// Set the params
			$tpl->setParams($registry);

			if ($this->_stream_date) {
				$tpl->setDate($this->_stream_date);
			}

			$tpl->setAccess('core.view');

			// Add the stream
			$stream->add($tpl);
		}

		return $state;
	}

	/**
	 * Override stream creation date. This function need to be called before calling the store function.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function setStreamDate($datestring)
	{
		$this->_stream_date = $datestring;
	}

	/**
	 * Generates the content of the announcement
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getContent()
	{
		// Escape html codes
		$content = $this->content;

		$lib = ES::string();

		// Apply gist replacements
		$content = $lib->replaceGist($content);

		// Apply bbcode
		$options = array('code' => true, 'escape' => true);

		if ($this->content_type == 'bbcode') {
			$content = $lib->parseBBCode($content, $options);

			// Replace video bbcode
			$content = ES::bbcode()->replaceVideo($content);

			// Apply e-mail replacements
			$content = $lib->replaceEmails($content);

			// Apply hyperlinks only after the content is parse
			$content = $lib->replaceHyperlinks($content);
		}

		return $content;
	}

	/**
	 * Retrieves the edit permalink for the news
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getEditPermalink($xhtml = true)
	{
		$cluster = ES::cluster($this->cluster_id);
		$type = $cluster->getType();

		if (!isset($apps[$type])) {
			$apps[$type] = $cluster->getApp('news');
		}

		$options = array();

		$options['layout'] = 'canvas';
		$options['customView'] = 'form';
		$options['uid'] = $this->cluster_id;
		$options['type'] = $cluster->getType();
		$options['id'] = $apps[$type]->getAlias();
		$options['newsId'] = $this->id;

		$permalink = ESR::apps($options, $xhtml);

		return $permalink;
	}

	/**
	 * Retrieves the created date
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getCreatedDate()
	{
		$date = ES::date($this->created);

		return $date;
	}

	/**
	 * Returns the permalink to the announcement
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getPermalink($xhtml = true, $external = false, $sef = true, $adminSef = false)
	{
		static $apps = array();

		$cluster = ES::cluster($this->cluster_id);
		$type = $cluster->getType();

		if (!isset($apps[$type])) {
			$apps[$type] = $cluster->getApp('news');
		}

		$options = array();
		$options['layout'] = 'canvas';
		$options['customView'] = 'item';
		$options['uid'] = $cluster->getAlias();
		$options['type'] = $cluster->getType();
		$options['id'] = $apps[$type]->getAlias();
		$options['newsId'] = $this->id;
		$options['external'] = $external;
		$options['sef'] = $sef;
		$options['adminSef'] = $adminSef;

		$permalink = ESR::apps($options, $xhtml);

		return $permalink;
	}

	/**
	 * Override parent's behavior to delete the item.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function delete($pk = null)
	{
		$state = parent::delete($pk);

		if ($state) {

			// Cleanup related items
			ES::stream()->delete($this->id, 'news');
			ES::comments( $this->id , 'news', 'create')->delete();
			ES::likes()->delete($this->id, 'news', 'create');

			// Points assignment
			// Deduct points from the news creator when the news is deleted.
			$cluster = ES::cluster($this->cluster_id);
			$type = $cluster->getTypePlural();

			ES::points()->assign($type . '.news.delete', 'com_easysocial', $this->created_by);
		}

		return $state;
	}

	/**
	 * Increase the hit counter of the news item
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function hit($pk = null)
	{
		$ip = $this->input->server->get('REMOTE_ADDR', '');

		if (!empty($ip) && !empty($this->id)) {
			$token = md5($ip . $this->id);

			$session = JFactory::getSession();
			$exists = $session->get($token, false);

			if ($exists) {
				return true;
			}

			$session->set($token, 1);
		}

		// Update hit counter
		return parent::hit($pk);
	}

	/**
	 * Retrieve the author for this news
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function getAuthor()
	{
		$cluster = ES::cluster($this->cluster_id);

		// Special case for Pages, the author will always be the page
		if ($cluster->getType() == SOCIAL_TYPE_PAGE) {
			return $cluster;
		}
		return ES::user($this->created_by);
	}

	/**
	 * Render Meta Object
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function renderMetaObj()
	{
		$metaObject = new stdClass();
		$metaObject->title = $this->title;
		$metaObject->description = $this->content;
		$metaObject->url = $this->getPermalink(true, true);

		ES::meta()->setMetaObj($metaObject);
	}

	/**
	 * Checks if the provided user is allowed to view this news
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function isViewable($userId = null)
	{
		$cluster = ES::cluster($this->cluster_id);

		// Open pages allows anyone to view the contents from the page
		if ($cluster->isOpen()) {
			return true;
		}

		// Allow page owner
		if (($cluster->isClosed() || $cluster->isInviteOnly()) && $cluster->isMember()) {
			return true;
		}

		// Allow page owner
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
	 * Export the news data
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function toExportData($viewer)
	{
		$properties = get_object_vars($this);

		$news = array();

		foreach ($properties as $key => $value) {
			if ($key[0] != '_') {
				$news[$key] = $value;
			}
		}

		return $news;
	}
}
