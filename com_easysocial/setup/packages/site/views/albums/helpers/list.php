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

class EasySocialViewAlbumsListHelper extends EasySocial
{
	/**
	 * Determine if the view is All type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isViewAll()
	{
		// Check if the current request is made for the current logged in user or another user.
		$uid = $this->getUid();
		$type = $this->getType();

		// When someone tries to view all albums
		if (is_null($uid) && is_null($type)) {

			$layout = $this->getLayout();

			if (!$layout || ($layout && $layout == 'all')) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the current layout is for favourite
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function isViewFavourite()
	{
		$uid = $this->getUid();
		$type = $this->getType();

		if (is_null($uid) && is_null($type)) {
			$layout = $this->getLayout();

			if ($layout == 'favourite') {
				return true;
			}
		}

		return false;
	}

	public function isViewItem()
	{
		if ($this->isViewAll() || $this->isViewFavourite()) {
			return false;
		}

		return true;
	}

	/**
	 * Get the layout type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getLayout()
	{
		return $this->input->get('layout', '', 'default');
	}

	/**
	 * Retrieve albums library
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getAlbumsLibrary()
	{
		static $lib = null;

		if (is_null($lib)) {

			if ($this->isViewAll() || $this->isViewFavourite()) {
				$lib = ES::albums(ES::user()->id, SOCIAL_TYPE_USER);
			} else {

				// Check if the current request is made for the current logged in user or another user.
				$uid = $this->getUid();
				$type = $this->getType();

				$lib = ES::albums($uid, $type);
			}
		}

		return $lib;
	}

	/**
	 * Retrieve uid if available
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getUid()
	{
		return $this->input->get('uid', null, 'int');
	}

	/**
	 * Retrieve the type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getType()
	{
		return $this->input->get('type', null, 'cmd');
	}

	/**
	 * Get the filter type
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilter()
	{
		if ($this->isViewAll()) {
			return 'all';
		}

		if ($this->isViewFavourite()) {
			return 'favourite';
		}

		$filter = $this->input->get('filter', '', 'default');

		return $filter;
	}

	/**
	 * Determines the current sorting type from the listing page
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public function getSort()
	{
		static $sort = null;

		if (is_null($sort)) {
			$sort = $this->input->get('sort', '', 'word');
		}

		return $sort;
	}

	/**
	 * Determines the current limitstart from the listing page
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public function getLimitstart()
	{
		static $limitstart = null;

		if (is_null($limitstart)) {
			$limitstart = $this->input->get('limitstart', '', 'int');
		}

		return $limitstart;
	}

	/**
	 * Generates the canonical options on the page
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public function getCanonicalOptions()
	{
		static $options = null;

		if (is_null($options)) {
			$options = array('external' => true);

			$layout = $this->input->get('layout', '', 'cmd');

			if ($layout === 'favourite' || $layout === 'mine') {
				$options['layout'] = $layout;
			}

			$uid = $this->getUid();
			$type = $this->getType();

			// Only for clusters page title
			if ($uid && $type && $type != SOCIAL_TYPE_USER) {
				$cluster = ES::cluster($type, $uid);

				$options['type'] = $type;
				$options['uid'] = $cluster->getAlias();
			}

			if ($type === SOCIAL_TYPE_USER && $uid) {
				$options['type'] = SOCIAL_TYPE_USER;
				$options['uid'] = ES::user($uid)->getAlias();
			}

			$sort = $this->getSort();

			if ($sort) {
				$options['sort'] = $sort;

				// Exclude these ordering and sorting page shouldn't get index by search engine advised by Google
				$this->doc->setMetadata('robots', 'noindex,follow');
			}

			$limitstart = $this->getLimitstart();

			if ($limitstart) {
				$options['limitstart'] = $limitstart;
			}
		}

		return $options;
	}

	/**
	 * Generates the canonical url for the current albums listing
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public function getCanonicalUrl()
	{
		static $url = null;

		if (is_null($url)) {
			$options = $this->getCanonicalOptions();

			$url = ESR::albums($options);
		}

		return $url;
	}

	/**
	 * Retrieves the total number of albums created on the site.
	 *
	 * @since	4.0.10
	 * @access	public
	 */
	public function getTotalAlbums()
	{
		static $total = null;

		if (is_null($total)) {
			$options = [
				'core' => false,
				'privacy' => true,
				'withCovers' => true,
				'excludeblocked' => true,
				'countOnly' => true
			];

			$model = ES::model('Albums');
			$total = $model->getAlbums('', SOCIAL_TYPE_USER, $options);
		}

		return $total;
	}

	/**
	 * Retrieves the total number of albums by current user from the site
	 *
	 * @since	4.0.10
	 * @access	public
	 */
	public function getTotalMyFavouriteAlbums()
	{
		static $total = null;

		if (is_null($total)) {
			$options = [
				'core' => false,
				'favourite' => true,
				'userFavourite' => $this->my->id,
				'countOnly' => true
			];

			$model = ES::model('Albums');
			$total = $model->getAlbums('', SOCIAL_TYPE_USER, $options);
		}

		return $total;
	}

	/**
	 * Retrieves the total number of all favourite albums by current user from the site
	 *
	 * @since	4.0.10
	 * @access	public
	 */
	public function getTotalMyAlbums()
	{
		static $total = null;

		if (is_null($total)) {
			$options = [
				'core' => false,
				'privacy' => true,
				'countOnly' => true
			];

			$model = ES::model('Albums');
			$total = $model->getAlbums($this->my->id, SOCIAL_TYPE_USER, $options);
		}

		return $total;
	}
}