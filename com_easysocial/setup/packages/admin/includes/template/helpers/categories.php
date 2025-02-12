<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class ThemesHelperCategories extends ThemesHelperAbstract
{
	/**
	 * Render the categories for the sidebar listings
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function sidebar($type, $activeCategory, $categories = array(), $buildNested = true)
	{
		$theme = ES::themes();
		$hasAvatar = false;
		$displayCounters = true;

		// Clusters categories
		$clusters = array(SOCIAL_TYPE_EVENT, SOCIAL_TYPE_PAGE, SOCIAL_TYPE_GROUP);

		if (in_array($type, $clusters)) {

			$cluster = false;

			// If the caller is from cluster(page/group)
			if ($type == SOCIAL_TYPE_EVENT) {
				$eventCluster = $this->input->get('type', '', 'string');
				$uid = $this->input->get('uid', null, 'int');

				if ($eventCluster == SOCIAL_TYPE_PAGE || $eventCluster == SOCIAL_TYPE_GROUP) {
					$cluster = ES::cluster($eventCluster, $uid);
				}
			}

			if (!$categories) {
				$categories = ES::populateCategoriesTree($type, array(), array('state' => SOCIAL_STATE_PUBLISHED, 'buildNested' => $buildNested, 'cluster' => $cluster));
			}

			$hasAvatar = true;
		}

		// Media Categories (Audio/Video)
		$media = array(SOCIAL_TYPE_VIDEO, SOCIAL_TYPE_AUDIO);

		if (in_array($type, $media)) {
			if ($type == SOCIAL_TYPE_VIDEO) {
				$helper = ES::viewHelper('Videos', 'List');
				$displayCounters = $this->config->get('video.counters') ? true : false;
				$categories = $helper->getCategories($displayCounters);
			}

			if ($type == SOCIAL_TYPE_AUDIO) {
				$helper = ES::viewHelper('Audios', 'List');
				$displayCounters = $this->config->get('audio.counters') ? true : false;
				$categories = $helper->getGenres($displayCounters);
			}
		}

		if ($type == SOCIAL_TYPE_MARKETPLACE) {
			$options = array('state' => SOCIAL_STATE_PUBLISHED, 'buildNested' => $buildNested);

			// If the caller is from cluster(page/group)
			$listingType = $this->input->get('type', '', 'string');
			$uid = $this->input->get('uid', null, 'int');

			if ($listingType == SOCIAL_TYPE_PAGE || $listingType == SOCIAL_TYPE_GROUP) {
				$cluster = ES::cluster($listingType, $uid);
				$options['cluster'] = $cluster;
			} else {
				$options['user'] = $uid;
			}

			$categories = ES::populateCategoriesTree($type, array(), $options);

			$hasAvatar = true;
		}

		$namespace = 'site/helpers/categories/menu';

		if ($this->isMobile()) {
			$namespace = 'site/helpers/categories/menu.mobile';
		}

		$theme->set('categories', $categories);
		$theme->set('type', $type);
		$theme->set('activeCategory', $activeCategory);
		$theme->set('hasAvatar', $hasAvatar);
		$theme->set('displayCounters', $displayCounters);

		$content = $theme->output($namespace);

		return $content;
	}
}
