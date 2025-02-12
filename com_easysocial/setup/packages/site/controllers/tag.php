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

class EasySocialControllerTag extends EasySocialController
{
	/**
	 * Allows caller to create a new tag filter or update an existing one
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveFilter()
	{
		ES::requireLogin();
		ES::checkToken();

		// Get the filter item
		$id = $this->input->get('id', 0, 'int');
		$title = $this->input->get('title', '', 'default');
		$hashtag = trim($this->input->get('hashtag', '', 'default'));

		// Get the cluster id and type if available
		$cid = $this->input->get('cid', 0, 'int');
		$filterType = $this->input->get('filterType', '', 'string');
		$clusterType = $this->input->get('clusterType', '', 'string');

		$delete = $this->input->get('delete', false, 'bool');

		$view = ES::view(ucfirst($filterType), false);

		if (!$title && !$delete) {
			return $view->exception('COM_EASYSOCIAL_TAG_FILTER_WARNING_TITLE_EMPTY');
		}

		if (!$hashtag && !$delete) {
			return $view->exception('COM_EASYSOCIAL_TAG_FILTER_WARNING_HASHTAG_EMPTY');
		}

		$tag = ES::tag();

		if ($delete) {
			$tag->deleteFilter($id);

			$view->setMessage('COM_EASYSOCIAL_TAG_FILTER_DELETE_SUCCESS');

			return $view->call(__FUNCTION__, $cid, $clusterType);
		}

		// Bind the input
		$options = array(
			'id' => $id,
			'title' => $title,
			'hashtag' => $hashtag,
			'cid' => $cid,
			'filterType' => $filterType
		);


		// Save the filters
		$state = $tag->saveFilters($options);

		if (!$state) {
			$view->setMessage('COM_EASYSOCIAL_TAG_FILTER_SAVE_ERROR', ES_ERROR);

			// Pass this back to the view.
			return $view->call(__FUNCTION__, $cid, $clusterType);
		}

		$view->setMessage('COM_EASYSOCIAL_TAG_FILTER_SAVED', SOCIAL_MSG_SUCCESS);

		return $view->call(__FUNCTION__, $cid, $clusterType);
	}

	/**
	 * Allow caller to delete the filter
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function deleteFilter()
	{
		ES::requireLogin();
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$cid = $this->input->get('cid', 0, 'int');
		$filterType = $this->input->get('filterType', '', 'string');
		$clusterType = $this->input->get('clusterType', '', 'string');

		$view = ES::view(ucfirst($filterType), false);
		$tag = ES::tag();

		// Delete the tag
		$state = $tag->deleteFilter($id);

		if (!$state) {
			$view->setMessage('COM_EASYSOCIAL_TAG_FILTER_DELETE_ERROR', ES_ERROR);

			return $view->call(__FUNCTION__, $cid, $clusterType);
		}

		$view->setMessage('COM_EASYSOCIAL_TAG_FILTER_DELETE_SUCCESS');
		return $view->call(__FUNCTION__, $cid, $clusterType);
	}
}
