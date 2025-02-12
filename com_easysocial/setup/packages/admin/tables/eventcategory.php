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

ES::import('admin:/tables/clustercategory');

class SocialTableEventCategory extends SocialTableClusterCategory
{

	/**
	 * Retrieve the permalink of the category filter page
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getFilterPermalink($xhtml = true, $external = false, $canonicalOptions = [])
	{
		$options = array('categoryid' => $this->getAlias());

		if ($external) {
			$options['external'] = true;
		}

		if ($canonicalOptions) {
			$options = array_merge($canonicalOptions);
		}

		$url = ESR::events($options, $xhtml);
		return $url;
	}


	/**
	 * Preprocess before calling parent::store();
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function store($updateNulls = false)
	{
		$this->type = SOCIAL_TYPE_EVENT;

		return parent::store($updateNulls);
	}

	/**
	 * Returns the total number of events in this category.
	 *
	 * @since  1.3
	 * @access public
	 */
	public function getTotalEvents($options = array())
	{
		static $total = array();

		$defaultOptions = array(
			'state' => SOCIAL_STATE_PUBLISHED,
			'type' => array(SOCIAL_EVENT_TYPE_PUBLIC, SOCIAL_EVENT_TYPE_PRIVATE),
			'category' =>  $this->id
		);

		if ($this->container) {
			// Get all child ids from this category
			$model = ES::model('ClusterCategory');
			$childs = $model->getChildCategories($this->id, array(), SOCIAL_TYPE_EVENT, array('state' => SOCIAL_STATE_PUBLISHED));

			$childIds = array();

			foreach ($childs as $child) {
				$childIds[] = $child->id;
			}

			if (!empty($childIds)) {
				$defaultOptions['category'] = $childIds;
			}
		}

		if (!isset($options['type'])) {
			$user = ES::user();
			$options['type'] = $user->isSiteAdmin() ? 'all' : 'user';
		}

		// If this is from page/group event listing
		// We need to get a correct count
		if (isset($options['cluster']) && $options['cluster']) {
			$cluster = $options['cluster'];

			if ($cluster->getType() == SOCIAL_TYPE_PAGE) {
				$options['page_id'] = $cluster->id;
			}
			if ($cluster->getType() == SOCIAL_TYPE_GROUP) {
				$options['group_id'] = $cluster->id;
			}

			// unset this option since already assign the cluster id to the above option.
			unset($options['cluster']);
		}

		$options = array_merge($defaultOptions, $options);

		ksort($options);

		$key = serialize($options);

		if (!isset($total[$this->id][$key])) {
			$total[$this->id][$key] = ES::model('events')->getTotalEvents($options);
		}

		return $total[$this->id][$key];
	}
}
