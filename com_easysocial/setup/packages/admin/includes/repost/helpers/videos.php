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

ES::import('admin:/includes/themes/themes');

class SocialRepostHelperVideos
{
	private $title = null;
	private $content = null;

	public function __construct($uid, $group, $element)
	{
		$videoTbl = ES::table('Video');
		$videoTbl->load($uid);

		$video = ES::video($videoTbl);
		$uid = '';
		$utype = '';

		if ($video->type != SOCIAL_TYPE_USER) {
			$uid = $video->uid;
			$utype = $video->type;
		}

		$theme = ES::themes();
		$theme->set('video', $video);
		$theme->set('uid', $uid);
		$theme->set('utype', $utype);

	 	$html = $theme->output('site/repost/preview.video');

		$this->content = $html;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getContent()
	{
		return $this->content;
	}
}
