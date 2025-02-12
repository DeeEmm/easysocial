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

class SocialRepostHelperAudios
{
	private $title = null;
	private $content = null;

	public function __construct($uid, $group, $element)
	{
		$audioTbl = ES::table('Audio');
		$audioTbl->load($uid);

		$audio = ES::audio($audioTbl);

		$theme = ES::themes();
		$theme->set('audio', $audio);
	 	$html = $theme->output( 'site/repost/preview.audio');

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
