<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/themes/themes');

class SocialRepostHelperStream
{
	private $title = null;
	private $content = null;

	public function __construct($uid, $group, $element)
	{
		$stream = ES::stream();
		$data = $stream->getItem($uid);

		$item = $data[0];

		$this->title = $item->title;
		$this->content = $item->content;

		if (isset($item->preview) && $item->preview) {
			$this->content .= $item->preview;
		}

		$this->content = $this->content . $item->getMetaHtml();
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
