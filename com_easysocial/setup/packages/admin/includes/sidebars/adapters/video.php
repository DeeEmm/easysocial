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

require_once(__DIR__ . '/abstract.php');
require_once(__DIR__ . '/traits.php');

class SocialSidebarsAdapterVideo extends SocialSidebarAdapter
{
	use SocialSidebarTrait;

	public function __construct($uid, $utype)
	{
		parent::__construct($uid, $utype);
	}

	/**
	 * render videos filters section
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function renderVideos($filter = '')
	{
		$uid = $this->uid;
		$type = $this->type;

		// set the value into input so that
		// video list helper can access these value
		$this->input->set('filter', $filter);


		// get the require data.
		$helper = ES::viewHelper('Videos', 'List');
		$adapter = $helper->getAdapter();
		$filter = $helper->getCurrentFilter();
		$titles = $helper->getPageTitles();
		$counters = $helper->getCounters();
		$filtersAcl = $helper->getFiltersAcl();

		$themes = ES::themes();

		$themes->set('adapter', $adapter);
		$themes->set('filter', $filter);
		$themes->set('titles', $titles);
		$themes->set('counters', $counters);
		$themes->set('filtersAcl', $filtersAcl);

		$html = $themes->output('site/videos/default/sidebar.filters');
		return $html;
	}

	/**
	 * render videos categories section
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function renderCategories($category = '')
	{
		$this->input->set('categoryId', $category);

		$activeCategory = false;

		if ($category) {
			$activeCategory = ES::table('VideoCategory');
			$activeCategory->load($category);
		}

		$html = ES::template()->html('categories.sidebar', SOCIAL_TYPE_VIDEO, $activeCategory);
		return $html;
	}
}
