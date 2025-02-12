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

class ThemesHelperListing
{
	/**
	 * Generates the namespace for the theme file
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	private function getStyleNamespace($type, $style = 'listing')
	{
		$namespace = 'site/helpers/listing/' . $type . '/' . $style;

		return $namespace;
	}

	/**
	 * Renders the listing layout for album
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function album(SocialTableAlbum $album, $displayType = false)
	{
		$theme = ES::themes();
		$theme->set('displayType', $displayType);
		$theme->set('album', $album);
		$output = $theme->output('site/helpers/listing/album');

		return $output;
	}

	/**
	 * Renders a loader for listings
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function loader($style = 'card', $rows = 5, $columns = 2, $options = array())
	{
		$snackbar = ES::normalize($options, 'snackbar', false);
		$sortbar = ES::normalize($options, 'sortbar', false);
		$pictureOnly = ES::normalize($options, 'pictureOnly', false);
		$columnStyle = round(12 / $columns);

		$theme = ES::themes();
		$theme->set('sortbar', $sortbar);
		$theme->set('snackbar', $snackbar);
		$theme->set('pictureOnly', $pictureOnly);
		$theme->set('rows', $rows);
		$theme->set('columns', $columns);
		$theme->set('columnStyle', $columnStyle);

		if ($style == 'listing') {
			$style = 'list';
		}

		$namespace = 'site/helpers/loader/' . $style;

		$theme->set('namespace', $namespace);

		$output = $theme->output('site/helpers/loader/structure');

		return $output;
	}

	/**
	 * Renders the listing layout for users
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function user(SocialUser $user, $options = array())
	{
		static $cache = [];

		$showRemoveFromList = ES::normalize($options, 'showRemoveFromList', false);
		$displayType = ES::normalize($options, 'displayType', false);
		$style = ES::normalize($options, 'style', 'listing');
		$namespace = $this->getStyleNamespace('user', $style);

		$index = md5($user->id . ((int)$showRemoveFromList) . $displayType . $namespace);

		if (!isset($cache[$index])) {

			$badges = $user->getBadges();

			$theme = ES::themes();
			$theme->set('badges', $badges);
			$theme->set('displayType', $displayType);
			$theme->set('showRemoveFromList', $showRemoveFromList);
			$theme->set('user', $user);

			$cache[$index] = $theme->output($namespace);
		}

		return $cache[$index];
	}

	/**
	 * Renders the listing layout for events
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function event(SocialEvent $event, $options = array())
	{
		$style = ES::normalize($options, 'style', 'listing');
		$namespace = $this->getStyleNamespace('event', $style);

		$showDistance = ES::normalize($options, 'showDistance', false);
		$displayType = ES::normalize($options, 'displayType', false);
		$isGroupOwner = ES::normalize($options, 'isGroupOwner', false);
		$browseView = ES::normalize($options, 'browseView', false);

		$theme = ES::themes();
		$theme->set('showDistance', $showDistance);
		$theme->set('displayType', $displayType);
		$theme->set('isGroupOwner', $isGroupOwner);
		$theme->set('browseView', $browseView);
		$theme->set('event', $event);

		$output = $theme->output($namespace);

		return $output;
	}

	/**
	 * Renders the listing layout for groups
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function group(SocialGroup $group, $options = array())
	{
		$style = ES::normalize($options, 'style', 'listing');
		$namespace = $this->getStyleNamespace('group', $style);

		$showDistance = ES::normalize($options, 'showDistance', false);
		$displayType = ES::normalize($options, 'displayType', false);

		$theme = ES::themes();
		$theme->set('showDistance', $showDistance);
		$theme->set('displayType', $displayType);
		$theme->set('group', $group);

		$output = $theme->output($namespace);

		return $output;
	}

	/**
	 * Renders the listing layout for pages
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function page(SocialPage $page, $options = array())
	{
		$style = ES::normalize($options, 'style', 'listing');
		$namespace = $this->getStyleNamespace('page', $style);

		$displayType = ES::normalize($options, 'displayType', false);

		$theme = ES::themes();
		$theme->set('displayType', $displayType);
		$theme->set('page', $page);

		$output = $theme->output($namespace);

		return $output;
	}

	/**
	 * Renders the listing layout for audios
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function audio(SocialAudio $audio, $displayType = false)
	{
		$theme = ES::themes();
		$theme->set('displayType', $displayType);
		$theme->set('audio', $audio);

		$output = $theme->output('site/helpers/listing/audio');

		return $output;
	}

	/**
	 * Renders the listing layout for videos
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function video(SocialVideo $video, $displayType = false)
	{
		$theme = ES::themes();
		$theme->set('displayType', $displayType);
		$theme->set('video', $video);

		$output = $theme->output('site/helpers/listing/video');

		return $output;
	}

	/**
	 * Renders the listing layout for photos
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function photo(SocialTablePhoto $photo, $displayType = false)
	{
		$theme = ES::themes();
		$theme->set('displayType', $displayType);
		$theme->set('photo', $photo);
		$output = $theme->output('site/helpers/listing/photo');

		return $output;
	}

	/**
	 * Renders the listing layout for apps listings
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function app(SocialTableApp $app, $options = array())
	{
		$style = ES::normalize($options, 'style', 'listing');
		$namespace = $this->getStyleNamespace('app', $style);

		$displayType = ES::normalize($options, 'displayType', false);

		$theme = ES::themes();
		$theme->set('displayType', $displayType);
		$theme->set('app', $app);

		$output = $theme->output($namespace);

		return $output;
	}

	/**
	 * Renders the listing layout for marketplace
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function marketplace(SocialMarketplace $listing, $options = array())
	{
		$style = ES::normalize($options, 'style', 'listing');
		$namespace = $this->getStyleNamespace('marketplace', $style);

		$displayType = ES::normalize($options, 'displayType', false);
		$browseView = ES::normalize($options, 'browseView', false);
		$from = ES::normalize($options, 'from', '');

		$theme = ES::themes();
		$theme->set('displayType', $displayType);
		$theme->set('browseView', $browseView);
		$theme->set('listing', $listing);
		$theme->set('from', $from);

		$output = $theme->output($namespace);

		return $output;
	}

	/**
	 * Renders the listing layout for ads
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function ads(SocialAd $ad, $options = array())
	{
		$theme = ES::themes();
		$theme->set('ad', $ad);
		$output = $theme->output('site/helpers/listing/ad');

		return $output;
	}
}
