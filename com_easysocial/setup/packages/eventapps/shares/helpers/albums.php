<?php
/**
* @package        EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license        GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(dirname(__FILE__) . '/abstract.php');

class SocialEventSharesHelperAlbums extends SocialEventSharesHelper
{
	public function getContent()
	{
		$message = $this->formatContent($this->share->content);
		$sourceId = $this->share->uid;

		// Load the album object
		$album = ES::table('Album');
		$album->load($sourceId);

		// Get user's privacy.
		$my = ES::user();
		$privacy = ES::privacy($my->id);

		if (!$privacy->validate('albums.view', $album->id, SOCIAL_TYPE_ALBUM, $album->uid)) {
			return false;
		}

		$theme = ES::get('Themes');
		$theme->set('album', $album);
		$theme->set('message', $message);

		$html = $theme->output('apps/event/shares/streams/albums/content');

		return $html;
	}

	public function getLink($sef = true)
	{
		$link = ESR::albums(array('id' => $this->item->contextId, 'sef' => $sef));

		return $link;
	}

	public function getStreamTitle()
	{
		$actors = $this->item->actors;
		$names = ES::string()->namesToStream($actors, true, 3);

		// Load the album
		$album = ES::table('Album');
		$album->load($this->share->uid);
		$albumCreator = ES::user($album->uid);

		$theme = ES::get('Themes');
		$theme->set('names', $names);
		$theme->set('album', $album);
		$theme->set('creator', $albumCreator);

		$html = $theme->output('apps/user/shares/streams/albums/title');

		return $html;
	}
}
