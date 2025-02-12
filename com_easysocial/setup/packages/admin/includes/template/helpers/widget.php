<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class ThemesHelperWidget extends ThemesHelperAbstract
{
	/**
	 * Renders the DOM structure for a widget title
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function title($contents, $actionData = array())
	{
		$contents = JText::_($contents);
		$action = false;

		if ($actionData) {
			$action = new stdClass();
			$action->attributes = isset($actionData['attributes']) ? $actionData['attributes'] : '';
			$action->link = isset($actionData['link']) ? $actionData['link'] : 'javascript:void(0);';
			$action->icon = isset($actionData['icon']) ? $actionData['icon'] : '';
			$action->text = isset($actionData['text']) ? JText::_($actionData['text']) : '';
		}

		$theme = ES::themes();
		$theme->set('action', $action);
		$theme->set('contents', $contents);
		$output = $theme->output('site/helpers/widget/title');

		return $output;
	}

	/**
	 * Renders an empty block on the widget
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function placeholder()
	{
		$theme = ES::themes();
		$output = $theme->output('site/helpers/widget/placeholder');

		return $output;
	}

	/**
	 * Renders an empty block on the widget
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function emptyBlock($text)
	{
		$theme = ES::themes();

		$text = JText::_($text);

		$theme->set('text', $text);
		$output = $theme->output('site/helpers/widget/empty');

		return $output;
	}

	/**
	 * Widget for generating covers on the sidebar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function albums($albums = array(), $emptyMessage = 'COM_EASYSOCIAL_NO_ALBUM_AVAILABLE', $limit = 3)
	{
		$emptyMessage = JText::_($emptyMessage);
		$photoIds = array();

		// Get photos from the album
		foreach ($albums as $album) {
			$photos = $album->getPhotos(array('limit' => $limit));

			$album->photos = $photos['photos'];
			$album->totalPhotos = count($photos['photos']);

			// Photos caching
			$photosIds[] = $photos['photos'];
		}

		if ($photoIds) {
			ES::cache()->cachePhotos($photoIds);
		}

		$theme = ES::themes();
		$theme->set('albums', $albums);
		$theme->set('emptyMessage', $emptyMessage);

		$output = $theme->output('site/helpers/widget/albums');

		return $output;
	}

	/**
	 * Widget for generating covers on the sidebar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function photos($photos = array(), $emptyMessage = 'APP_PHOTOS_PROFILE_WIDGET_NO_PHOTOS_UPLOADED_YET')
	{
		$emptyMessage = JText::_($emptyMessage);

		$theme = ES::themes();
		$theme->set('photos', $photos);
		$theme->set('emptyMessage', $emptyMessage);

		$output = $theme->output('site/helpers/widget/photos');

		return $output;
	}

	/**
	 * Widget for generating covers on the sidebar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function users($users = array(), $emptyMessage = 'COM_EASYSOCIAL_NO_USERS')
	{
		$emptyMessage = JText::_($emptyMessage);

		$theme = ES::themes();
		$theme->set('users', $users);
		$theme->set('emptyMessage', $emptyMessage);

		$output = $theme->output('site/helpers/widget/users');

		return $output;
	}

	/**
	 * Widget for generating groups on the sidebar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function groups($groups = array(), $emptyMessage = 'APP_USER_GROUPS_WIDGET_NO_GROUPS_YET')
	{
		$emptyMessage = JText::_($emptyMessage);

		$theme = ES::themes();
		$theme->set('groups', $groups);
		$theme->set('emptyMessage', $emptyMessage);

		$output = $theme->output('site/helpers/widget/groups');

		return $output;
	}

	/**
	 * Widget for generating pages on the sidebar
	 *
	 * @since   2.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function pages($pages = array(), $emptyMessage = 'APP_USER_PAGES_WIDGET_NO_PAGES_YET')
	{
		$emptyMessage = JText::_($emptyMessage);

		$theme = ES::themes();
		$theme->set('pages', $pages);
		$theme->set('emptyMessage', $emptyMessage);

		$output = $theme->output('site/helpers/widget/pages');

		return $output;
	}

	/**
	 * Widget for generating events on the sidebar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function events($events = array(), $emptyMessage = 'APP_USER_EVENTS_WIDGET_NO_EVENTS')
	{
		$emptyMessage = JText::_($emptyMessage);

		$theme = ES::themes();
		$theme->set('events', $events);
		$theme->set('emptyMessage', $emptyMessage);

		$output = $theme->output('site/helpers/widget/events');

		return $output;
	}

	/**
	 * Widget for generating events on the sidebar
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function videos($videos = array(), $emptyMessage = 'APP_VIDEOS_PROFILE_WIDGET_NO_VIDEOS_UPLOADED_YET', $layout = 'card')
	{
		$emptyMessage = JText::_($emptyMessage);

		$theme = ES::themes();
		$theme->set('videos', $videos);
		$theme->set('emptyMessage', $emptyMessage);
		$theme->set('showDuration', $this->config->get('video.layout.item.duration'));

		$namespace = 'site/helpers/widget/videos';

		if ($layout == 'card') {
			$namespace .= '.card';
		}

		$output = $theme->output($namespace);

		return $output;
	}

	/**
	 * Widget for audio on the sidebar
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function audios($audios = array(), $emptyMessage = 'APP_AUDIO_PROFILE_WIDGET_NO_AUDIO_UPLOADED_YET')
	{
		$emptyMessage = JText::_($emptyMessage);

		$theme = ES::themes();
		$theme->set('audios', $audios);
		$theme->set('emptyMessage', $emptyMessage);
		$theme->set('showDuration', $this->config->get('audio.layout.item.duration'));

		$output = $theme->output('site/helpers/widget/audios');

		return $output;
	}

	/**
	 * Renders the DOM structure for a widget view all link
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function viewAll($string, $link)
	{
		$string = JText::_($string);

		$theme = ES::themes();
		$theme->set('string', $string);
		$theme->set('link', $link);
		$output = $theme->output('site/helpers/widget/viewall');

		return $output;
	}
}
