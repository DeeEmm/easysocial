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

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelDownload extends EasySocialModel
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Retrieves download requests
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getRequests($options = array())
	{
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT * FROM ' . $db->qn('#__social_download');

		// Determines if we need to order the items by column.
		$ordering = isset($options['ordering']) ? $options['ordering'] : '';

		$limit = $this->getState('limit');

		// Get the limitstart.
		$limitstart = $this->getUserStateFromRequest('limitstart' , 0);
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limitstart' , $limitstart);

		// Set the total number of items.
		$countQuery = implode(' ', $query);
		$this->setTotal($countQuery, true);

		// Get the list of users
		$rows = $this->getData($query);

		if (!$rows) {
			return array();
		}
		
		$requests = array();

		foreach ($rows as $row) {
			$request = ES::table('Download');
			$request->bind($row);

			$requests[] = $request;
		}
		
		return $requests;
	}

	/**
	 * Method to retrieve records pending for cron processing.
	 *
	 * @since 2.2
	 * @access public
	 */
	public function getCronDownloadReq($max = 1)
	{
		$db = ES::db();

		$query = "select * from `#__social_download`";
		$query .= " where `state` IN (" . $db->Quote(ES_DOWNLOAD_REQ_NEW) . ',' . $db->Quote(ES_DOWNLOAD_REQ_PROCESS) . ")";
		$query .= " order by `id`";

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}


	/**
	 * Method to retrieve records pending for cron processing.
	 *
	 * @since 2.1.11
	 * @access public
	 */
	public function getExpiredRequest($max = 10)
	{
		$db = ES::db();
		$config = ES::config();

		$days = $config->get('users.download.expiry', 14);
		$now = ES::date()->toMySQL();

		$query = "select a.* from `#__social_download` as a";
		$query .= " where a.`state` = " . $db->Quote(ES_DOWNLOAD_REQ_READY);
		$query .= " and a.`created` <= DATE_SUB(" . $db->Quote($now) . ", INTERVAL " . $days . " DAY)";
		$query .= " order by `id`";

		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	/**
	 * Removes all download requests and delete the files
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function purgeRequests()
	{
		$db = ES::db();
		$query = array();
		$query[] = 'DELETE FROM ' . $db->qn('#__social_download');

		$db->setQuery($query);
		$db->Query();

		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		$folders = JFolder::folders(SOCIAL_GDPR_DOWNLOADS, '.', false, true);

		if ($folders) {
			foreach ($folders as $folder) {
				JFolder::delete($folder);
			}
		}

		$files = JFolder::files(SOCIAL_GDPR_DOWNLOADS, '.', false, true);

		if ($files) {
			foreach ($files as $file) {
				JFile::delete($file);
			}
		}

		return true;
	}
}