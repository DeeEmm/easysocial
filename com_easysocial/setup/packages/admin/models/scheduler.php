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

ES::import('admin:/includes/model');

class EasySocialModelScheduler extends EasySocialModel
{
	public function __construct()
	{
		parent::__construct('scheduler');
	}

	/**
	 * Retrieving scheduled streams for crons publishing.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getCronScheduledStream()
	{	
		$items = $this->getScheduledStream(true, array('limit', 10));

		if (empty($items)) {
			return array();
		}

		$streamToPublish = array();

		foreach ($items as $item) {
			$stream = ES::table('Stream');
			$stream->load($item->stream_id);

			$streamItem = ES::table('StreamItem');
			$streamItem->load(array('uid' => $stream->id));

			$scheduled = ES::table('StreamScheduled');
			$scheduled->bind($item);

			$streamToPublish[] = array(
				'stream' => $stream,
				'streamItem' => $streamItem,
				'scheduled' => $scheduled
			);			
		}

		return $streamToPublish;
	}

	/**
	 * Retrieving scheduled streams.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getScheduledStream($pending = false, $options = array())
	{
		$db = ES::db();
		$query = array();

		$date = ES::date();

		$query[] = 'SELECT * FROM `#__social_stream_scheduled`';

		if ($pending) {
			$query[] = 'WHERE `scheduled` <= ' . $db->Quote($date->toSql());
		}

		if (isset($options['limit']) && $options['limit']) {
			$query[] = 'LIMIT 10';
		}

		$query = implode(' ', $query);
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}