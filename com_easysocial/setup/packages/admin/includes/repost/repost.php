<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialRepost extends EasySocial
{
	public $uid = null;
	public $element = null;
	public $group = null;
	public $cluster_id = null;
	public $cluster_type = null;
	public $share_as = null;
	public $stream_id = null;

	public function __construct($uid, $element, $group = SOCIAL_APPS_GROUP_USER, $clusterId = 0, $clusterType = '', $shareAs = 'user')
	{
		parent::__construct();

		$this->uid = $uid;
		$this->element = $element;
		$this->group = $group;
		$this->cluster_id = $clusterId;
		$this->cluster_type = $clusterType;
		$this->share_as = $shareAs;
	}

	public static function factory($uid, $element, $group = SOCIAL_APPS_GROUP_USER, $clusterId = 0, $clusterType = '', $shareAs = 'user')
	{
		return new self($uid, $element, $group, $clusterId, $clusterType, $shareAs);
	}

	public function setCluster($clusterId, $clusterType)
	{
		$this->cluster_id = $clusterId;
		$this->cluster_type = $clusterType;
	}

	public function setStreamId($streamId)
	{
		$this->stream_id = $streamId;
	}

	public function getCluster()
	{
		// Get the cluster object
		$cluster = ES::cluster($this->cluster_type, $this->cluster_id);


		// if ($cluster->getType() == SOCIAL_TYPE_EVENT) {

		// 	$tmp = null;

		// 	if ($cluster->isGroupEvent()) {
		// 		$tmp = $event->getGroup();
		// 	}

		// 	if ($cluster->isPageEvent()) {
		// 		$tmp = $cluster->getPage();
		// 	}

		// 	if ($tmp) {
		// 		$cluster = $tmp;
		// 	}
		// }

		return $cluster;
	}


	public function canShare()
	{
		// Site admins should always be allowed to react
		if (ES::isSiteAdmin()) {
			return true;
		}

		if ($this->cluster_id) {
			$cluster = $this->getCluster();

			// If it is a public cluster, it should allow this
			if ($cluster->isOpen()) {
				return true;
			}

			// Here we assume that the cluster is not open and we need to ensure that the viewer is a member of the cluster
			if (!$cluster->isMember()) {
				return false;
			}

			return true;
		}

		if ($this->element == 'stream') {
			$streamTable = ES::table('Stream');
			$streamTable->load($this->uid);

			if ($streamTable->id && (!$streamTable->isModerated() && !$streamTable->isTrashed())) {
				$items = ES::stream()->getItem($this->uid, '', '', false, array('perspective' => 'dashboard'));
				if (!is_array($items)) {
					// if stream lib return data, mean this user can view the stream
					return false;
				}
				return true;
			}
		}

		if ($this->element == 'albums') {
			$album = ES::table('Album');
			$album->load($this->uid);

			$lib = ES::albums($album->uid, $album->type, $album);

			if (!$lib->viewable() || !$lib->canViewAlbum()) {
				return false;
			}

			return true;
		}

		if ($this->element == 'photos') {
			$photo = ES::table('Photo');
			$photo->load($this->uid);

			$lib = ES::photo($photo->uid, $photo->type, $photo);

			if ($lib->isblocked() || !$lib->viewable()) {
				return false;
			}

			return true;
		}

		return true;
	}


	public function add($userId = null, $content = null)
	{
		if (empty($userId)) {
			$userId = ES::user()->id;
		}

		$model = ES::model('Repost');
		$state = $model->add($this->uid, $this->formKeys($this->element, $this->group), $userId, $content, $this->share_as, $this->cluster_id, $this->stream_id);

		return $state;
	}

	public function delete($userId = null)
	{
		if (empty($userId))
		{
			$userId = ES::user()->id;
		}

		$model = ES::model('Repost');
		$state = $model->delete($this->uid, $this->formKeys($this->element, $this->group), $userId);

		return $state;
	}

	public function isShared($userId, $shareAs = 'user')
	{
		$element = $this->formKeys($this->element, $this->group);

		$table = ES::table('Share');
		$table->load(array('uid' => $this->uid, 'element' => $element, 'user_id' => $userId, 'share_as' => $shareAs));

		if ($table->id) {
			// already shared before. js return true.
			return true;
		}

		return false;
	}

	private function formKeys($element, $group)
	{
		return $element . '.' . $group;
	}

	public function getCount()
	{
		$model = ES::model('Repost');
		$cnt = $model->getCount($this->uid, $this->formKeys($this->element, $this->group));

		return $cnt;
	}

	/**
	 * Alias method for getButton
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function button($label = null)
	{
		return $this->getButton($label);
	}

	/**
	 * Retrieves the repost link
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getButton($label = null)
	{
		if (!$this->config->get('repost.enabled')) {
			return;
		}

		$my = ES::user();

		if (!$label) {
			$label = JText::_('COM_EASYSOCIAL_REPOST');
		}

		$currentViewName = $this->input->get('view', '', 'cmd');

		$themes = ES::get('Themes');
		$themes->set('text', $label);
		$themes->set('my', $my);
		$themes->set('uid', $this->uid);
		$themes->set('element', $this->element);
		$themes->set('group', $this->group);
		$themes->set('clusterId', $this->cluster_id);
		$themes->set('clusterType', $this->cluster_type);
		$themes->set('streamId', $this->stream_id);
		$themes->set('currentViewName', $currentViewName);

		$html = $themes->output('site/repost/action');
		return $html;
	}

	/**
	 * Retrieves the html of the repost
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function counter()
	{
		if (!$this->config->get('repost.enabled')) {
			return;
		}

		// Get the count.
		$count = 0;
		$text = '';

		$count = $this->getCount();
		$text = JText::sprintf('COM_EASYSOCIAL_REPOST' . ES::language()->pluralize($count, true)->getString(), $count);

		$theme = ES::themes();
		$theme->set('text', $text);
		$theme->set('uid', $this->uid);
		$theme->set('element', $this->element);
		$theme->set('group', $this->group);
		$theme->set('count', $count);

		$output = $theme->output('site/repost/counter');

		return $output;
	}

	/**
	 * Deprecated. Use @counter instead
	 *
	 * @deprecated	2.0
	 * @access	public
	 */
	public function toHTML()
	{
		return $this->counter();
	}

	/**
	 * Displays the sharing code on the page.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getHTML()
	{
		// Get the count.
		$count = 0;
		$text = '';

		$count = $this->getCount();

		// $text = JText::sprintf('COM_EASYSOCIAL_REPOST_COUNT_SHARED', $count);
		$cntPluralize = ES::get('Language')->pluralize($count, true)->getString();
		$text = JText::sprintf('COM_EASYSOCIAL_REPOST' . $cntPluralize, $count);

		$themes = ES::get('Themes');
		$themes->set('text', $text);
		$themes->set('uid', $this->uid);
		$themes->set('element', $this->element);
		$themes->set('group', $this->group);
		$themes->set('count', $count);

		$html = $themes->output('site/repost/item');
		return $html;
	}

	/**
	 * Retrieves the preview item
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function preview()
	{
		$file = dirname(__FILE__) . '/helpers/' . $this->element . '.php';

		if (JFile::exists($file)) {
			require_once($file);

			// Get class name.
			$className = 'SocialRepostHelper' . ucfirst($this->element);

			// Instantiate the helper object.
			$helper = new $className($this->uid, $this->group, $this->element);

			$content = $helper->getContent();
			$title = $helper->getTitle();
			$message = '';

			if ($this->stream_id) {
				$stream = ES::table('Stream');
				$stream->load($this->stream_id);
				$message = $stream->content;
			}

			$themes = ES::get('Themes');
			$themes->set('title', $title);
			$themes->set('message', $message);
			$themes->set('content', $content);

			$html = $themes->output('site/repost/preview');
			return $html;
		}

		return false;
	}

	public function toExportData(SocialUser $viewer)
	{
		$repost = new stdClass();
		$repost->uid = $this->uid;
		$repost->element = $this->element;
		$repost->group = $this->group;
		$repost->cluster_id = $this->cluster_id;
		$repost->cluster_type = $this->cluster_type;
		$repost->share_as = $this->share_as;
		$repost->stream_id = $this->stream_id;
		$repost->isShared = $this->isShared($viewer->id);

		return $repost;
	}

}
