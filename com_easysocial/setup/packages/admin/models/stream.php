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

class EasySocialModelStream extends EasySocialModel
{
	private $data = null;
	private $nextdate = null;
	private $enddate = null;
	private $paginationdate = null;
	private $uids = null; // stream ids
	protected $pagination = null;
	protected $total = null;

	//used in queries optmisation.
	static $_relateditems = array();
	static $_activitylogs = array();
	static $_tagging = array();

	// stream pagination sorting
	private $streamPaginationSort = '';
	private $nextdatelimit = '';

	public function __construct()
	{
		parent::__construct('stream');

		$config = ES::config();
		$this->streamPaginationSort = $config->get('stream.pagination.ordering');
	}

	public function initStates()
	{
		parent::initStates();

		$state = $this->getUserStateFromRequest('state', 'all');

		$this->setState('state', $state);
	}

	public function getItemsWithState($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		// Determines if user is filtering the items
		$state = $this->getState('state');

		$sql->column('a.*');
		$sql->column('b.name', 'actorName');
		$sql->column('c.title', 'clusterName');

		if ($state == SOCIAL_STREAM_STATE_SCHEDULED) {
			$sql->column('sc.id', 'scheduledId');
			$sql->column('sc.scheduled', 'scheduledDate');
			$sql->column('sc.modified', 'scheduledModified');
		}

		if ($state == SOCIAL_STREAM_STATE_ARCHIVED) {
			$sql->select('#__social_stream_history', 'a');
		} else {
			$sql->select('#__social_stream', 'a');
		}

		$sql->join('#__users', 'b', 'INNER');
		$sql->on('a.actor_id', 'b.id');

		$sql->join('#__social_clusters', 'c', 'LEFT');
		$sql->on('a.cluster_id', 'c.id');

		if ($state == SOCIAL_STREAM_STATE_SCHEDULED) {
			$sql->join('#__social_stream_scheduled', 'sc', 'inner');
			$sql->on('a.id', 'sc.stream_id');
		}

		if(!is_null($state) && $state != SOCIAL_STREAM_STATE_ARCHIVED) {
			if ($state == 'all') {
				$sql->where('(', '', '', 'AND');
				$sql->where('a.state', SOCIAL_STREAM_STATE_PUBLISHED, '=', 'OR');
				$sql->where('a.state', SOCIAL_STREAM_STATE_RESTORED, '=', 'OR');
				$sql->where(')');
			} else {
				$sql->where('a.state' , $state);
			}
		}

		// Ordering
		$ordering = $this->getState('ordering', 'created');

		if ($ordering) {
			$direction = $this->getState('direction') ? $this->getState('direction') : 'DESC';

			$sql->order($ordering, $direction);
		}

		// Set the total
		$this->setTotal($sql->getTotalSql());

		// Debug
		// echo str_replace('#_', 'jos', $sql->getSql());exit;

		$result = parent::getData($sql->getSql());

		$this->pagination = parent::getPagination();

		return $result;
	}


	public function exists($uid, $context, $verb, $actorId, $options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		// options
		$actorType = isset($options['actortype']) ? $options['actortype'] : SOCIAL_TYPE_USER;


		$query = "select `id` from `#__social_stream_item`";
		$query .= " where `actor_id` = '$actorId'";
		$query .= " and `actor_type`='$actorType'";
		$query .= " and `context_type`='$context'";
		$query .= " and `context_id`='$uid'";
		$query .= " and `verb`='$verb'";

		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadResult();

		return $result ? true : false;
	}

	/**
	 * Allows caller to hide a stream item
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hide($ids, $userId)
	{
		if (!$ids) {
			return false;
		}

		$ids = ES::makeArray($ids);

		$db = ES::db();

		foreach ($ids as $cid) {

			$table = ES::table('StreamHide');
			$table->user_id = $userId;
			$table->uid = $cid;
			$table->type = SOCIAL_STREAM_HIDE_TYPE_STREAM;

			if (!$table->store()) {
				return false;
			}

			// Since this stream might consist of several activity logs, then we will need to 'hide' them all as well.
			$query = 'SELECT ' . $db->qn('id') . ' FROM ' . $db->qn('#__social_stream_item');
			$query .= ' WHERE ' . $db->qn('uid') . '=' . $db->Quote($cid);
			$db->setQuery($query);

			$items = $db->loadObjectList();

			if ($items) {
				foreach ($items as $item) {
					$table = ES::table('StreamHide');
					$table->uid = $item->id;
					$table->user_id = $userId;
					$table->type = SOCIAL_STREAM_HIDE_TYPE_ACTIVITY;
					$table->store();
				}
			}
		}

		return true;
	}


	/**
	 * Allows caller to hide stream context from specific users
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function hideapp($context, $userId)
	{
		if (!$context) {
			return false;
		}

		$db = ES::db();

		$tbl = ES::table('StreamHide');
		$tbl->user_id 	= $userId;
		$tbl->uid = '0';
		$tbl->type = '';
		$tbl->context = $context;

		if (!$tbl->store()) {
			return false;
		}

		return true;
	}


	public function hideactor($actor_id, $userId)
	{
		if(empty($actor_id))
			return false;


		$db = ES::db();

		$tbl = ES::table('StreamHide');
		$tbl->user_id 	= $userId;
		$tbl->uid 		= '0';
		$tbl->type 		= '';
		$tbl->actor_id 	= $actor_id;

		if(! $tbl->store())
		{
			return false;
		}

		return true;
	}

	public function unhideactor($actor_id, $userId)
	{
		if(empty($actor_id))
			return false;


		$db = ES::db();

		$delQuery = 'delete from ' . $db->nameQuote('#__social_stream_hide') . ' where ' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($actor_id);
		$delQuery .= ' and ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);

		$db->setQuery($delQuery);
		$db->query();

		return true;
	}



	/**
	 * Retrieves the past 7 days statistics for all postings by specific user
	 *
	 * @since	1.0
	 * @access	public
	 * @return	Array
	 */
	public function getPostStats($dates , $userId)
	{
		$db 	= ES::db();
		$posts	= array();

		foreach($dates as $date)
		{
			// Registration date should be Y, n, j
			$date	= ES::date($date)->format('Y-m-d');

			$query 		= array();
			$query[] 	= 'SELECT `a`.`id`, COUNT(`a`.`id`) AS `cnt` FROM `#__social_stream` AS a';
			$query[]	= 'WHERE `a`.`actor_id`=' . $db->Quote($userId);
			$query[]	= 'AND `a`.`actor_type`=' . $db->Quote(SOCIAL_TYPE_USER);
			$query[]	= 'AND DATE_FORMAT(`a`.`created`, GET_FORMAT(DATE , "ISO")) = ' . $db->Quote($date);
			$query[]    = 'group by a.`actor_id`';

			$query 		= implode(' ' , $query);

			$sql		= $db->sql();
			$sql->raw($query);

			$db->setQuery($sql);

			$items				= $db->loadObjectList();

			// There is nothing on this date.
			if(!$items)
			{
				$posts[]	= 0;
				continue;
			}

			foreach($items as $item)
			{
				$posts[]	= $item->cnt;
			}
		}

		// Reset the index.
		$posts 	= array_values($posts);

		return $posts;
	}

	public function unhideapp($context, $userId)
	{
		if(empty($context))
			return false;


		$db = ES::db();

		$delQuery = 'delete from ' . $db->nameQuote('#__social_stream_hide') . ' where ' . $db->nameQuote('context') . ' = ' . $db->Quote($context);
		$delQuery .= ' and ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);

		$db->setQuery($delQuery);
		$db->query();

		return true;
	}

	/**
	 * Deletes stream items given the context type and context id.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete($contextId, $contextType, $actorId = '', $verb = '')
	{
		$db = ES::db();

		// Get a list of items from the item table first.
		$sql = $db->sql();

		$sql->select('#__social_stream_item');
		$sql->where('context_id', $contextId);
		$sql->where('context_type', $contextType);

		if ($actorId) {
			$sql->where('actor_id', $actorId);
		}

		if ($verb) {
			$sql->where('verb', $verb);
		}

		$db->setQuery($sql);

		$items = $db->loadObjectList();

		if (!$items) {
			return false;
		}


		// Delete from #__social_stream_item
		$sql->clear();

		$sql->delete('#__social_stream_item');
		$sql->where('context_id', $contextId);
		$sql->where('context_type', $contextType);

		if ($actorId) {
			$sql->where('actor_id' , $actorId);
		}

		if ($verb) {
			$sql->where('verb', $verb);
		}

		$db->setQuery($sql);
		$db->Query();

		// lets check if the UID has more than one item or not. If yes, then we shouldn't
		// delete the master record.

		foreach ($items as $item) {

			// Delete from #__social_stream
			$sql->clear();

			$sql->select('#__social_stream_item');
			$sql->column('count(1)', 'cnt');
			$sql->where('uid', $item->uid);

			$db->setQuery($sql);

			$count = $db->loadResult();

			// Delete the parent stream item if necessary (If it is aggregated, we should check if there's any child left)
			if ($count <= 0) {

				// Remove comments and reactions
				$query = "delete a, b, c, d";
				$query .= " from `#__social_stream` as a";
				// remove all associated comments;
				$query .= " left join `#__social_comments` as b on a.`id` = b.`stream_id`";
				// remove all associated reaction;
				$query .= " left join `#__social_likes` as c on a.`id` = c.`stream_id`";
				// remove all associated hashtags;
				$query .= " left join `#__social_stream_tags` as d on a.`id` = d.`stream_id`";
				$query .= " where a.`id` = " . $db->Quote($item->uid);

				$db->setQuery($query);
				$db->query();


				// now lets try to delete the notifications that point to this stream.
				$url = '%view=stream&id=' . $item->uid . '&layout=item%';

				$query = "delete from `#__social_notifications` where `url` LIKE " . $db->Quote($url);
				$db->setQuery($query);
				$db->query();
				
			}
		}

		// now we delete any 'left overs' reactions on those deleted comments
		$query = "delete a";
		$query .= " from `#__social_likes` as a";
		$query .= "	left join `#__social_comments` as b on a.`uid` = b.`id`";
		$query .= " where a.`type` = " . $db->Quote('comments.user.like');
		$query .= " and b.`id` is null";

		$db->setQuery($query);
		$db->query();

		return true;
	}

	public function unhide($ids, $userId)
	{
		if(empty($ids))
			return false;

		if(! is_array($ids))
		{
			$ids = array($ids);
		}


		$db = ES::db();

		foreach($ids as $cid)
		{
			$delQuery = 'delete from ' . $db->nameQuote('#__social_stream_hide') . ' where ' . $db->nameQuote('uid') . ' = ' . $db->Quote($cid);
			$delQuery .= ' and ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);
			$delQuery .= ' and ' . $db->nameQuote('type') . ' = ' . $db->Quote(SOCIAL_STREAM_HIDE_TYPE_STREAM);

			$db->setQuery($delQuery);
			$db->query();


			//since this stream might consist of several activity logs, then we will need to 'hide' them all as well.
			$query = 'select ' . $db->nameQuote('id') . ' from ' . $db->nameQuote('#__social_stream_item') . ' where ' . $db->nameQuote('uid') . ' = ' . $db->Quote($cid);
			$db->setQuery($query);

			$items = $db->loadObjectList();

			if(count($items) > 0)
			{
				$itemIds = array();
				foreach($items as $item)
				{
					$itemIds[] = $item->id;
				}

				$strIds = implode(',', $itemIds);

				$delQuery = 'delete from ' . $db->nameQuote('#__social_stream_hide') . ' where ' . $db->nameQuote('uid') . ' IN (' . $strIds . ')';
				$delQuery .= ' and ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);
				$delQuery .= ' and ' . $db->nameQuote('type') . ' = ' . $db->Quote(SOCIAL_STREAM_HIDE_TYPE_ACTIVITY);

				$db->setQuery($delQuery);
				$db->query();

			}
		}

		return true;
	}

	public function getItems()
	{
		$search 		= ES::get('Themes')->getUserStateFromRequest('com_easysocal.stream.search', 'search', '', 'string');
		$actor_type 	= ES::get('Themes')->getUserStateFromRequest('com_easysocal.stream.actor_type', 'actor_type', '', 'string');
		$context_type 	= ES::get('Themes')->getUserStateFromRequest('com_easysocal.stream.context_type', 'context_type', '', 'string');

		$db = ES::db();
		$where  = array();
		//if(!empty($search))
		//	$where[]    = ' a.`actor_id` = ' . $this->_db->Quote($search);

		if(!empty($actor_type))
			$where[]    = 'a.' . $db->nameQuote('actor_type') . ' = ' . $db->Quote($actor_type);

		if(!empty($context_type))
			$where[]    = 'a.' . $db->nameQuote('context_type') . ' = ' . $db->Quote($context_type);

		$extra 		= (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');

		$CountHeader  = 'select count(1)';

		$header  = 'select a.*, b.' . $db->nameQuote('name') . ' as ' . $db->nameQuote('actor_name') . ',';
		$header  .= ' FLOOR(TIME_TO_SEC(TIMEDIFF(NOW(), a.' . $db->nameQuote('created') . ')) / 60) AS ' . $db->nameQuote('mindiff') . ',';
		$header  .= ' FLOOR(TIME_TO_SEC(TIMEDIFF(NOW(), a.' . $db->nameQuote('created') . ')) / 60 / 60) AS ' . $db->nameQuote('hourdiff') . ',';
		$header  .= ' FLOOR(TIME_TO_SEC(TIMEDIFF(NOW(), a.' . $db->nameQuote('created') . ')) / 60 / 60 / 24) AS ' . $db->nameQuote('daydiff');

		$query  = ' from ' . $db->nameQuote('#__social_stream') . ' as a';
		$query  .= '   left join ' . $db->nameQuote('#__users') . ' as b on a.' . $db->nameQuote('source_id') . ' = b.' . $db->nameQuote('id') . ' and a.' . $db->nameQuote('actor_type') . ' = ' . $db->Quote('people');
		$query  .= $extra;
		$query  .= ' order by a.' . $db->nameQuote('created desc');

		$mainSQL    = $header   . $query;

		$countSQL   = $CountHeader . $query;
		$this->setTotal($countSQL);

		// echo $mainSQL;

		return $this->getStreamData($mainSQL);
	}

	public function getPagination($reload = null)
	{
		return $this->pagination;
	}

	public function getType($type)
	{
		$db = ES::db();
		$targetType = '';

		switch($type)
		{
			case 'source':
				$targetType = 'actor_type';
				break;
			case 'context':
				$targetType = 'context_type';
				break;
			default:
				break;
		}

		if(empty($targetType))
			return;

		$query  = 'SELECT DISTINCT ' . $db->nameQuote($targetType);
		$query  .= ' FROM  ' . $db->nameQuote('#__social_stream');

		return $this->getStreamData($query, false);
	}

	public function getTotalCount()
	{
		return $this->total;
	}

	public function getNextEndDate()
	{
		return $this->enddate;
	}

	public function getNextStartDate()
	{
		return $this->nextdate;
	}

	public function getNextDateLimit()
	{
		return $this->nextdatelimit;
	}

	public function getCurrentStartDate()
	{
		//use the current datetime
		return ES::date()->toMySQL();
	}

	public function getUids()
	{
		//stream ids
		return $this->uids;
	}

	/**
	 * Retrive the start date and end date used in query limit.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string - start date (in mysql date format)
	 * @return	array - startdate and enddate
	 */

	private function getLimitDates($startdate, $enddate, $tables, $conds = array(), $direction = 'older')
	{
		$config = ES::config();
		$db 	= ES::db();

		$fetchLimit = $config->get('stream.pagination.limit');
		$sortDate   = $this->streamPaginationSort;

		// $fetchLimit = 10080 + 10080;
		// $fetchLimit = 5;

		$dates      = array();
		$countConds = $conds;

		//use last modified date from stream.
		// $query = 'SELECT MAX(a.' . $db->nameQuote('modified') . ') AS ' . $db->nameQuote('startdate') . ',';
		// $query .= ' DATE_ADD(MAX(a.' . $db->nameQuote('modified') . ')  , INTERVAL -' . $fetchLimit . ' MINUTE) AS ' . $db->nameQuote('enddate') . ' ';

		$query = 'SELECT a.' . $db->nameQuote('modified') . ' AS ' . $db->nameQuote('startdate') . ',';
		$query .= ' DATE_ADD(a.' . $db->nameQuote('modified') . ' , INTERVAL -' . $fetchLimit . ' MINUTE) AS ' . $db->nameQuote('enddate') . ' ';

		if($startdate)
		{
			if($direction == 'later')
			{
				$conds[] = ' and a.' . $db->nameQuote($sortDate) . ' >= ' . $db->Quote($startdate);
			}
			else
			{
				$conds[] = ' and a.' . $db->nameQuote($sortDate) . ' < ' . $db->Quote($startdate);
			}
		}

		//unset the index 1, 2 and 3 to remove the join on location, mood and users table.
		unset($tables[1]);
		unset($tables[2]);

		if (strpos($tables[3] , '#__users') !== false) {
			unset($tables[3]);
		}


		$tables 	= implode(' ', $tables);
		$conds 		= implode(' ' , $conds);

		$ordering = ' order by a.`modified` DESC LIMIT 1';

		$query .= $tables . ' ' . $conds . $ordering;

		// echo $query . '<br /><br />';
		// exit;

		$db->setQuery($query);
		$data = $db->loadObject();


		// var_dump($data);

		if(isset($data->startdate))
		{
			$dates['startdate'] 		= $data->startdate;
			$dates['paginationdate'] 	= $data->startdate;
		}

		if($enddate)
		{
			$dates['enddate'] = $enddate;
		}
		else
		{
			if(isset($data->enddate))
			{
				$dates['enddate'] = $data->enddate;
			}
		}


		// now lets test whether the next set of dates has data or not.
		if(isset($data->enddate) && $direction != 'later')
		{
			$limiting = ' LIMIT 1';

			$query = 'select a.`id`';
			$countConds[] = ' and a.' . $db->nameQuote($sortDate) . ' < ' . $db->Quote($data->enddate);

			// joining the condition
			$countConds = implode(' ' , $countConds);

			$query .= $tables . ' ' . $countConds . $limiting;

			$db->setQuery($query);
			$data = $db->loadResult();
			if(empty($data))
			{
				$dates['paginationdate'] = false;
			}

			// always return true to skid the count check to improve the page load speed.
			// $dates['paginationdate'] = true;
		}

		return $dates;
	}


	// called in backend dashboard.
	public function getRecentFeeds($maxCnt = 10)
	{
		$db = ES::db();

		$config  	= ES::config();
		$sortDate   = $this->streamPaginationSort;

		if(empty($maxCnt))
			$maxCnt = 10;

		$query	= 'SELECT a.*';
		$query	.= ', FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.' . $db->nameQuote('modified') . ')) / 60) AS ' . $db->nameQuote('min');
		$query	.= ', FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.' . $db->nameQuote('modified') . ')) / 60 / 60) AS ' . $db->nameQuote('hour');
		$query	.= ', FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.' . $db->nameQuote('modified') . ')) / 60 / 60 / 24) AS ' . $db->nameQuote('day');
		$query	.= ' FROM ' . $db->nameQuote('#__social_stream') . ' AS a';
		$query	.= ' ORDER BY a.' . $db->nameQuote($sortDate) . ' DESC';
		$query  .= ' LIMIT ' . $maxCnt;

		$db->setQuery($query);

		$result 	= $db->loadObjectList();
		return $result;
	}

	private function getStreamTableAlias($userId, $type, $options = array())
	{
		$userStickyOnly = ES::normalize($options, 'userStickyOnly', false);
		$useDate = ES::normalize($options, 'useDate', false);
		$direction = ES::normalize($options, 'direction', '');
		$startdate = ES::normalize($options, 'startdate', null);
		$enddate = ES::normalize($options, 'enddate', null);
		$customView = ES::normalize($options, 'customView', false);
		$context = ES::normalize($options, 'context', SOCIAL_STREAM_CONTEXT_TYPE_ALL);
		$anywhereId = ES::normalize($options, 'anywhereId', false);
		$isScheduled = ES::normalize($options, 'isScheduled', false);

		// apps that other user can post on my profile timeline
		$profileApps = array('friends' => 'add',
							'story' => 'create',
							'photos' => 'share',
							'links' => 'create',
							'files' => 'create',
							'videos' => 'create',
							'reviews' => 'create');

		$db = ES::db();
		$view = ($customView) ? $customView : $this->input->get('view', '', 'string');

		$config = ES::config();
		$sortDate = $this->streamPaginationSort;

		$joinFriends = ($view == 'dashboard' || $anywhereId) && !$userStickyOnly && ES::config()->get('friends.enabled');

		$streamTableAlias = '(';
		$streamTableAlias .= ' select a1.* from ' . $db->nameQuote('#__social_stream') . ' as a1';

		if ($joinFriends) {

			$streamTableAlias .= ' INNER JOIN (';
			$streamTableAlias .= '	select target_id as fid from `#__social_friends` where `actor_id` = ' . $db->Quote($userId) . ' and `state` = 1';
			$streamTableAlias .= '		UNION ALL ';
			$streamTableAlias .= '	select actor_id as fid from `#__social_friends` where `target_id` = ' . $db->Quote($userId) . ' and `state` = 1';
			$streamTableAlias .= '		UNION ALL ';
			$streamTableAlias .= '	select ' . $db->Quote($userId) . ' as fid';
			$streamTableAlias .= ' ) as fren on a1.`actor_id` = fren.`fid`';
		}

		$streamTableAlias .= ' where ' . $db->nameQuote('actor_type') . ' = ' . $db->Quote($type);

		if (!$joinFriends) {
			$streamTableAlias .= ' and ' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($userId);
		}

		// [Page] Here we have to exclude user's posts that post_as page
		if ($view == 'profile') {
			$streamTableAlias .= ' and ' . $db->nameQuote('post_as') . ' = ' . $db->Quote(SOCIAL_TYPE_USER);
		}

		// We'll only get the scheduled post.
		if ($isScheduled) {
			$streamTableAlias .= ' and `state` = ' . $db->Quote(SOCIAL_STREAM_STATE_SCHEDULED);
		}

		if (! $userStickyOnly) {
			$streamTableAlias .= ' UNION ';

			// tagged item
			
			// $streamTableAlias .= ' SELECT a2.* FROM `#__social_stream` AS a2';
			// $streamTableAlias .= ' WHERE a2.`actor_type` = ' . $db->Quote(SOCIAL_TYPE_USER);
			// $streamTableAlias .= ' AND a2.`target_id` = ' . $db->Quote($userId);

			// $inQuery = '';
			// foreach ($profileApps as $contextType => $verb) {
			// 	$join = ($inQuery) ? ',' : '';
			// 	$inQuery .= $join . $db->Quote($contextType);
			// }

			// $streamTableAlias .= ' and a2.`context_type` IN (' . $inQuery . ')';

			// old query

			$streamTableAlias .= 'select a2.* from ' . $db->nameQuote('#__social_stream') . ' as a2 ';
			$streamTableAlias .= ' inner join ' . $db->nameQuote('#__social_stream_item') . ' as ai2 on a2.' . $db->nameQuote('id') . ' = ai2.' . $db->nameQuote('uid');
			$streamTableAlias .= ' where a2.' . $db->nameQuote('actor_type') . ' = ' . $db->Quote($type);

			$inQuery = '';
			$orQuery = '';
			foreach ($profileApps as $contextType => $verb) {
				$join = ($inQuery) ? ',' : '';
				$inQuery .= $join . $db->Quote($contextType);

				$orJoin = ($orQuery) ? ' or ' : '';
				$orQuery .= $orJoin . '(ai2.' . $db->nameQuote('context_type') . ' = ' . $db->Quote($contextType) . ' and ai2.' . $db->nameQuote('verb') . ' = ' . $db->Quote($verb) . ')';
			}

			$streamTableAlias .= ' and a2.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($userId) . ' and a2.' . $db->nameQuote('context_type') . ' IN (' . $inQuery . ')';
			$streamTableAlias .= ' and (';
			$streamTableAlias .= $orQuery;
			$streamTableAlias .= '	)';

		}

		if ($useDate) {
			if ($direction == 'later') {
				$streamTableAlias .= ' AND a2.' . $db->nameQuote($sortDate) . ' >= ' . $db->Quote($startdate);
			} else {
				$streamTableAlias .= ' AND (a2.' . $db->nameQuote($sortDate) . ' <= ' . $db->Quote($startdate) . ' AND a2.' . $db->nameQuote($sortDate) . ' >= ' . $db->Quote($enddate) . ')';
			}
		}

		// if (($view == 'dashboard' || $anywhereId) && !$userStickyOnly && ES::config()->get('friends.enabled')) {

		// 	$streamTableAlias .= ' UNION ';

		// 	// friends item
		// 	$streamTableAlias .= ' SELECT a4.*';
		// 	$streamTableAlias .= ' FROM `#__social_stream` AS a4';
		// 	$streamTableAlias .= ' INNER JOIN (';
		// 	$streamTableAlias .= '	select target_id as fid from `#__social_friends` where `actor_id` = ' . $db->Quote($userId) . ' and `state` = 1';
		// 	$streamTableAlias .= '		union all ';
		// 	$streamTableAlias .= '	select actor_id as fid from `#__social_friends` where `target_id` = ' . $db->Quote($userId) . ' and `state` = 1';
		// 	$streamTableAlias .= ' ) as fren on a4.`actor_id` = fren.`fid`';
		// 	$streamTableAlias .= ' WHERE a4.`post_as` = ' . $db->Quote(SOCIAL_TYPE_USER);

		// }

		$streamTableAlias .= ' UNION ';
		$streamTableAlias .= 'select a6.* from ' . $db->nameQuote('#__social_stream') . ' as a6 inner join ' . $db->nameQuote('#__social_stream_tags') . ' as st on a6.' . $db->nameQuote('id') . ' = st.' . $db->nameQuote('stream_id') . ' where st.' . $db->nameQuote('uid') . ' = ' . $db->Quote($userId) . ' and st.' . $db->nameQuote('utype') . ' = ' . $db->Quote(SOCIAL_STREAM_TAGGING_TYPE_USER);

		$streamTableAlias .= ') as a';

		// echo str_replace('#_', 'jos', $streamTableAlias);exit;

		return $streamTableAlias;
	}

	/**
	 * Retrieves a list of stream items from cluster types
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getClusterStreamData($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$config = ES::config();

		// Get the view
		$view = $this->input->get('view', '', 'string');

		// Enforce a hard limit
		$hardLimit = SOCIAL_STREAM_HARD_LIMIT;

		// Get the sorting behavior
		$sortDate = $this->streamPaginationSort;

		// Determines if the user wants to filter items by cluster
		$clusterId = isset($options['clusterId']) ? $options['clusterId'] : false;
		$clusterType = isset($options['clusterType']) ? $options['clusterType'] : false;
		$clusterCategory = isset($options['clusterCategory']) ? $options['clusterCategory'] : false;

		$context = isset($options['context']) ? $options['context'] : false;
		$userid = isset($options['userid']) ? $options['userid'] : false;
		$aspect = isset($options['aspect']) ? $options['aspect'] : false;

		$uid = isset($options['uid']) ? $options['uid'] : false;
		$type = isset($options['type']) ? $options['type'] : SOCIAL_TYPE_USER;
		$viewer = isset($options['viewer']) ? $options['viewer'] : false;

		if ($viewer !== false) {
			$viewer = (int) $viewer;
		}

		$limitstart = isset($options['limitstart']) ? $options['limitstart'] : false;
		$limitend = isset($options[ 'limitend' ]) ? $options['limitend'] : false;

		$streamId = isset($options['streamId']) ? $options['streamId'] : false;
		$direction = isset($options['direction']) ? $options['direction'] : 'older';

		$isSticky = isset($options['issticky']) ? $options['issticky'] : false;
		$noSticky = isset($options['nosticky']) ? $options['nosticky'] : false;

		$ignoreUser = isset($options['ignoreUser']) ? $options['ignoreUser'] : false ;
		$tag = isset($options['tag']) ? $options['tag'] : false;
		$matchAllTags = isset($options['matchAllTags']) ? $options['matchAllTags'] : false;

		$guest = isset($options['guest']) ? $options['guest'] : false ;
		$limit = isset($options['limit']) ? $options['limit'] : false ;
		$startlimit = isset($options['startlimit']) ? $options['startlimit'] : '0' ;
		$startdatelimit = isset($options['startdatelimit']) ? $options['startdatelimit'] : '';

		$perspective = isset($options['perspective']) ? $options['perspective'] : null;

		// Determine if we would only like to get streams from specific apps only
		$includeApps = isset($options['includeApps']) ? $options['includeApps'] : false;

		// Scheduled posting.
		$isScheduled = ES::normalize($options, 'isScheduled', false);

		// If tag is provided, we need to ensure that it's an array
		$tag = ES::makeArray($tag);

		// Ensure that the cluster type is an array
		$clusterType = ES::makeArray($clusterType);

		// Ensure that the cluster id is an array
		$clusterId = ES::makeArray($clusterId);

		// Ensure that the cluster category is an array
		$clusterCategory = ES::makeArray($clusterCategory);


		$excludeSiteAdmin = $config->get('stream.exclude.admin') ? true : false;
		$excludeSiteAdminCluster = false;

		// lets check if there are different type of cluster or not. if yes, we will use the global 'stream.exclude.admin' instead.
		$tmpType = array_unique($clusterType);

		// if there is only one cluster type, lets request the xxxx.feed.includeadmin.
		$includeMapping = array(
							'group' => 'groups.feed.includeadmin',
							'page' => 'pages.feed.includeadmin',
							'event' => 'events.feed.includeadmin'
						);

		if ($tmpType) {

			if (count($tmpType) == 1) {
				$includeConfigName = isset($includeMapping[$tmpType[0]]) ? $includeMapping[$tmpType[0]] : '';

				if ($includeConfigName) {
					$excludeSiteAdmin = $config->get($includeConfigName) ? false : true;
				}
			} else if (count($tmpType) > 1) {
				$excludeSiteAdmin = false;

				foreach ($tmpType as $cType) {
					if (!$config->get($includeMapping[$cType])) {
						$excludeSiteAdminCluster = true;
						break;
					}
				}
			}
		}

		$viewerId = null;

		if ($viewer) {
			$viewerId = $viewer;
		}

		$my = ES::user($viewerId);
		$isAdmin = $my->isSiteAdmin();

		$query = array();
		$table = array();
		$cond = array();
		$order = array();

		$distinctRow = $tag ? ' DISTINCT' : '';

		$query[] = 'SELECT ' . $distinctRow. ' a.*';
		$query[] = ', l.id as loc_id, l.uid as loc_uid, l.type as loc_type, l.user_id as loc_user_id, l.created as loc_created, l.short_address as loc_short_address';
		$query[] = ',l.address as loc_address, l.longitude as loc_longitude, l.latitude as loc_latitude, l.params as loc_params';
		$query[] = ',md.id as md_id, md.namespace as md_namespace,md.namespace_uid as md_namespace_uid, md.icon as md_icon, md.verb as md_verb, md.subject as md_subject, md.custom as md_custom';
		$query[] = ',md.text as md_text, md.user_id as md_user_id, md.created as md_created, a.sticky_id as sticky';

		if ($isScheduled) {
			$query[] = ', sch.`scheduled`';
		}

		if ($viewer) {
			$query[] = ',sbm.id as bookmarked';
		} else {
			$query[] = ',0 as bookmarked';
		}


		$query[] = ',FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.`modified`)) / 60) AS `min`';
		$query[] = ',FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.`modified`)) / 60 / 60) AS `hour`';
		$query[] = ',FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.`modified`)) / 60 / 60 / 24) AS `day`';


		$table[] = ' FROM `#__social_stream` AS a INNER JOIN `#__social_clusters` AS sc ON a.`cluster_id` = sc.`id`';

		// joining events meta table
		$table[] = 'LEFT JOIN ' . $db->nameQuote('#__social_events_meta') . ' AS em ON sc.' . $db->nameQuote('id') . ' = em.' . $db->nameQuote('cluster_id');

		// joining location table
		$table[] = 'LEFT JOIN ' . $db->nameQuote('#__social_locations') . ' AS l ON a.' . $db->nameQuote('location_id') . ' = l.' . $db->nameQuote('id');

		// joining mood table
		$table[] = 'LEFT JOIN ' . $db->nameQuote('#__social_moods') . ' AS md ON a.' . $db->nameQuote('mood_id') . ' = md.' . $db->nameQuote('id');

		// joining bookmark table
		if ($viewer) {
			$table[] = 'LEFT JOIN ' . $db->nameQuote('#__social_bookmarks') . ' AS sbm FORCE INDEX (`idx_user_utype`)';
			$table[] = 'ON a.' . $db->nameQuote('id') . ' = sbm.' . $db->nameQuote('uid') . ' and sbm.' . $db->nameQuote('type') . ' = ' . $db->Quote('stream');
			$table[] = 'and sbm.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($viewer);
		}

		if ($isSticky) {
			$table[] = 'INNER JOIN ' . $db->nameQuote('#__social_stream_sticky') . ' AS ssk';
			$table[] = 'ON a.' . $db->nameQuote('sticky_id') . ' = ssk.' . $db->nameQuote('id');
		}

		if (!$ignoreUser) {
			$table[] = 'INNER JOIN `#__users` AS uu ON a.`actor_id` = uu.`id` AND uu.`block` = 0' ;
		}

		if ($tag) {
			$table[] = 'INNER JOIN `#__social_stream_tags` AS tags';
			$table[] = 'ON a.`id` = tags.`stream_id`';
		}

		if ($isScheduled) {
			$table[] = 'LEFT JOIN ' . $db->nameQuote('#__social_stream_scheduled') . ' AS `sch`';
			$table[] = 'ON a.' . $db->nameQuote('id') . ' = sch.' . $db->nameQuote('stream_id');
		}

		$isSingleItem = false;

		if (isset($options['moderated']) && $options['moderated'] && !$isScheduled) {
			$cond[] = 'WHERE (a.`state` = 1 OR a.`state` = 5)';
		} else if ($isScheduled) {
			$cond[] = 'WHERE a.`state` = ' . $db->Quote(SOCIAL_STREAM_STATE_SCHEDULED);

			if ($userid) {
				$cond[] = 'AND sch.`actor_id` = ' . $db->Quote($userid[0]);
			}
		} else {

			if (isset($options['onlyModerated']) && $options['onlyModerated']) {
				$cond[] = 'WHERE a.`state` = ' . $db->Quote(SOCIAL_STREAM_STATE_MODERATE);
			} else {

				// Filter by published and restored from archive. #907
				$cond[] = 'WHERE a.`state` IN(' . $db->Quote(SOCIAL_STREAM_STATE_PUBLISHED) . ',' . $db->Quote(SOCIAL_STREAM_STATE_RESTORED) . ')';
			}
		}

		if ($streamId) {
			$cond[] = 'AND a.`id` = ' . $db->Quote($streamId);
			$isSingleItem = true;
		} else {

			// Support for multiple cluster type
			// Also support for clusterType = false
			if ($clusterType) {
				if (count($clusterType) > 1) {
					$cond[] = 'AND a.`cluster_type` IN (' . implode(',', $db->quote($clusterType)) . ')';
				} else {
					$cond[] = 'AND a.`cluster_type` = ' . $db->quote($clusterType[0]);
				}
			}

			if ($clusterId) {
				if (count($clusterId) > 1) {
					$cond[] = 'AND a.`cluster_id` IN (' . implode(',', $db->quote($clusterId)) . ')';
				} else {
					$cond[] = 'AND a.`cluster_id` = ' . $clusterId[0] ;
				}
			}

			if ($clusterCategory) {
				if (count($clusterCategory) > 1) {
					$cond[] = 'AND sc.`category_id` IN (' . implode(',', $db->quote($clusterCategory)) . ')';
				} else {
					$cond[] = 'AND sc.`category_id` = ' . $clusterCategory[0];
				}
			}

			$cond[]	= 'AND a.`actor_type` = ' . $db->Quote($type);

			if ($context !== 'all') {

				// context used to filter the apps.
				if (is_array($context)) {

					if (count($context) == 1) {
						$cond[]	= 'AND a.`context_type` = ' . $db->Quote($context[0]);
					} else {

						$tmpString = '';

						foreach ($context as $citem) {
							$tmpString .= ($tmpString) ? ',' . $db->Quote($citem) : $db->Quote($citem);
						}

						$cond[]	= 'AND a.`context_type` IN (' . $tmpString . ')';
					}
				} else {
					$cond[]	= 'AND a.`context_type` =' . $db->Quote($context);
				}
			}
		}

		if ($includeApps) {
			$this->generateAccessibleAppsSql($includeApps, $cond);
		}

		// exclude these context items
		$excludeApps = array();

		if ($clusterType) {
			foreach ($clusterType as $c) {
				$excludeApps[$c] = $this->getUnAccessilbleUserApps($viewer, $c, $perspective);
			}
		}

		$this->generateUnAccessibleAppsSQL($excludeApps, $cond);

		if ($viewer) {

			// If user is site admin, do not block them
			if (!ES::user($viewer)->isSiteAdmin()) {
				// Cluster privacy
				$cond[]	= 'AND (';

				// If filter from dashboard, we need to check for cluster nodes even if the cluster is public
				if ($aspect && $aspect == 'dashboard') {
					$cond[]	= ' ' . $db->Quote($viewer) . ' IN (select scn.`uid` from `#__social_clusters_nodes` as scn where (scn.`cluster_id` = a.`cluster_id` OR scn.`cluster_id` = em.`group_id` OR scn.`cluster_id` = em.`page_id`)';
					$cond[] = ' and `type` = ' . $db->Quote(SOCIAL_TYPE_USER);
				} else {
					// if the cluster is private or invite only
					$cond[]	= ' (sc.`type`  IN(' . $db->Quote(SOCIAL_GROUPS_PUBLIC_TYPE) . ', ' . $db->Quote(SOCIAL_GROUPS_SEMI_PUBLIC_TYPE) . ')) OR (sc.`type` > 1) AND ' . $db->Quote($viewer) . ' IN (select scn.`uid` from `#__social_clusters_nodes` as scn where (scn.`cluster_id` = a.`cluster_id` OR scn.`cluster_id` = em.`group_id` OR scn.`cluster_id` = em.`page_id`)';
					$cond[] = ' and `type` = ' . $db->Quote(SOCIAL_TYPE_USER);
				}

				// If the clusterNode state is 1 or (stream clustertype is event and clusterNode is 'maybe'/'not attending')
				// This is for private/invite-only event, user that chose maybe/notattending, will also see the stream item.
				$cond[] = ' and (`state` = 1 OR (a.`cluster_type` = ' . $db->Quote(SOCIAL_TYPE_EVENT) . ' and `state` > 2)))';

				$cond[] = ')';
			}

			// based on stream item.
			$cond[]	= 'AND NOT EXISTS (';
			$cond[]	= 'SELECT h.' . $db->nameQuote('uid') . ' FROM ' . $db->nameQuote('#__social_stream_hide') . ' AS h';
			$cond[]	= 'WHERE a.' . $db->nameQuote('id') . '= h.' . $db->nameQuote('uid');
			$cond[]	= 'AND h.' . $db->nameQuote('user_id') . '=' . $db->Quote($viewer);
			$cond[]	= 'AND h.' . $db->nameQuote('type') . '=' . $db->Quote('stream');
			$cond[]	= ')';

			//based on context
			$cond[]	= 'AND NOT EXISTS (';
			$cond[] = 'SELECT h1.' . $db->nameQuote('context') . ' FROM ' . $db->nameQuote('#__social_stream_hide') . ' AS h1';
			$cond[] = 'WHERE a.' . $db->nameQuote('context_type') . ' = h1.' . $db->nameQuote('context');
			$cond[] = 'AND h1.' . $db->nameQuote('user_id') . '=' . $db->Quote($viewer) ;
			$cond[] = 'AND h1.' . $db->nameQuote('uid') . ' = ' . $db->Quote('0');
			$cond[] = 'AND h1.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote('0');
			$cond[] = ')';

			// based on user who blocked the viewer
			// Should not check for block if the viewer is admin #3888
			if ($config->get('users.blocking.enabled') && !ES::user($viewer)->isSiteAdmin()) {
				$cond[]	= 'AND NOT EXISTS (';
				$cond[]	= 'select bs.' . $db->nameQuote('user_id') . ' from ' . $db->nameQuote('#__social_block_users') . ' as bs';
				$cond[] = 'where (a.' . $db->nameQuote('actor_id') . ' = bs.' . $db->nameQuote('user_id');
				$cond[] = 'and bs.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($viewer);

				// exclude cluster's admin user #4448
				$cond[] = 'and bs.' . $db->nameQuote('target_id') . ' NOT IN (';
				$cond[] = '    select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where a.`cluster_id` = clsnd.`cluster_id` and clsnd.`admin` = 1)';

				$cond[] = ') OR (';
				$cond[] = 'a.' . $db->nameQuote('actor_id') . ' = bs.' . $db->nameQuote('target_id');
				$cond[] = 'and bs.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($viewer) . ')';
				$cond[] = ')';
			}

		} else {
			// group privacy
			$cond[]	= 'AND sc.`type` IN(' . $db->Quote(SOCIAL_GROUPS_PUBLIC_TYPE) . ', ' . $db->Quote(SOCIAL_GROUPS_SEMI_PUBLIC_TYPE) . ')';
		}

		// Exclude site admin
		if (($excludeSiteAdmin || $excludeSiteAdminCluster) && !$isAdmin) {

			// Get a list of site administrators from the site.
			$userModel = ES::model('Users');
			$admins = $userModel->getSiteAdmins(true);

			if ($admins) {
				$ids = array();

				foreach ($admins as $adminId) {
					if ($my->id == $adminId) {
						continue;
					}

					$ids[] = $adminId;
				}

				if ($ids) {

					if ($excludeSiteAdmin) {
						$cond[] = ' AND a.' . $db->nameQuote('actor_id') . ' NOT IN (' . implode(',', $ids) . ')';
					} else if ($excludeSiteAdminCluster) {
						$cond[] = ' AND a.' . $db->nameQuote('id') . ' NOT IN (';
						$cond[] = ' select stream.' . $db->nameQuote('id') . ' from #__social_stream as stream where';
						$cond[] = ' (';

						$clusterCond = array();
						$i = 0;

						foreach ($tmpType as $cType) {

							// Skip exclusion
							if ($config->get($includeMapping[$cType])) {
								continue;
							}

							if ($i > 0) {
								$clusterCond[] = ' OR ';
							}

							$clusterCond[] = ' (';
							$clusterCond[] = ' stream.' . $db->nameQuote('actor_id') . ' IN (' . implode(',', $ids) . ')';
							$clusterCond[] = ' AND stream.' . $db->nameQuote('cluster_type') . ' = ' . $db->Quote($cType);
							$clusterCond[] = ' )';

							$i++;
						}

						$cond[] = implode(' ', $clusterCond);
						$cond[] = ' )';
						$cond[] = ' )';
					}
				}
			}
		}

		if ($tag && !$matchAllTags) {
			if (count($tag) == 1) {
				$cond[]	= 'AND tags.' . $db->nameQuote('title') . '=' . $db->Quote($tag[0]);
			} else {
				$totalTags = count($tag);
				$tagQuery = '';

				for ($t = 0; $t < $totalTags; $t++) {
					$tagQuery .= ($t < $totalTags - 1) ? ' (tags.title = ' . $db->Quote($tag[ $t ]) . ') OR ' : ' (tags.title = ' . $db->Quote($tag[ $t ]) . ')';
				}

				$cond[]	= 'AND (' . $tagQuery . ')';
			}
		}

		if ($tag && $matchAllTags) {
			$totalTags = count($tag);
			$tagQuery = 'SELECT `stream_id` FROM ' . $db->nameQuote('#__social_stream_tags');
			$tagQuery .= 'WHERE ' . $db->nameQuote('title') . 'IN(';

			for ($i = 0; $i < $totalTags; $i++) {
				$tagQuery .= $db->Quote($tag[$i]);

				if ($i < $totalTags -1 ) {
					$tagQuery .= ',';
				}
			}

			$tagQuery .= ')';
			$tagQuery .= 'GROUP BY `stream_id` HAVING count(`stream_id`) = ' . $db->Quote($totalTags);

			$cond[] = 'AND tags.stream_id IN(' . $tagQuery . ')';
		}

		if ($noSticky) {
			// exclude sticky posts.
			$cond[] = ' AND a.`sticky_id` = 0';
		}

		// lets get the limit dates here instead
		$limitDates = array();

		// startdate holding the larger date
		// enddte holding the smaller date.
		if (!$isSingleItem && !$limit) {
			if ($direction == 'later') {
				$cond[]	= 'AND a.' . $db->nameQuote($sortDate) . ' >= ' . $db->Quote($limitstart);
			}
		}


		$isUsingStartDateLimit = false;
		// is this to avoid duplicate stream being loaded in pagination when there are new steams
		if (!$isSingleItem && $limit && $startdatelimit) {
			$isUsingStartDateLimit = true;
			$cond[]	= 'AND a.' . $db->nameQuote($sortDate) . ' < ' . $db->Quote($startdatelimit);
		}

		// Ensure that is sticky stream data, sort sticky id in order to show the recent pinned stream item
		if ($isSticky && !$noSticky) {
			$sortDate = 'sticky_id';
		}

		// ordering. DO NOT change the ordering.
		$order[] = 'ORDER BY a.' . $db->nameQuote($sortDate) . ' DESC';

		// concate all the queries segments.
		$query = implode(' ', $query);
		$table = implode(' ', $table);

		$cond = implode(' ', $cond);
		$order = implode(' ', $order);

		$totalSQL = 'select count(1) from (' . $query . ' ' . $table . ' ' . $cond . ') as x';

		$query = $query . ' ' . $table . ' ' . $cond . ' ' . $order;

		if ($limit) {
			if ($isUsingStartDateLimit) {
				$query .= ' LIMIT ' . ($limit + 1);
			} else {
				$query .= ' LIMIT ' . $startlimit . ',' . ($limit + 1);
			}
		}

		// echo str_replace('#_', 'jos', $query);
		// echo '<br><br>';

		$sql->raw($query);

		$db->setQuery($sql);

		$result = $db->loadObjectList();
		$counts = count($result);


		// now we need to remove the last index from the result.
		if (!$isSingleItem && $limit && $counts > $limit) {
			array_pop($result);
		}

		if ($counts > $limit) {
			$total = 1;
		} else {
			$total = 0;
		}

		$this->total = $total;

		$lastItemDate = '';
		$total = count($result);

		if (!$total) {
			return $result;
		}

		$streamIds = array();
		$streamContexts = array();

		foreach ($result as $row) {
			$streamIds[] = $row->id;
			$streamContexts[] = $row->context_type;

			$lastItemDate = $row->modified;
			$this->nextdatelimit = $sortDate == 'modified' ? $row->modified : $row->created;
		}

		// -------------------------------------------------------------
		// This is the starting points of optimizing queries for stream.
		// -------------------------------------------------------------

		$this->setBatchRelatedItems($streamIds, $streamContexts, $viewer);

		// set stream actors.
		$this->setActorsBatch($result);

		// set stream photos.
		$this->setMediaBatch($result);

		// set stream tagging
		$this->setTaggingBatch($streamIds);

		//set stream likes
		$like = ES::model('Likes');
		$like->setStreamLikesBatch($result);

		// Cache stream repost
		$repost = ES::model('Repost');
		$repost->setStreamRepostBatch($result);

		$share = ES::table('Share');
		$share->setSharesBatch($result);

		// comment count
		$commentModel = ES::model('Comments');
		$commentModel->setStreamCommentCountBatch($result);

		return $result;
	}

	/**
	 * Get total stream item made by a user
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getTotalStreamBy($userId = 0)
	{
		$db = ES::db();
		$user = ES::user($userId);

		$query = 'SELECT COUNT(*) AS total';
		$query .= ' FROM ' . $db->nameQuote('#__social_stream');
		$query .= ' WHERE ' . $db->nameQuote('actor_id') . '=' . $user->id;
		$query .= ' AND ' . $db->nameQuote('context_type') . '=' . $db->quote('story');
		$query .= ' AND ' . $db->nameQuote('verb') . '=' . $db->quote('create');
		$query .= ' AND ' . $db->nameQuote('state') . '= 1';

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result->total;
	}

	/**
	 * Retrieves the stream data.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getStreamData($config = array(), $debug = false)
	{
		$db = ES::db();

		$sysconfig = ES::config();
		$streamLib = ES::stream();

		$sortDate = $this->streamPaginationSort;

		// If a context is given.
		$actorid = isset($config['actorid']) ? $config['actorid'] : false;
		$context = isset($config['context']) ? $config['context'] : false;
		$userid = isset($config['userid']) ? $config['userid'] : false;
		$listid = isset($config['list']) ? $config['list'] : false;
		$profileId = isset($config['profileId']) ? $config['profileId'] : false;
		$uid = isset($config['uid']) ? $config['uid'] : false;

		$type = isset($config['type']) ? $config['type'] : SOCIAL_TYPE_USER;
		$viewer = isset($config['viewer']) ? $config['viewer'] : false;

		if ($viewer !== false) {
			$viewer = (int) $viewer;
		}

		$limitstart = isset($config['limitstart']) ? $config['limitstart'] : false;
		$limitend = isset($config['limitend']) ? $config['limitend'] : false;
		$isFollow = isset($config['isfollow']) ? $config['isfollow'] : false;
		$isBookmark = isset($config['isbookmark']) ? $config['isbookmark'] : false;

		$isSticky = isset($config['issticky']) ? $config['issticky'] : false;
		$userStickyOnly = isset($config['userstickyonly']) ? $config['userstickyonly'] : false; // this option is true when we want to get user's related sticky only.
		$isAdminSticky = isset($config['isadminsticky']) ? $config['isadminsticky'] : false; // this option is true when we only get admin sticky only
		$includeClusterSticky = isset($config['includeclustersticky']) ? $config['includeclustersticky'] : false; // this optin used together with 'userStickyOnly' flag
		$noSticky = isset($config['nosticky']) ? $config['nosticky'] : false;
		$isStickyFilter = isset($config['isStickyFilter']) ? $config['isStickyFilter'] : false;

		$streamId = isset($config['streamId']) ? $config['streamId'] : false;
		$direction = isset($config['direction']) ? $config['direction'] : 'older';
		$ignoreUser = isset($config['ignoreUser']) ? $config['ignoreUser'] : false;

		// Exclude Site Admin
		$excludeSiteAdmin = $sysconfig->get('stream.exclude.admin') ? true : false;

		// exclude streams
		$excludeStreamIds = isset($config['excludeStreamIds']) ? $config['excludeStreamIds'] : null;

		// pagination for public stream
		$limit = isset($config['limit']) ? $config['limit'] : false;
		$startlimit = isset($config['startlimit']) ? $config['startlimit'] : '0';
		$startdatelimit = isset($config['startdatelimit']) ? $config['startdatelimit'] : '';

		$guest = isset($config['guest']) ? $config['guest'] : false;
		$tag = isset($config['tag']) ? $config['tag'] : false;
		$matchAllTags = isset($config['matchAllTags']) ? $config['matchAllTags'] : false;

		$customView = isset($config['customView']) ? $config['customView'] : false;

		$anywhereId = isset($config['anywhereId']) ? $config['anywhereId'] : false;

		// Determine if we would only like to get streams from specific apps only
		$includeApps = isset($config['includeApps']) ? $config['includeApps'] : false;

		// Scheduled posting.
		$isScheduled = ES::normalize($config, 'isScheduled', false);

		if ($tag && !is_array($tag)) {
			$tag = array($tag);
		}

		$query = array();
		$table = array();
		$cond = array();
		$order = array();

		//for total count
		$cntTable = array();
		$cntCond = array();

		// $view = $this->input->get('view', '', 'string');

		$customView = ($customView) ? $customView : JFactory::getApplication()->input->get('view', '', 'default');
		$viewerId = null;

		if ($viewer) {
			$viewerId = $viewer;
		}

		$my = ES::user($viewerId);

		$isAdmin = $my->isSiteAdmin();
		$respectPrivacy = !$my->isSiteAdmin() && $sysconfig->get('privacy.enabled') ? true : false;

		if (empty($listid)
			&& empty($isBookmark)
			&& empty($isFollow)
			&& empty($profileId)
			&& !$streamId
			&& !$guest
			&& !$tag
			&& $isAdmin) {

			//if this is true, most likely the user is an admin and admin is clicking on 'me/my friend' filter.
			// if that is the case, then we need to get only admin and friends stream. so we canot set isAdmin to true.

			$isAdmin = false;
		}

		$streamTableAlias = $db->nameQuote('#__social_stream') . ' AS a';

		if (empty($listid) && empty($isBookmark) && empty($isFollow) && empty($profileId) && !$streamId && !$guest && !$tag && !$isAdmin && !$isAdminSticky) {

			$options = array(
				'userStickyOnly' => $userStickyOnly,
				'useDate' => false,
				'direction' => '',
				'startdate' => null,
				'enddate' => null,
				'customView' => $customView,
				'context' => $context,
				'anywhereId' => $anywhereId,
				'isScheduled' => $isScheduled
			);

			$streamTableAlias = $this->getStreamTableAlias($userid[0], $type, $options);
		}

		// since we are joining social_items table, we will need to distinct the results.
		$distinctRow = ($tag) ? ' distinct ' : '';

		$select = [
			'a.*',
			'l.`id` as `loc_id`',
			'l.`uid` as `loc_uid`',
			'l.`type` as `loc_type`',
			'l.`user_id` as `loc_user_id`',
			'l.`created` as `loc_created`',
			'l.`short_address` as `loc_short_address`',
			'l.`address` as `loc_address`',
			'l.`longitude` as `loc_longitude`',
			'l.`latitude` as `loc_latitude`',
			'l.`params` as `loc_params`',
			'md.`id` as `md_id`',
			'md.`namespace` as `md_namespace`',
			'md.`namespace_uid` as `md_namespace_uid`',
			'md.`icon` as `md_icon`',
			'md.`verb` as `md_verb`',
			'md.`subject` as `md_subject`',
			'md.`custom` as `md_custom`',
			'md.`text` as `md_text`',
			'md.`user_id` as `md_user_id`',
			'md.`created` as `md_created`',
			'a.`sticky_id` as `sticky`',
			'a.`background_id`'
		];

		if ($isScheduled) {
			$select[] = 'sch.`scheduled`';
		}

		if ($viewer) {
			$select[] = 'sbm.`id` as bookmarked';
		} else {
			$select[] = '0 as bookmarked';
		}

		$select[] = 'FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.`modified`)) / 60) AS `min`';
		$select[] = 'FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.`modified`)) / 60 / 60) AS `hour`';
		$select[] = 'FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.`modified`)) / 60 / 60 / 24) AS `day`';

		// Implode select statement
		$query[] = 'SELECT ' . $distinctRow . implode(', ', $select);

		$table[] = ' FROM ' . $streamTableAlias;

		$table[] = 'LEFT JOIN ' . $db->nameQuote('#__social_events_meta') . ' AS em ON a.' . $db->nameQuote('cluster_id') . ' = em.' . $db->nameQuote('cluster_id');

		// joining location table - do not change the position of this code
		$table[] = 'LEFT JOIN ' . $db->nameQuote('#__social_locations') . ' AS l ON a.' . $db->nameQuote('location_id') . ' = l.' . $db->nameQuote('id');

		// joining mood table - do not change the position of this code
		$table[] = 'LEFT JOIN ' . $db->nameQuote('#__social_moods') . ' AS md ON a.' . $db->nameQuote('mood_id') . ' = md.' . $db->nameQuote('id');

		// joining users table - do not change the position of this code
		if (!$ignoreUser) {
			$table[] = 'INNER JOIN ' . $db->nameQuote('#__users') . ' AS uu ON a.' . $db->nameQuote('actor_id') . ' = uu.' . $db->nameQuote('id') . ' AND uu.' . $db->nameQuote('block') . ' = 0' ;
		}

		if ($isFollow) {
			$table[] = 'INNER JOIN ' . $db->nameQuote('#__social_subscriptions') . ' AS s';
			$table[] = 'ON a.' . $db->nameQuote('actor_id') . ' = s.' . $db->nameQuote('uid');
		}

		if ($profileId) {
			$table[] = 'INNER JOIN ' . $db->nameQuote('#__social_profiles_maps') . ' AS spm';
			$table[] = 'ON a.' . $db->nameQuote('actor_id') . ' = spm.' . $db->nameQuote('user_id') . ' and spm.`profile_id` = ' . $db->Quote($profileId) ;
		}


		if ($viewer) {
			if ($isBookmark) {
				$table[] = 'INNER JOIN ' . $db->nameQuote('#__social_bookmarks') . ' AS sbm FORCE INDEX (`idx_user_utype`)';
				$table[] = 'ON a.' . $db->nameQuote('id') . ' = sbm.' . $db->nameQuote('uid') . ' and sbm.' . $db->nameQuote('type') . ' = ' . $db->Quote('stream') . ' and sbm.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($viewer);
			} else {
				$table[] = 'LEFT JOIN ' . $db->nameQuote('#__social_bookmarks') . ' AS sbm FORCE INDEX (`idx_user_utype`)';
				$table[] = 'ON a.' . $db->nameQuote('id') . ' = sbm.' . $db->nameQuote('uid') . ' and sbm.' . $db->nameQuote('type') . ' = ' . $db->Quote('stream') . ' and sbm.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($viewer);
			}
		}

		if ($isSticky) {
			$table[] = 'INNER JOIN ' . $db->nameQuote('#__social_stream_sticky') . ' AS ssk';
			$table[] = 'ON a.' . $db->nameQuote('sticky_id') . ' = ssk.' . $db->nameQuote('id');
		}

		if (!empty($listid)) {
			$table[] = 'INNER JOIN ' . $db->nameQuote('#__social_lists_maps') . ' AS lm';
			$table[] = 'ON a.' . $db->nameQuote('actor_id') . ' = lm.' . $db->nameQuote('target_id') . ' AND lm.' . $db->nameQuote('list_id') . ' = ' . $db->Quote($listid) . ' and lm.' . $db->nameQuote('target_type') . ' = ' .$db->Quote('user');

		}

		if ($tag) {
			$table[] = 'INNER JOIN ' . $db->nameQuote('#__social_stream_tags') . ' AS tags';
			$table[] = 'ON a.' . $db->nameQuote('id') . ' = tags.' . $db->nameQuote('stream_id');
		}

		if ($isScheduled) {
			$table[] = 'LEFT JOIN ' . $db->nameQuote('#__social_stream_scheduled') . ' AS `sch`';
			$table[] = 'ON a.' . $db->nameQuote('id') . ' = sch.' . $db->nameQuote('stream_id');
		}

		$isSingleItem = false;

		// we do not want any cluster stream here.

		if ($isScheduled) {
			$cond[] = 'WHERE a.`state` = ' . $db->Quote(SOCIAL_STREAM_STATE_SCHEDULED);

			// We'll only loads the scheduled stream from this user only.
			if ($userid) {
				$cond[] = 'AND sch.`actor_id` = ' . $db->Quote($userid[0]);
			}
		} else {
			// * cntCond should not inlcude this cluster criteria.
			// Filter by published and restored from archive. #907
			$cond[] = 'WHERE a.`state` IN(' . $db->Quote(SOCIAL_STREAM_STATE_PUBLISHED) . ',' . $db->Quote(SOCIAL_STREAM_STATE_RESTORED) . ')';
		}

		// exclude streams
		if ($excludeStreamIds) {
			if (is_array($excludeStreamIds) && count($excludeStreamIds) > 1) {
				$cond[] = 'AND a.`id` NOT IN (' . implode(',', $excludeStreamIds) . ')';
			} else if (is_array($excludeStreamIds) && count($excludeStreamIds) == 1) {
				$cond[] = 'AND a.`id` != ' . $db->Quote($excludeStreamIds[0]);
			} else if (! is_array($excludeStreamIds)) {
				$cond[] = 'AND a.`id` != ' . $db->Quote($excludeStreamIds);
			}
		}

		if ($actorid) {
			if (is_array($actorid)) {
				$cond[] = 'AND a.`actor_id` IN (' . implode(',', $actorid) . ')';
			} else {
				$cond[] = 'AND a.`actor_id` = ' . $actorid;
			}
		}

		if ($anywhereId) {
			$cond[] = 'AND a.`anywhere_id` = ' . $db->Quote($anywhereId);
		}

		if ($isSticky && $userStickyOnly && !$includeClusterSticky) {
			// we only want pinned items from users and not clusters.
			$cond[] = 'AND a.`cluster_id`= 0';
		} else {

			$cond[] = 'AND (';
			$cond[]	= '(a.`cluster_id`= 0) OR';


			if ($viewer && (!$sysconfig->get('stream.clusters.private', true) && !$sysconfig->get('stream.groups.public', true))) {

				$cond[]	= '(a.`cluster_id` > 0 and a.`cluster_access` IN (1, 4)';
				if (!$isAdmin) {
					$cond[] = 'AND ' . $viewer  . ' IN(';
					$cond[] = 'select scn.`uid` FROM `#__social_clusters_nodes` AS `scn` WHERE scn.`cluster_id` = a.`cluster_id`';
					$cond[] = 'AND scn.`type`=' . $db->Quote(SOCIAL_TYPE_USER);
					$cond[] = 'AND scn.`state`= 1';
					$cond[] = ')';
				}
				$cond[]	= ')';

			} else {

				if (($viewer && $streamId) || $sysconfig->get('stream.groups.public', true)) {
					$cond[]	= '(a.`cluster_id` > 0 and a.`cluster_access` IN (1, 4))';
				} else {
					$cond[]	= '(a.`cluster_id` > 0 and a.`cluster_access` IN (1, 4) and a.`cluster_type` IN (' . $db->Quote(SOCIAL_TYPE_EVENT)  . ',' . $db->Quote(SOCIAL_TYPE_PAGE) . '))';
				}

				if ($viewer && ($streamId || $sysconfig->get('stream.clusters.private', true) || !$sysconfig->get('stream.groups.public', true))) {
					$cond[] = 'OR (';

					$clusterOperator = '>';

					if (!$sysconfig->get('stream.groups.public', true)) {
						$clusterOperator = '>=';
					}

					$cond[] = 'a.`cluster_id` > 0 AND a.`cluster_access` ' . $clusterOperator . ' 1';
					if (!$isAdmin) {
						$cond[] = 'AND ' . $viewer  . ' IN(';
						$cond[] = 'select scn.`uid` FROM `#__social_clusters_nodes` AS `scn` WHERE (scn.`cluster_id` = a.`cluster_id` OR scn.`cluster_id` = em.`group_id`)';
						$cond[] = 'AND scn.`type`=' . $db->Quote(SOCIAL_TYPE_USER);
						$cond[] = 'AND scn.`state`= 1';
						$cond[] = ')';
					}

					$cond[] = ')';
				}

			}

			$cond[]	= ')';
		}


		if ($streamId) {
			$cond[] = 'AND a.' . $db->nameQuote('id') . '=' . $db->Quote($streamId);

			if ($viewer && $sysconfig->get('users.blocking.enabled') && !$my->isSiteAdmin()) {
				//based on user who blocked the viewer
				$cond[]	= 'AND NOT EXISTS (';
				$cond[]	= 'select bs.' . $db->nameQuote('user_id') . ' from ' . $db->nameQuote('#__social_block_users') . ' as bs';
				$cond[] = 'where (a.' . $db->nameQuote('actor_id') . ' = bs.' . $db->nameQuote('user_id');
				$cond[] = 'and bs.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($viewer);

				// exclude cluster's admin user #4448
				$cond[] = 'and bs.' . $db->nameQuote('target_id') . ' NOT IN (';
				$cond[] = '    select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where a.`cluster_id` = clsnd.`cluster_id` and clsnd.`admin` = 1)';

				$cond[] = ') OR (';
				$cond[] = 'a.' . $db->nameQuote('actor_id') . ' = bs.' . $db->nameQuote('target_id');
				$cond[] = 'and bs.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($viewer) . ')';
				$cond[] = ')';
			}

			$isSingleItem 	= true;
		} else {

			if ($uid) {
				// filtering based on a particular stream.
				$cond[]	= 'AND a.' . $db->nameQuote('id') . '=' . $db->Quote($uid);
				$isSingleItem = true;
			} else {
				$cond[]	= 'AND a.' . $db->nameQuote('actor_type') . '=' . $db->Quote($type);

				if ($context !== 'all') {

					// context used to filter the apps.
					if (is_array($context)) {
						if (count($context) == 1) {
							$cond[]	= 'AND a.' . $db->nameQuote('context_type') . '=' . $db->Quote($context[0]);
						} else {

							$tmpString = '';

							foreach ($context as $citem) {
								$tmpString .= ($tmpString) ? ',' . $db->Quote($citem) : $db->Quote($citem);
							}

							$cond[]	= 'AND a.' . $db->nameQuote('context_type') . 'IN (' . $tmpString . ')';
						}
					} else {
						$cond[]	= 'AND a.' . $db->nameQuote('context_type') . '=' . $db->Quote($context);
					}
				}
			}

			if ($includeApps) {
				$this->generateAccessibleAppsSql($includeApps, $cond);
			}

			// exclude these context items
			$excludeUserApps = $this->getUnAccessilbleUserApps($viewer, SOCIAL_APPS_GROUP_USER, $customView);
			$excludeGroupApps = $this->getUnAccessilbleUserApps($viewer, SOCIAL_APPS_GROUP_GROUP, $customView);
			$excludeEventApps = $this->getUnAccessilbleUserApps($viewer, SOCIAL_APPS_GROUP_EVENT, $customView);
			$excludePageApps = $this->getUnAccessilbleUserApps($viewer, SOCIAL_APPS_GROUP_PAGE, $customView);

			$excludeApps = array();

			if ($excludeUserApps) {
				$excludeApps[SOCIAL_APPS_GROUP_USER] = $excludeUserApps;
			}

			if ($excludeGroupApps) {
				$excludeApps[SOCIAL_APPS_GROUP_GROUP] = $excludeGroupApps;
			}

			if ($excludeEventApps) {
				$excludeApps[SOCIAL_APPS_GROUP_EVENT] = $excludeEventApps;
			}

			if ($excludePageApps) {
				$excludeApps[SOCIAL_APPS_GROUP_PAGE] = $excludePageApps;
			}

			$this->generateUnAccessibleAppsSQL($excludeApps, $cond);

			if ($isFollow) {
				$cond[]	= 'AND s.' . $db->nameQuote('type') . '=' . $db->Quote(SOCIAL_TYPE_USER . '.' . SOCIAL_SUBSCRIPTION_TYPE_USER);
				$cond[]	= 'AND s.' . $db->nameQuote('user_id') . '=' . $db->Quote($userid[ 0 ]);
			}

			if ($viewer) {

				if ($respectPrivacy) {

					$privacyCondsArr = [];

					// privacy here.
					// always start with left bracket to wrap the privacy conds'
					$privacyCondsArr[] = '(';

					//public
					$privacyCondsArr[] = '(a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR';

					//member
					$privacyCondsArr[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $viewer . ' > 0)) OR ';

					if ($sysconfig->get('friends.enabled')) {
						//friends of friends
						$privacyCondsArr[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND ((' . $streamLib->generateMutualFriendSQL($viewer, 'a.`actor_id`') . ') > 0)) OR ';

						//friends
						$privacyCondsArr[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND ((' . $streamLib->generateIsFriendSQL('a.`actor_id`', $viewer) . ') > 0)) OR ';

						//friends
						$privacyCondsArr[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $streamLib->generateIsFriendSQL('a.`actor_id`', $viewer) . ') > 0)) OR ';
					} else {
						// fall back to 'member'
						$privacyCondsArr[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIENDS_OF_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';
						$privacyCondsArr[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $viewer . ' > 0)) OR ';
					}


					//only me
					$privacyCondsArr[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (a.`actor_id` = ' . $viewer . ')) OR ';

					// custom
					$privacyCondsArr[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ') AND (a.`custom_access` LIKE ' . $db->Quote('%,' . $viewer . ',%') . '   )) OR ';

					if ($sysconfig->get('users.privacy.field')) {
						// field
						$fieldPrivacyQuery = '(select count(1) from `#__social_stream_privacy_field` as fa';
						$fieldPrivacyQuery .= ' inner join `#__social_fields` as ff on fa.`field_key` = ff.`unique_key`';
						$fieldPrivacyQuery .= ' inner join `#__social_fields_data` as fd on ff.`id` = fd.`field_id`';
						$fieldPrivacyQuery .= ' where fa.`stream_id` = a.`id`';
						$fieldPrivacyQuery .= ' and fd.`uid` = ' . $db->Quote($viewer);
						$fieldPrivacyQuery .= ' and fd.`type` = ' . $db->Quote('user');
						$fieldPrivacyQuery .= ' and fd.`raw` LIKE concat(' . $db->Quote('%') . ',fa.`field_value`, ' . $db->Quote('%') . '))';

						$privacyCondsArr[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (a.`field_access` <= ' . $fieldPrivacyQuery . ')) OR ';
					} else {
						$privacyCondsArr[] = '((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FIELD) . ') AND (' . $viewer . ' > 0)) OR ';
					}

					// my own items.
					$privacyCondsArr[] = '(a.`actor_id` = ' . $viewer . ')';

					// privacy checking end here.
					// always end with right bracket to end the wrap of privacy conds'
					$privacyCondsArr[] = ')';

					// trigger an event so that 3rd party plugin can manipulate the stream privacy conds in queries.
					ESDispatcher::trigger('onEasySocialStreamPrivacyConds', array($viewer, &$privacyCondsArr));

					if ($privacyCondsArr) {

						$privacyConds = implode(" ", $privacyCondsArr);
						$cond[] = 'AND ' . $privacyConds;
					}

				}

				// based on stream item.
				$cond[]	= 'AND NOT EXISTS (';
				$cond[]	= 'SELECT h.' . $db->nameQuote('uid') . ' FROM ' . $db->nameQuote('#__social_stream_hide') . ' AS h';
				$cond[]	= 'WHERE a.' . $db->nameQuote('id') . '= h.' . $db->nameQuote('uid');
				$cond[]	= 'AND h.' . $db->nameQuote('user_id') . '=' . $db->Quote($viewer);
				$cond[]	= 'AND h.' . $db->nameQuote('type') . '=' . $db->Quote('stream');
				$cond[]	= ')';

				//based on context
				$cond[]	= 'AND NOT EXISTS (';
				$cond[] = 'SELECT h1.' . $db->nameQuote('context') . ' FROM ' . $db->nameQuote('#__social_stream_hide') . ' AS h1';
				$cond[] = 'WHERE a.' . $db->nameQuote('context_type') . ' = h1.' . $db->nameQuote('context');
				$cond[] = 'AND h1.' . $db->nameQuote('user_id') . '=' . $db->Quote($viewer) ;
				$cond[] = 'AND h1.' . $db->nameQuote('uid') . ' = ' . $db->Quote('0');
				$cond[] = 'AND h1.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote('0');
				$cond[] = ')';

				//based on actor
				$cond[]	= 'AND NOT EXISTS (';
				$cond[] = 'SELECT h2.' . $db->nameQuote('actor_id') . ' FROM ' . $db->nameQuote('#__social_stream_hide') . ' AS h2';
				$cond[] = 'WHERE a.' . $db->nameQuote('actor_id') . '= h2.' . $db->nameQuote('actor_id') ;
				$cond[] = 'AND h2.' . $db->nameQuote('user_id') . '=' . $db->Quote($viewer) ;
				$cond[] = 'AND h2.' . $db->nameQuote('uid') . ' = ' . $db->Quote('0');
				$cond[] = 'AND h2.' . $db->nameQuote('context') . ' is null';
				$cond[] = ')';

				 // Should not check for block if the viewer is admin #3888
				// if ($sysconfig->get('users.blocking.enabled') && !$my->isSiteAdmin()) {
				if (ES::isBlockEnabled()) {

					$blockedUserIds = $this->getBlockedUserIds($viewer);

					if ($blockedUserIds) {
						$cond[] = 'AND a.`actor_id` NOT IN ( ' . implode(',', $blockedUserIds) . ')';
					}


					// //based on user who blocked the viewer
					// $cond[]	= 'AND NOT EXISTS (';
					// $cond[]	= 'select bs.' . $db->nameQuote('user_id') . ' from ' . $db->nameQuote('#__social_block_users') . ' as bs';
					// $cond[] = 'where (a.' . $db->nameQuote('actor_id') . ' = bs.' . $db->nameQuote('user_id');
					// $cond[] = 'and bs.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($viewer);

					// // exclude cluster's admin user #4448
					// $cond[] = 'and bs.' . $db->nameQuote('target_id') . ' NOT IN (';
					// $cond[] = '    select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where a.`cluster_id` = clsnd.`cluster_id` and clsnd.`admin` = 1)';

					// $cond[] = ') OR (';
					// $cond[] = 'a.' . $db->nameQuote('actor_id') . ' = bs.' . $db->nameQuote('target_id');
					// $cond[] = 'and bs.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($viewer) . ')';
					// $cond[] = ')';
				}

			} else {
				// guest privacy
				$cond[] = 'AND a.`access` = ' . $db->Quote('0');
			}

			// Exclude site admin
			if (!$isAdminSticky && $excludeSiteAdmin && !$isAdmin) {

				// Get a list of site administrators from the site.
				$userModel = ES::model('Users');
				$admins = $userModel->getSiteAdmins(true);

				if ($admins) {
					$ids = array();

					foreach ($admins as $adminId) {
						if ($my->id == $adminId) {
							continue;
						}

						$ids[] = $adminId;
					}

					if ($ids) {
						$cond[] = ' AND a.' . $db->nameQuote('actor_id') . ' NOT IN (' . implode(',', $ids) . ')';
					}
				}
			}

			if ($tag && !$matchAllTags) {
				if (count($tag) == 1) {
					$cond[]	= 'AND tags.' . $db->nameQuote('title') . '=' . $db->Quote($tag[0]);
				} else {
					$totalTags = count($tag);
					$tagQuery = '';

					for ($t = 0; $t < $totalTags; $t++) {
						$tagQuery .= ($t < $totalTags - 1) ? ' (tags.title = ' . $db->Quote($tag[ $t ]) . ') OR ' : ' (tags.title = ' . $db->Quote($tag[ $t ]) . ')';
					}

					$cond[]	= 'AND (' . $tagQuery . ')';
				}
			}

			if ($tag && $matchAllTags) {
				$totalTags = count($tag);
				$tagQuery = 'SELECT `stream_id` FROM ' . $db->nameQuote('#__social_stream_tags');
				$tagQuery .= 'WHERE ' . $db->nameQuote('title') . 'IN(';

				for ($i = 0; $i < $totalTags; $i++) {
					$tagQuery .= $db->Quote($tag[$i]);

					if ($i < $totalTags -1 ) {
						$tagQuery .= ',';
					}
				}

				$tagQuery .= ')';
				$tagQuery .= 'GROUP BY `stream_id` HAVING count(`stream_id`) = ' . $db->Quote($totalTags);

				$cond[] = 'AND tags.stream_id IN(' . $tagQuery . ')';
			}

			if ($noSticky) {
				// exclude sticky posts.
				$cond[] = ' AND a.`sticky_id` = 0';
			}
		}

		// lets get the limit dates here instead
		$limitDates = array();

		// Ensure that is sticky stream data, sort sticky id in order to show the recent pinned stream item
		if ($isSticky && !$noSticky && !$isStickyFilter) {
			$sortDate = 'sticky_id';
		}

		// ordering. DO NOT change the ordering.
		$order[] = 'ORDER BY a.' . $db->nameQuote($sortDate) . ' DESC';

		// startdate holding the larger date
		// enddte holding the smaller date.
		if (!$isSingleItem && !$limit && $direction == 'later') {
			$cond[]	= 'AND a.' . $db->nameQuote($sortDate) . ' >= ' . $db->Quote($limitstart);
			$cntCond[] = 'AND a.' . $db->nameQuote($sortDate) . ' >= ' . $db->Quote($limitstart);
		}

		// is this to avoid duplicate stream being loaded in pagination when there are new steams
		$isUsingStartDateLimit = false;
		if (!$isSingleItem && $limit && $startdatelimit) {
			$isUsingStartDateLimit = true;

			$cond[]	= 'AND a.' . $db->nameQuote($sortDate) . ' < ' . $db->Quote($startdatelimit);
		}

		// concate all the queries segments.
		$query = implode(' ', $query);
		$table = implode(' ', $table);

		$cond = implode(' ', $cond);
		$order = implode(' ', $order);


		$query = $query . ' ' . $table . ' ' . $cond . ' ' . $order;

		if (!$isSingleItem && $limit) {
			if ($isUsingStartDateLimit) {
				$query .= ' LIMIT ' . ($limit + 1);
			} else {
				$query .= ' LIMIT ' . $startlimit . ',' . ($limit + 1);
			}
		}

		// echo $query;
		// echo '<br><br>';
		// exit;

		$db->setQuery($query);
		$result = $db->loadObjectList();

		$counts = count($result);

		// now we need to remove the last index from the result.
		if (!$isSingleItem && $limit && $counts > $limit) {
			array_pop($result);
		}

		if ($counts > $limit) {
			$total = 1;
		} else {
			$total = 0;
		}

		$this->total = $total;

		$itemCnt = count($result);

		if ($itemCnt) {
			$streamIds = array();
			$streamContexts = array();
			$clusterIds = array();

			foreach ($result as $row) {
				$streamIds[] = $row->id;
				$streamContexts[] = $row->context_type;

				$this->nextdatelimit = $sortDate == 'modified' ? $row->modified : $row->created;

				if ($row->cluster_type == SOCIAL_TYPE_GROUP || $row->cluster_type == SOCIAL_TYPE_EVENT || $row->cluster_type == SOCIAL_TYPE_PAGE) {
					$clusterIds[] = $row->cluster_id;
				}
			}

			$this->uids = $streamIds;

			// -------------------------------------------------------------
			// This is the starting points of optimizing queries for stream.
			// -------------------------------------------------------------

			// @sam: it seems like adding these two slowing down the page load. lets comment it for now.
			// $streamTbl = ES::table('Stream');
			// $streamTbl->loadByBatch($streamIds);

			// $streamItemTbl = ES::table('StreamItem');
			// $streamItemTbl->loadByUIDBatch($streamIds);

			$this->setBatchRelatedItems($streamIds, $streamContexts, $viewer);

			if ($clusterIds) {
				$clusterIds = array_unique($clusterIds);
				ES::cache()->cacheClusters($clusterIds);
			}

			// set stream actors.
			$this->setActorsBatch($result);

			// set stream photos.
			$this->setMediaBatch($result);

			// set stream tagging
			$this->setTaggingBatch($streamIds);

			//set stream likes
			$like = ES::model('Likes');
			$like->setStreamLikesBatch($result);

			// Cache stream repost
			$repost = ES::model('Repost');
			$repost->setStreamRepostBatch($result);

			$share = ES::table('Share');
			$share->setSharesBatch($result);

			// comment count
			$commentModel = ES::model('Comments');
			$commentModel->setStreamCommentCountBatch($result);

			// privacy
			if ($sysconfig->get('privacy.enabled')) {
				$privacyModel = ES::model('Privacy');
				$privacyModel->setStreamPrivacyItemBatch($result);
			}
		}

		return $result;
	}

	/**
	 * Method to generate the accessible apps
	 *
	 * @since	3.1.0
	 * @access	private
	 */
	public function generateAccessibleAppsSql($includeAppsContainer, &$cond)
	{
		$db = ES::db();

		$container = array();
		$userContainer = array();

		foreach ($includeAppsContainer as $group => $includeApps) {

			if (!$includeApps) {
				continue;
			}

			$conditions = array();
			$tmpString = '';

			foreach ($includeApps as $iApp) {
				$tmpString .= ($tmpString) ? ',' . $db->Quote($iApp) : $db->Quote($iApp);
			}

			$condition = '(a.' . $db->nameQuote('context_type') . ' IN (' . $tmpString . ') and';
			if ($group == SOCIAL_APPS_GROUP_USER) {
				$condition .= ' a.' . $db->nameQuote('cluster_type') . ' is null)';
			} else {
				$condition .= ' a.' . $db->nameQuote('cluster_type') . ' = ' . $db->Quote($group) . ')';
			}

			$conditions[] = $condition;

			$query = implode(' AND ', $conditions);

			$query = '(' . $query . ')';

			if ($group == SOCIAL_APPS_GROUP_USER) {
				$userContainer[] = $query;
			} else {
				$container[] = $query;
			}
		}

		if ($userContainer || $container) {
			$mainQuery = 'AND (';

			if ($userContainer) {
				$mainQuery .= $userContainer[0];
			}

			if ($container) {
				if ($userContainer) {
					$mainQuery .= ' OR ';
				}

				$mainQuery .= ' (' . implode(' OR ', $container) . ')';
			}

			$mainQuery .= ')';

			$cond[] = $mainQuery;
		}
	}


	private function generateUnAccessibleAppsSQL($excludeAppsContainer, &$cond)
	{
		$db = ES::db();

		if ($excludeAppsContainer) {

			$container = array();
			$userContainer = array();

			$appOnly = array();
			$appWithVerbs = array();

			foreach ($excludeAppsContainer as $group => $excludeApps) {

				if (! $excludeApps) {
					continue;
				}

				foreach ($excludeApps as $app => $verbs) {

					if ($verbs === true) {
						$appOnly[$group][] = $app;
						continue;
					}

					// curently only user app of pages, groups and events app
					// will have this behavior by returning an object
					if (!is_array($verbs)) {
						if ($verbs->excludeVerb === true) {
							$appOnly[$verbs->group][] = $app;
							continue;
						}

						if (isset($appWithVerbs[$verbs->group][$app])) {
							$tmpVerbs = $appWithVerbs[$verbs->group][$app];

							$tmpVerbs = array_merge($tmpVerbs, $verbs->excludeVerb);
							$tmpVerbs = array_unique($tmpVerbs);

							$appWithVerbs[$verbs->group][$app] = $tmpVerbs;
						} else {
							$appWithVerbs[$verbs->group][$app] = $verbs->excludeVerb;
						}

						continue;
					}

					if (isset($appWithVerbs[$group][$app])) {

						$tmpVerbs = $appWithVerbs[$group][$app];

						$tmpVerbs = array_merge($tmpVerbs, $verbs);
						$tmpVerbs = array_unique($tmpVerbs);

						$appWithVerbs[$group][$app] = $tmpVerbs;

					} else {
						$appWithVerbs[$group][$app] = $verbs;
					}
				}
			}

			// lets process `app only` exclusions
			if (!empty($appOnly)) {

				$conditions = array();

				foreach ($appOnly as $group => $apps) {

					$tmpString = '';

					foreach ($apps as $app) {
						$tmpString .= ($tmpString) ? ',' . $db->Quote($app) : $db->Quote($app);
					}

					$tmpCond = 'AND NOT (a.' . $db->nameQuote('context_type') . ' IN (' . $tmpString . ') and';
					if ($group == SOCIAL_APPS_GROUP_USER) {
						$tmpCond .= ' a.' . $db->nameQuote('cluster_type') . ' is null)';
					} else {
						$tmpCond .= ' a.' . $db->nameQuote('cluster_type') . ' <=> ' . $db->Quote($group) . ')';
					}

					$cond[] = $tmpCond;
				}
			}

			if ($appWithVerbs) {

				$conditions = array();

				foreach ($appWithVerbs as $group => $apps) {

					foreach ($apps as $app => $verbs) {
						if (empty($verbs)) {
							continue;
						}

						$verbString = '';

						foreach ($verbs as $verb) {
							$verbString .= ($verbString) ? ',' . $db->Quote($verb) : $db->Quote($verb);
						}

						$tmpCond = ' AND NOT (a.' . $db->nameQuote('context_type') . ' = ' . $db->Quote($app) . ' and';
						$tmpCond .= ' a.' . $db->nameQuote('verb') . ' IN (' . $verbString . ') and ';
						if ($group == SOCIAL_APPS_GROUP_USER) {
							$tmpCond .= ' a.' . $db->nameQuote('cluster_type') . ' is null)';
						} else {
							$tmpCond .= ' a.' . $db->nameQuote('cluster_type') . ' <=> ' . $db->Quote($group) . ')';
						}

						$cond[] = $tmpCond;
					}
				}

			}

		}
	}


	private function getUnAccessilbleUserApps($userId = null, $group =  SOCIAL_APPS_GROUP_USER, $perspective = null)
	{
		static $_cache = array();

		if (! isset($_cache[$group])) {

			$db = ES::db();
			$sql = $db->sql();
			$exclude = array();

			// if ($userId) {
				$query = "select `element` from `#__social_apps`";
				$query .= " where `type` = 'apps'";
				$query .= " and `state` = 0";

				if ($group && $group != 'all') {
					$query .= ' and `group` = ' . $db->Quote($group);
				}

				// $query .= " union ";
				// $query .= "select a.`element` from `#__social_apps` as a";
				// $query .= " left join `#__social_apps_map` as b on a.`id` = b.`app_id`";
				// $query .= "	and b.`type` = 'user'";
				// $query .= "	and b.`uid` = '$userId'";
				// $query .= " where a.`type` = 'apps'";
				// $query .= " and a.`group` = 'user'";
				// $query .= " and a.`default` = 0";
				// $query .= " and a.`system` = 0";
				// $query .= " and a.`state` = 1";
				// $query .= " and b.`app_id` is null";

				$sql->raw($query);
				$db->setQuery($sql);

				$results = $db->loadColumn();

				if ($results) {
					foreach ($results as $app) {
						$exclude[$app] = true;
					}
				}
			// }

			//now we need to triggers all the apps to see if there is any setting to exclude certain verb's item or not.
			$appLib = ES::getInstance('Apps');
			$appLib->load($group); // load user apps

			// Pass arguments by reference.
			$args = array(&$exclude, $perspective);

			// @trigger: onStreamVerbExclude
			$dispatcher = ES::dispatcher();

			if ($group == 'all') {
				$result = $dispatcher->trigger(SOCIAL_APPS_GROUP_USER , 'onStreamVerbExclude' , $args);
				$result = $dispatcher->trigger(SOCIAL_APPS_GROUP_GROUP , 'onStreamVerbExclude' , $args);
				$result = $dispatcher->trigger(SOCIAL_APPS_GROUP_EVENT , 'onStreamVerbExclude' , $args);
				$result = $dispatcher->trigger(SOCIAL_APPS_GROUP_PAGE , 'onStreamVerbExclude' , $args);
			} else {
				$result = $dispatcher->trigger($group , 'onStreamVerbExclude' , $args);
			}


			$_cache[$group] = $exclude;
		}

		return $_cache[$group];
	}

	public function setMediaBatch($result)
	{
		$photoIds 	= array();
		$albumIds 	= array();

		$streamModel = ES::model('Stream');

		foreach($result as $item)
		{
			if($item->context_type == 'photos')
			{
				$relatedData = $streamModel->getBatchRalatedItem($item->id);

				if($relatedData)
				{
					foreach($relatedData as $rdata)
					{
						$photoIds[] = $rdata->context_id;
					}
				}
			}
		}

		if($photoIds)
		{
			// photos
			$photo = ES::table('Photo');
			$photo->setCacheable(true);
			$albumIds = $photo->loadByBatch($photoIds);

			// photos meta
			$photoModel = ES::model('Photos');
			$photoModel->setCacheable(true);
			$photoModel->setMetasBatch($photoIds);
		}

		if($albumIds)
		{
			$albumIds = array_unique($albumIds);
			$album = ES::table('Album');
			$album->loadByBatch($albumIds);
		}

	}

	public function setActorsBatch($result)
	{
		$config = ES::config();
		$actorIds  = array();

		$streamModel = ES::model('Stream');

		foreach ($result as $item) {
			$relatedData = $streamModel->getBatchRalatedItem($item->id);

			if ($relatedData) {
				foreach ($relatedData as $rdata) {
					$actorIds[] = $rdata->actor_id;

					if ($rdata->target_id) {
						if (!($rdata->context_type == 'photos' && $rdata->verb == 'add')
							&& !($rdata->context_type == 'shares' && $rdata->verb == 'add.stream')) {
							$actorIds[] = $rdata->target_id;
						}
					}
				}
			}
		}

		$actorIds[] = ES::user()->id;
		$actors = array_unique($actorIds);

		if ($actors) {
			// Preload users
			ES::user($actors);

			if ($config->get('privacy.enabled')) {
				ES::cache()->cacheUsersPrivacy($actors);
			}
		}
	}


	public function setBatchRelatedItems($uids , $contexts, $viewer = null)
	{
		// _relateditems

		// make sure the keys is not already added.
		foreach($uids as $id)
		{
			if(array_key_exists($id, self::$_relateditems))
			{
				return ;
			}
		}

		// Get related activities for aggregation.
		$query 		= array();
		$db 		= ES::db();

		$contextSegments = array();

		for($i = 0; $i < count($uids); $i++)
		{
			$contextSegments[ $contexts[$i] ][] = $uids[$i];
		}

		$ci = 0;
		$cCount = count($contextSegments);
		foreach($contextSegments as $context => $cSegmentIds)
		{
			$idSegments = array_chunk($cSegmentIds, 5);

			for($i = 0; $i < count($idSegments); $i++)
			{
				$segment    = $idSegments[$i];
				$ids  		= implode(',', $segment);

				$query[]	= 'SELECT * FROM ' . $db->nameQuote('#__social_stream_item') . ' as a';
				$query[]	= 'WHERE ' . $db->nameQuote('uid') . ' IN (' . $ids . ')';

				if($context != 'all' && $context != SOCIAL_TYPE_STREAM)
				{
					$query[]	= 'AND ' . $db->nameQuote('context_type') . '=' . $db->Quote($context);
				}

				if($viewer)
				{
					$query[]	= 'AND a.' . $db->nameQuote('id') . ' NOT IN (';
					$query[]	= 'SELECT h.' . $db->nameQuote('uid') . ' FROM ' . $db->nameQuote('#__social_stream_hide') . ' AS h';
					$query[]	= 'WHERE h.' . $db->nameQuote('user_id') . '=' . $db->Quote($viewer);
					$query[]	= 'AND h.' . $db->nameQuote('type') . '=' . $db->Quote('activity');
					$query[]	= ')';
				}

				if(($i + 1)  < count($idSegments))
				{
					$query[] = ' UNION ';
				}
			}

			if(++$ci < $cCount)
			{
				$query[] = ' UNION ';
			}
		}

		$query 		= implode(' ' , $query);

		$db->setQuery($query);

		$result = $db->loadObjectList();

		foreach($result as $row)
		{
			self::$_relateditems[ $row->uid ][] = $row;
		}

		// var_dump(count(self::$_relateditems));

	}


	public function getBatchRalatedItem($uid)
	{
		if(isset(self::$_relateditems[ $uid ]))
		{
			return self::$_relateditems[ $uid ];
		}
	}


	/**
	 * Get related activities from a single stream so that we can perform aggregation.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The unique id.
	 * @param	string	The context.
	 */
	public function getRelatedActivities($uid, $context, $viewer = null)
	{

		$keys = $uid;

		if(isset(self::$_relateditems[$keys]))
		{
			return self::$_relateditems[$keys];
		}

		// items not found from static variable. lets fall back to manual sql method.
		$db 		= ES::db();

		// Get related activities for aggregation.
		$query 		= array();
		$query[]	= 'SELECT * FROM ' . $db->nameQuote('#__social_stream_item') . ' as a';
		$query[]	= 'WHERE ' . $db->nameQuote('uid') . '=' . $db->Quote($uid);

		if($context != 'all' && $context != SOCIAL_TYPE_STREAM)
		{
			$query[]	= 'AND ' . $db->nameQuote('context_type') . '=' . $db->Quote($context);
		}

		if($viewer)
		{
			$query[]	= 'AND a.' . $db->nameQuote('id') . ' NOT IN (';
			$query[]	= 'SELECT h.' . $db->nameQuote('uid') . ' FROM ' . $db->nameQuote('#__social_stream_hide') . ' AS h';
			$query[]	= 'WHERE h.' . $db->nameQuote('user_id') . '=' . $db->Quote($viewer);
			$query[]	= 'AND h.' . $db->nameQuote('type') . '=' . $db->Quote('activity');
			$query[]	= ')';
		}

		$query 		= implode(' ' , $query);

		$db->setQuery($query);

		$result 	= $db->loadObjectList();

		// log into static variable.
		self::$_relateditems[$keys] = $result;

		return $result;
	}

	public function setBatchActivityItems($data)
	{
		foreach($data as $row)
		{
			self::$_activitylogs[ $row->id ][] = $row;
		}

		// var_dump(self::$_activitylogs);
		// exit;
	}

	/**
	 * Get related activities from a single stream so that we can perform aggregation.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		activity item id.
	 */
	public function getActivityItem($uid, $column = 'id')
	{

		if($column == 'uid')
		{
			if(isset(self::$_relateditems[ $uid ]))
			{
				return self::$_relateditems[ $uid ];
			}
		}
		else
		{
			if(isset(self::$_activitylogs[ $uid ]))
			{
				return self::$_activitylogs[ $uid ];
			}
		}


		$db 		= ES::db();

		// Get related activities for aggregation.
		$sql 		= $db->sql();
		$sql->select('#__social_stream_item');
		$sql->where($column , $uid);

		$db->setQuery($sql);

		$result 	= $db->loadObjectList();

		return $result;
	}

	/**
	 * used in stream api
	 */
	public function updateStream($data)
	{
		$db = ES::db();
		$sql = $db->sql();

		$date = ES::date($data->created);
		$duration   = 30;
		$isClusterType = ($data->cluster_id && $data->cluster_type) ? true : false;

		// Get the config obj.
		$config = ES::config();

		// Always default to true
		$allowAggregation = $config->get('stream.aggregation.enabled');

		// The duration between activities.
		$duration = $config->get('stream.aggregation.duration');

		if ($data->isAggregate) {

			// retrive the last item
			$query  = 'select a.`id`, a.`uid` from `#__social_stream_item` as a';
			if ($isClusterType) {
				$query .= ' inner join `#__social_stream` as b on a.`uid` = b.`id` and b.`cluster_id` = ' . $db->Quote($data->cluster_id);
			}

			$query  .= ' where a.`actor_id` = ' . $db->Quote($data->actor_id);
			$query  .= ' and a.`actor_type` = ' . $db->Quote($data->actor_type);
			$query  .= ' and a.`context_type` = ' . $db->Quote($data->context_type);
			$query  .= ' and a.`verb` = ' . $db->Quote($data->verb);
			$query  .= ' and a.`sitewide` = ' . $db->Quote($data->sitewide);
			$query  .= ' and date_add(a.`created` , INTERVAL ' . $duration . ' MINUTE) >= ' . $db->Quote($date->toMySQL());
			if ($data->aggregateWithTarget) {
				$query  .= ' and a.`target_id` = ' . $db->Quote($data->target_id);
			}
			$query	.= ' order by a.`created` DESC limit 1';

			$sql->raw($query);
			$db->setQuery($sql);

			$result = $db->loadObject();

			if (isset($result->uid)) {
				$streamTbl	= ES::table('Stream');
				$streamTbl->load($result->uid);
				$streamTbl->modified    = $date->toMySQL();
				$streamTbl->store();

				return $result->uid;
			}

			// if not found.
			$query  = 'select a.* from `#__social_stream_item` as a';
			if ($isClusterType) {
				// $query .= ' inner join `#__social_clusters` as b on a.`cluster_id` = b.`id` and a.`cluster_id` = ' . $db->Quote($data->cluster_id);
				$query .= ' inner join `#__social_stream` as b on a.`uid` = b.`id` and b.`cluster_id` = ' . $db->Quote($data->cluster_id);
			}
			$query	.= ' order by a.`created` DESC limit 1';

			$db->setQuery($query);

			$result = $db->loadObject();

			if (isset($result->id)) {
				$doAggregation = false;

				if(!$data->aggregateWithTarget &&
					($result->actor_id 		== $data->actor_id &&
					$result->actor_type 	== $data->actor_type &&
					$result->context_type 	== $data->context_type &&
					$result->verb 			== $data->verb &&
					$result->sitewide 		== $data->sitewide)) {
					// here
					$doAggregation = true;
				} else if ($data->aggregateWithTarget &&
					($result->actor_id 		== $data->actor_id &&
					$result->actor_type 	== $data->actor_type &&
					$result->context_type 	== $data->context_type &&
					$result->verb 			== $data->verb &&
					$result->sitewide 		== $data->sitewide &&
					$result->target_id 		== $data->target_id)) {
					// here
					$doAggregation = true;
				}

				if ($doAggregation) {
					$streamTbl	= ES::table('Stream');
					$streamTbl->load($result->uid);
					$streamTbl->modified    = $date->toMySQL();
					$streamTbl->store();

					return $result->uid;
				}


			}

		}

		// Create a new stream record
		$table = ES::table('Stream');
		$table->bind($data);

		$table->actor_type = isset($data->actor_type) && !empty($data->actor_type) ? $data->actor_type : 'user';

		// Experiment for Page
		$table->post_as = isset($data->post_as) && !empty($data->post_as) ? $data->post_as : 'user';

		$table->alias = '';

		// this is experimental codes
		//$table->alias = isset($data->alias) && !empty($data->alias) ? $data->alias : $data->actor_id;
		$table->created = $date->toSql();
		$table->params = $data->params;
		$table->modified = $date->toSql();
		$table->cluster_id = $data->cluster_id;
		$table->cluster_type = $data->cluster_type;
		$table->verb = $data->verb;
		$table->background_id = $data->background_id;

		// Set the state of the stream
		$table->state = $data->state;

		// Privacy access
		$table->privacy_id = $data->privacy_id;
		$table->access = $data->access;
		$table->custom_access = $data->custom_access;

		// we keep the number of fields condition.
		$table->field_access = $data->field_access ? count($data->field_access) : 0;

		$state = $table->store();

		if ($state && $data->field_access) {

			// now we need to update stream's field privacy data.
			// lets delete any previous saved records. we will do a re-insert method.

			$query = "delete from `#__social_stream_privacy_field` where `stream_id` = " . $db->Quote($table->id);
			$db->setQuery($query);
			$db->query();

			// $data->field_access is already holding an array. we do not need to explode(';');
			$fields = $data->field_access;

			$query = "insert into `#__social_stream_privacy_field` (`stream_id`, `field_key`, `field_value`) values ";

			$insertValues = array();
			foreach ($fields as $field) {
				$fieldData = explode('|', $field);

				$fieldElement = $fieldData[0];
				$fieldKey = $fieldData[1];
				$fieldValues = isset($fieldData[2]) && $fieldData[2] ? explode(',', $fieldData[2]) : array();

				if ($fieldValues) {
					foreach ($fieldValues as $fieldValue) {
						$insertValues[] = "(" . $db->Quote($table->id) . ',' . $db->Quote($fieldKey) . ',' . $db->Quote($fieldValue) . ')';
					}
				}
			}

			if ($insertValues) {
				$insertQuery = implode(',', $insertValues);
				$query .= $insertQuery;

				$db->setQuery($query);
				$db->query();
			}
		}

		return $table->id;
	}

	/**
	 * Remove mentions associated with a stream
	 *
	 * @since	1.2.8
	 * @access	public
	 * @param	int 	The stream's id
	 * @return	bool
	 */
	public function removeMentions($streamId)
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->delete('#__social_stream_tags');
		$sql->where('stream_id', $streamId);

		$db->setQuery($sql);

		return $db->Query();
	}

	/**
	 * Creates the mentions
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function addMentions($streamId , $mentions, $options = array())
	{
		// We should delete all tags for that streamId
		// and let it re-store again
		$db = ES::db();
		$query = "delete from `#__social_stream_tags` where `stream_id` = " . $db->Quote($streamId);
		$db->setQuery($query);
		$db->query();

		// Check if this is a scheduled post mentioned.
		$scheduled = isset($options['scheduled']) ? true : false;

		foreach ($mentions as $mention) {

			if (!isset($mention->type) || $mention->type == '') {
				continue;
			}

			$tag = ES::table('StreamTags');
			$tag->stream_id = $streamId;

			// Since this is not a with property.
			$tag->with = 0;

			// Set the offset and length
			$tag->offset = $mention->start;
			$tag->length = $mention->length;

			if ($mention->type == 'entity') {

				$parts = explode(':', $mention->value);

				if (count($parts) != 2) {
					continue;
				}

				$type = $parts[0];
				$id = $parts[1];

				// Set the type and uid
				$tag->utype = $type;
				$tag->uid = $id;
			}

			if ($mention->type == 'hashtag') {
				$tag->utype = 'hashtag';
				$tag->uid = 0;
				$tag->title = trim($mention->value);

				if ($scheduled) {
					$tag->state = SOCIAL_STATE_SCHEDULED;
				}
			}

			if ($mention->type == 'emoticon') {
				$title = str_replace(array('(', ')'), '', trim($mention->value));

				// Check if the title exists in database
				$model = ES::model('Emoticons');

				$emoticons = $model->getItems(array('title' => $title));

				if (!$emoticons) {
					continue;
				}

				$tag->utype = 'emoticon';
				$tag->uid = 0;
				$tag->title = $title;
			}

			$tag->store();
		}
	}

	public function setWith($streamId, $ids, $options = array())
	{
		// Check if this is a scheduled post mentioned.
		$scheduled = isset($options['scheduled']) ? true : false;

		if (!is_array($ids)) {
			$ids = array($ids);
		}

		foreach ($ids as $id) {
			if (!$id) {
				continue;
			}

			$tbl = ES::table('StreamTags');
			$tbl->stream_id = $streamId;
			$tbl->uid = $id;
			$tbl->utype = SOCIAL_STREAM_TAGGING_TYPE_USER;
			$tbl->with = 1;

			if ($scheduled) {
				$tbl->state = SOCIAL_STATE_SCHEDULED;
			}

			$tbl->store();
		}
	}

	public function setTaggingBatch($streamIds)
	{
		// _tagging

		$db = ES::db();
		$sql = $db->sql();

		$ids = array();
		foreach ($streamIds as $sid) {
			if (! isset(self::$_tagging[$sid])) {
				$ids[] = $db->Quote($sid);
				self::$_tagging[$sid] = array();
			}
		}

		if ($ids) {
			$uids = implode(',', $ids);

			$query = 'SELECT * FROM `#__social_stream_tags` WHERE `stream_id` IN(' . $uids . ') ORDER BY `stream_id`, `offset` DESC';
			$sql->raw($query);

			$db->setQuery($sql);

			$result = $db->loadObjectList();

			if ($result) {
				foreach ($result as $row) {

					if ($row->with) {
						self::$_tagging[$row->stream_id]['with'][] = ES::user($row->uid);
					} else {
						//this is a mention
						$obj = new stdClass();
						$obj->type = $row->utype;
						$obj->offset = $row->offset;
						$obj->length = $row->length;
						$obj->id = $row->id;

						if ($row->utype == SOCIAL_TYPE_USER) {
							$obj->user = ES::user($row->uid);

							self::$_tagging[$row->stream_id]['tags'][] = $obj;
						}

						if ($row->utype == 'hashtag' || $row->utype == 'emoticon') {
							$obj->title = $row->title;
							self::$_tagging[$row->stream_id]['tags'][] = $obj;
						}
					}

				}
			}
		}

	}

	/**
	 * Get tags for the stream item
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getTagging($streamId , $reqType = 'with')
	{
		if (!isset(self::$_tagging[$streamId])) {

			$db = ES::db();
			$sql = $db->sql();

			$sql->select('#__social_stream_tags', 'a');
			$sql->column('a.*');
			$sql->where('a.stream_id', $streamId);
			$sql->order('a.offset', 'DESC');

			$db->setQuery($sql);
			$result = $db->loadObjectList();

			$withs = array();
			$tags = array();

			if ($result) {
				$users = array();

				foreach ($result as $row) {
					if ($row->uid && $row->utype == SOCIAL_TYPE_USER) {
						$users[] = $row->uid;
					}
				}

				// Preload the user's list
				ES::user($users);

				foreach ($result as $row) {
					if ($row->with) {
						$withs[] = ES::user($row->uid);
					} else {
						//this is a mention
						$obj = new stdClass();
						$obj->type = $row->utype;
						$obj->offset = $row->offset;
						$obj->length = $row->length;
						$obj->id = $row->id;

						if ($row->utype == SOCIAL_TYPE_USER) {
							$obj->user = ES::user($row->uid);
							$tags[] = $obj;
						}

						if ($row->utype == 'hashtag' || $row->utype == 'emoticon') {
							$obj->title = $row->title;
							$tags[] = $obj;
						}
					}
				}
			}

			self::$_tagging[$streamId]['with'] = $withs;
			self::$_tagging[$streamId]['tags'] = $tags;
		}

		if (isset(self::$_tagging[$streamId][$reqType])) {
			return self::$_tagging[$streamId][$reqType];
		}

		return array();
	}

	/**
	 * Retrieves stream actor data.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getStreamActor($streamId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select `actor_id` from `#__social_stream` where `id` = ' . $db->Quote($streamId);
		$sql->raw($query);

		$db->setQuery($sql);

		$result = $db->loadResult();

		if ($result) {
			$user = ES::user($result);
			return $user;
		}

		return false;
	}

	/**
	 * Retrieves pending posts stream counts for cluster
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getModeratedPostsCount($clusterId, $clusterType, $exclude = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		$config = ES::config();
		$sortDate = $this->streamPaginationSort;

		$query = 'select count(1) as `cnt` from `#__social_stream` as a';
		$query .= ' WHERE a.`state` = ' . $db->Quote(SOCIAL_STREAM_STATE_MODERATE);
		$query .= ' and a.`cluster_id` = ' . $db->Quote($clusterId);
		$query .= ' and a.`cluster_type` = ' . $db->Quote($clusterType);

		if ($exclude) {
			$query .= ' and a.`id` NOT IN (' . $exclude . ')';
		}

		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Retrieves new stream item counts for cluster
	 *
	 * @since	1.0
	 * @access	public
	 * @return
	 */
	public function getClusterUpdateCount($source, $currentdate, $clusterType, $clusterId, $exclude = null)
	{
		$db 	= ES::db();
		$sql  	= $db->sql();

		$config 	= ES::config();
		$sortDate   = $this->streamPaginationSort;

		$query = 'select count(1) as `cnt`, a.`cluster_type` as `type` from `#__social_stream` as a';
		// $query = 'select count(1) as `cnt`, ' . $db->Quote('feeds') . ' as `type` from `#__social_stream` as a';
		$query .= ' WHERE a.`state` = 1';
		$query .= ' and a.`cluster_id` = ' . $db->Quote($clusterId);
		$query .= ' and a.`cluster_type` = ' . $db->Quote($clusterType);
		$query .= ' and a.`modified` > ' . $db->Quote($currentdate);
		if($exclude)
		{
			$query .= ' and a.`id` NOT IN (' . $exclude . ')';
		}

		// exclude these context items

		$excludeApps = array();
		$excludeApps[$clusterType] = $this->getUnAccessilbleUserApps('',  $clusterType);
		$cond = array();
		$this->generateUnAccessibleAppsSQL($excludeApps, $cond);

		$query .= ' ' . implode(' ', $cond);

		// do not get the context / actor that user choosed to hide.
		$user = ES::user();

		if (! $user->guest) {
			$userId = $user->id;

			//based on context
			$query	.= ' AND NOT EXISTS (';
			$query .= ' SELECT h1.' . $db->nameQuote('context') . ' FROM ' . $db->nameQuote('#__social_stream_hide') . ' AS h1';
			$query .= ' WHERE a.' . $db->nameQuote('context_type') . ' = h1.' . $db->nameQuote('context');
			$query .= ' AND h1.' . $db->nameQuote('user_id') . '=' . $db->Quote($userId) ;
			$query .= ' AND h1.' . $db->nameQuote('uid') . ' = ' . $db->Quote('0');
			$query .= ' AND h1.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote('0');
			$query .= ')';

			//based on actor
			$query .= ' AND NOT EXISTS (';
			$query .= ' SELECT h2.' . $db->nameQuote('actor_id') . ' FROM ' . $db->nameQuote('#__social_stream_hide') . ' AS h2';
			$query .= ' WHERE a.' . $db->nameQuote('actor_id') . '= h2.' . $db->nameQuote('actor_id') ;
			$query .= ' AND h2.' . $db->nameQuote('user_id') . '=' . $db->Quote($userId) ;
			$query .= ' AND h2.' . $db->nameQuote('uid') . ' = ' . $db->Quote('0');
			$query .= ' AND h2.' . $db->nameQuote('context') . ' is null';
			$query .= ')';

			if ($config->get('users.blocking.enabled')) {
				$query .= ' AND NOT EXISTS (';
				$query .= ' select bs.' . $db->nameQuote('user_id') . ' from ' . $db->nameQuote('#__social_block_users') . ' as bs';
				$query .= ' where (a.' . $db->nameQuote('actor_id') . ' = bs.' . $db->nameQuote('user_id');
				$query .= ' and bs.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($userId);

				// exclude cluster's admin user #4448
				$query .= ' and bs.' . $db->nameQuote('target_id') . ' NOT IN (';
				$query .= '    select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where a.`cluster_id` = clsnd.`cluster_id` and clsnd.`admin` = 1)';

				$query .= ') OR (';
				$query .= ' a.' . $db->nameQuote('actor_id') . ' = bs.' . $db->nameQuote('target_id');
				$query .= ' and bs.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId) . ')';

				$query .= ')';
			}
		}

		// echo $query;exit;

		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadAssocList();
		return $result;
	}

	/**
	 * Retrieves new stream item counts for users
	 *
	 * @since	3.2
	 * @access	public
	 */
	public function getClusterCategoryUpdateCount($source, $currentdate, $type, $id = '', $exclude = null)
	{
		$db = ES::db();
		$sql = $db->sql();
		$user = ES::user();
		$isAdmin = $user->isSiteAdmin();

		$streamLib = ES::stream();

		$db = ES::db();
		$sql = $db->sql();

		$config = ES::config();
		$sortDate = $this->streamPaginationSort;

		$tbl = ES::table('ClusterCategory');
		$tbl->load($id);

		$clusterType = $tbl->type;

		$query = 'select count(1) as `cnt`, ' . $db->Quote($type) . ' as `type`';
		$query .= ' from `#__social_stream` as a';
		$query .= ' INNER JOIN `#__social_clusters` cc on a.`cluster_id` = cc.`id` and cc.`category_id` = ' . $db->Quote($id);
		$query .= ' WHERE a.`state` = 1';
		$query .= ' and a.`modified` > ' . $db->Quote($currentdate);
		if ($exclude) {
			$query .= ' and a.`id` NOT IN (' . $exclude . ')';
		}

		// exclude these context items

		$excludeApps = array();
		$excludeApps[$clusterType] = $this->getUnAccessilbleUserApps('',  $clusterType);
		$cond = array();
		$this->generateUnAccessibleAppsSQL($excludeApps, $cond);

		$query .= ' ' . implode(' ', $cond);

		// do not get the context / actor that user choosed to hide.
		$user = ES::user();

		if (!$user->guest) {
			$userId = $user->id;

			//based on context
			$query	.= ' AND NOT EXISTS (';
			$query .= ' SELECT h1.' . $db->nameQuote('context') . ' FROM ' . $db->nameQuote('#__social_stream_hide') . ' AS h1';
			$query .= ' WHERE a.' . $db->nameQuote('context_type') . ' = h1.' . $db->nameQuote('context');
			$query .= ' AND h1.' . $db->nameQuote('user_id') . '=' . $db->Quote($userId) ;
			$query .= ' AND h1.' . $db->nameQuote('uid') . ' = ' . $db->Quote('0');
			$query .= ' AND h1.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote('0');
			$query .= ')';

			//based on actor
			$query .= ' AND NOT EXISTS (';
			$query .= ' SELECT h2.' . $db->nameQuote('actor_id') . ' FROM ' . $db->nameQuote('#__social_stream_hide') . ' AS h2';
			$query .= ' WHERE a.' . $db->nameQuote('actor_id') . '= h2.' . $db->nameQuote('actor_id') ;
			$query .= ' AND h2.' . $db->nameQuote('user_id') . '=' . $db->Quote($userId) ;
			$query .= ' AND h2.' . $db->nameQuote('uid') . ' = ' . $db->Quote('0');
			$query .= ' AND h2.' . $db->nameQuote('context') . ' is null';
			$query .= ')';

			if ($config->get('users.blocking.enabled')) {
				$query .= ' AND NOT EXISTS (';
				$query .= ' select bs.' . $db->nameQuote('user_id') . ' from ' . $db->nameQuote('#__social_block_users') . ' as bs';
				$query .= ' where (a.' . $db->nameQuote('actor_id') . ' = bs.' . $db->nameQuote('user_id');
				$query .= ' and bs.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($userId);

				// exclude cluster's admin user #4448
				$query .= ' and bs.' . $db->nameQuote('target_id') . ' NOT IN (';
				$query .= '    select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where a.`cluster_id` = clsnd.`cluster_id` and clsnd.`admin` = 1)';

				$query .= ') OR (';
				$query .= ' a.' . $db->nameQuote('actor_id') . ' = bs.' . $db->nameQuote('target_id');
				$query .= ' and bs.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId) . ')';

				$query .= ')';
			}
		}

		// echo $query;
		// echo '<br><br>';

		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadAssocList();
		return $result;
	}

	/**
	 * Retrieves new stream item counts for users
	 *
	 * @since	1.0
	 * @access	public
	 * @return
	 */
	public function getUpdateCount($source, $currentdate, $type, $id = '', $exclude = null)
	{
		$clusterCategories = array('groupcategory', 'eventcategory', 'pagecategory');
		if (in_array($type, $clusterCategories)) {
			$counts = $this->getClusterCategoryUpdateCount($source, $currentdate, $type, $id, $exclude);
			return $counts;
		}

		$db = ES::db();
		$sql = $db->sql();
		$user = ES::user();
		$isAdmin = $user->isSiteAdmin();

		$streamLib = ES::stream();


		$userId = $user->id;
		if ($type == 'me' || $type == 'following') {
			if ($id) {
				$userId = (int) $id;
			}
		}

		$config = ES::config();
		$sortDate = $this->streamPaginationSort;

		$commonCond = '';

		// we use the > to unsure the same stream will not get counted.
		// this is to prevent when someone post a story at the same time as the current date being returned by the
		// checkupdate, which lead to duplicate stream.
		$commonCond .= ' WHERE a.`state` = 1';
		$commonCond .= ' and a.`actor_type` = ' . $db->Quote(SOCIAL_TYPE_USER);
		$commonCond .= ' and a.`' . $sortDate . '` > ' . $db->Quote($currentdate);
		if ($exclude) {
			$commonCond .= ' and a.`id` NOT IN (' . $exclude . ')';
		}

		// we do not want stream item from clusters.
		$commonCond .= ' and (';
		$commonCond .= ' (a.`cluster_id`= 0) OR';
		$commonCond .= ' (a.`cluster_id` > 0 and a.`cluster_access` IN (1,4))';
		if ($userId) {
			$commonCond .= 'OR (a.`cluster_id` > 0 and a.`cluster_access` > 1 and ' . $userId . ' IN (select scn.`uid` from `#__social_clusters_nodes` as scn where scn.`cluster_id` = a.`cluster_id` and scn.`type` = ' . $db->Quote(SOCIAL_TYPE_USER) . ' and scn.`state` = 1))';
		}
		$commonCond .= ')';

		// exclude these context items
		// $excludeApps = $this->getUnAccessilbleUserApps($userId);

		$excludeUserApps = $this->getUnAccessilbleUserApps($userId, SOCIAL_APPS_GROUP_USER);
		$excludeGroupApps = $this->getUnAccessilbleUserApps($userId, SOCIAL_APPS_GROUP_GROUP);
		$excludeEventApps = $this->getUnAccessilbleUserApps($userId, SOCIAL_APPS_GROUP_EVENT);
		$excludePageApps = $this->getUnAccessilbleUserApps($userId, SOCIAL_APPS_GROUP_PAGE);

		$excludeApps = array();

		if ($excludeUserApps) {
			$excludeApps[SOCIAL_APPS_GROUP_USER] = $excludeUserApps;
		}

		if ($excludeGroupApps) {
			$excludeApps[SOCIAL_APPS_GROUP_GROUP] = $excludeGroupApps;
		}

		if ($excludeEventApps) {
			$excludeApps[SOCIAL_APPS_GROUP_EVENT] = $excludeEventApps;
		}

		if ($excludePageApps) {
			$excludeApps[SOCIAL_APPS_GROUP_PAGE] = $excludePageApps;
		}

		$cond = array();
		$this->generateUnAccessibleAppsSQL($excludeApps, $cond);

		$commonCond .= ' ' . implode(' ', $cond);

		// do not get the context / actor that user choosed to hide.
		if ($userId) {
			if (!$isAdmin && $config->get('privacy.enabled') ) {
				// privacy here.
				$commonCond	.= ' AND (';

				//public
				$commonCond	.= ' (a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_PUBLIC) . ') OR';

				//member
				$commonCond	.= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_MEMBER) . ') AND (' . $userId . ' > 0)) OR ';

				if ($config->get('friends.enabled')) {
					//friends
					$commonCond	.= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND ((' . $streamLib->generateIsFriendSQL('a.`actor_id`', $userId) . ') > 0)) OR ';
				} else {
					// fall back to 'member'
					$commonCond	.= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_FRIEND) . ') AND (' . $userId . ' > 0)) OR ';
				}

				//only me
				$commonCond	.= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_ONLY_ME) . ') AND (a.`actor_id` = ' . $userId . ')) OR ';

				// custom
				$commonCond	.= ' ((a.`access` = ' . $db->Quote(SOCIAL_PRIVACY_CUSTOM) . ') AND (a.`custom_access` LIKE ' . $db->Quote('%,' . $userId . ',%') . '   )) OR ';

				// my own items.
				$commonCond	.= ' (a.`actor_id` = ' . $userId . ')';

				// privacy checking end here.
				$commonCond .= ')';
			}


			//based on context
			$commonCond	.= ' AND NOT EXISTS (';
			$commonCond .= ' SELECT h1.' . $db->nameQuote('context') . ' FROM ' . $db->nameQuote('#__social_stream_hide') . ' AS h1';
			$commonCond .= ' WHERE a.' . $db->nameQuote('context_type') . ' = h1.' . $db->nameQuote('context');
			$commonCond .= ' AND h1.' . $db->nameQuote('user_id') . '=' . $db->Quote($userId) ;
			$commonCond .= ' AND h1.' . $db->nameQuote('uid') . ' = ' . $db->Quote('0');
			$commonCond .= ' AND h1.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote('0');
			$commonCond .= ')';

			//based on actor
			$commonCond .= ' AND NOT EXISTS (';
			$commonCond .= ' SELECT h2.' . $db->nameQuote('actor_id') . ' FROM ' . $db->nameQuote('#__social_stream_hide') . ' AS h2';
			$commonCond .= ' WHERE a.' . $db->nameQuote('actor_id') . '= h2.' . $db->nameQuote('actor_id') ;
			$commonCond .= ' AND h2.' . $db->nameQuote('user_id') . '=' . $db->Quote($userId) ;
			$commonCond .= ' AND h2.' . $db->nameQuote('uid') . ' = ' . $db->Quote('0');
			$commonCond .= ' AND h2.' . $db->nameQuote('context') . ' is null';
			$commonCond .= ')';

			if ($config->get('users.blocking.enabled')) {
				//based on user who blocked the viewer
				$commonCond	.= ' AND NOT EXISTS (';
				$commonCond	.= ' select bs.' . $db->nameQuote('user_id') . ' from ' . $db->nameQuote('#__social_block_users') . ' as bs';
				$commonCond .= ' where (a.' . $db->nameQuote('actor_id') . ' = bs.' . $db->nameQuote('user_id');
				$commonCond .= ' and bs.' . $db->nameQuote('target_id') . ' = ' . $db->Quote($userId);

				// exclude cluster's admin user #4448
				$commonCond .= ' and bs.' . $db->nameQuote('target_id') . ' NOT IN (';
				$commonCond .= '    select clsnd.`uid` from `#__social_clusters_nodes` as clsnd where a.`cluster_id` = clsnd.`cluster_id` and clsnd.`admin` = 1)';

				$commonCond .= ') OR (';
				$commonCond .= ' a.' . $db->nameQuote('actor_id') . ' = bs.' . $db->nameQuote('target_id');
				$commonCond .= ' and bs.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId) . ')';
				$commonCond .= ')';
			}

		} else {
			// public stream
			$commonCond .= ' AND a.`access` = ' . $db->Quote('0');

		}

		$query = '';

		// following
		$query .= 'select a.id, ' . $db->Quote('following') . ' as `type` from `#__social_stream` as a';
		$query .= ' inner join `#__social_subscriptions` as s';
		$query .= ' on a.`actor_id` = s.`uid`';
		$query .= '		AND s.`type` = ' . $db->Quote(SOCIAL_TYPE_USER . '.' . SOCIAL_SUBSCRIPTION_TYPE_USER);
		$query .= '		AND s.`user_id` = ' . $db->Quote($userId);
		$query .= $commonCond;


		$query .= ' union ';

		// me
		$queryMe = '';
		$queryMe .= 'select a.id, ' . $db->Quote('me') . ' as `type` from `#__social_stream` as a';
		$view = $this->input->get('view', '', 'string');

		if ($source == 'dashboard' && $config->get('friends.enabled')) {
			$queryMe	.= ' LEFT JOIN `#__social_friends` AS f1 ON a.`actor_id` = f1.`target_id` and f1.`actor_id` = ' . $db->Quote($userId) . ' and f1.`state` = 1';
			$queryMe	.= ' LEFT JOIN `#__social_friends` AS f2 ON a.`actor_id` = f2.`actor_id` and f2.`target_id` = ' . $db->Quote($userId) . ' and f2.`state` = 1';
		}
		$queryMe .= $commonCond;

		// start bracket
		$tmp	= 'AND (';

		// my items.
		$tmp	.= ' a.`actor_id` = ' . $db->Quote($userId);
		$tmp 	.= ' OR (a.`target_id` = ' . $db->Quote($userId) . ' and a.`context_type` IN (' . $db->Quote('story') . ', ' . $db->Quote('reviews') . '))';

		if ($source == 'dashboard' && $config->get('friends.enabled')) {
			// my friends items.
			$tmp 	.= ' OR f1.`actor_id` = ' . $db->Quote($userId);
			$tmp 	.= ' OR f2.`target_id` = ' . $db->Quote($userId);
		}

		// my tagged items.
		$tmp .= ' OR exists (select st.`stream_id` from `#__social_stream_tags` as st' ;
		$tmp .= '                 where st.`stream_id` = a.`id`';
		$tmp .= ' 					and st.`uid` = ' . $db->Quote($userId);
		$tmp .= ' 					and st.`utype` = ' . $db->Quote(SOCIAL_STREAM_TAGGING_TYPE_USER) . ')';

		// end bracket
		$tmp 	.= ')';
		$queryMe .= $tmp;

		$query .= $queryMe;

		if ($source == 'dashboard') {
			$query .= ' union ';

			$queryE = '';
			$queryE = 'select xE.* from (';

			//public stream
			$queryE .= 'select a.id, ' . $db->Quote('everyone') . ' as `type` from `#__social_stream` as a';
			$queryE .= $commonCond;

			$queryE .= ') as xE';

			$query .= $queryE;
		}


		$query .= ' union ';

		//list
		$query .= '';
		$query .= 'select a.id, CONCAT(' . $db->Quote('list-') . ', c.`id`) as `type`';
		$query .= ' from `#__social_stream` as a';
		$query .= ' 	inner join `#__social_lists_maps` as b on a.`actor_id` = b.`target_id` and b.`target_type` = ' . $db->Quote(SOCIAL_TYPE_USER);
		$query .= ' 	inner join `#__social_lists` as c on b.`list_id` = c.`id`';
		$query .= $commonCond;
		$query .= '	and c.`user_id` = ' . $db->Quote($userId);

		$sql->raw($query);

		// echo $query;
		// echo '<br><br>';

		$db->setQuery($sql);


		$results = $db->loadObjectList();

		// echo '<pre>';print_r($result);echo '</pre>';

		$data = array();
		if ($results) {
			foreach ($results as $item) {
				$data[ $item->type ] = (isset($data[ $item->type ])) ? $data[ $item->type ] + 1 : 1;
			}
		}

		$counts = array();
		if ($data) {
			foreach($data as $key => $val) {
				$counts[] = array('type' => $key, 'cnt' => $val);
			}
		}

		return $counts;
	}


	/**
	 * Retrieves assets based on the stream id.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The id of the stream item
	 * @param	string	The context type
	 * @return
	 */
	public function getAssets($id , $type)
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select('#__social_stream_assets');
		$sql->where('stream_id' , $id);
		$sql->where('type' , $type);

		$db->setQuery($sql);

		$items 	= $db->loadObjectList();

		return $items;
	}

	/**
	 * Retrieves a list of unique stream filters
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getAppFilters($group = SOCIAL_TYPE_USER, $clusterId = false)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_stream', 'a');
		$sql->column('DISTINCT(a.context_type)');

		if (($group == SOCIAL_TYPE_GROUP || $group == SOCIAL_TYPE_EVENT || $group == SOCIAL_TYPE_PAGE) && $clusterId) {
			$sql->where('cluster_id', $clusterId);
		}

		$db->setQuery($sql);

		$rows = $db->loadColumn();

		if (!$rows) {
			return;
		}

		$filters = array();

		foreach ($rows as $row) {

			$app = ES::table('App');
			$options = array('type' => SOCIAL_APPS_TYPE_APPS, 'element' => $row, 'group' => $group);
			$exists = $app->load($options);

			if ($app->group != $group) {
				continue;
			}

			if (!$app->state) {
				continue;
			}


			// Get the app object
			$obj = $app->getAppClass();

			if (!$obj) {
				continue;
			}

			if (!$obj->hasStreamFilter()) {
				continue;
			}

			$filter = new stdClass();

			$filter->id = $app->id;

			// We should use pre-defined or user-defined app title instead of a default language string since we allow user to change the title in the backend
			// JText it as well for users who want to use language strings for multilang sites
			$filter->title = JText::_('COM_EASYSOCIAL_STREAM_APP_FILTER_' . strtoupper($row));
			$filter->image = '';
			$filter->icon = '';
			$filter->favicon = '';
			$filter->alias = strtolower($row);

			// Since 1.4, we do not want to display the icons any longer.
			$filter->icon = 'icon-es-app';

			// if ($exists) {
			// 	// Try to get a favicon
			// 	$filter->favicon = $app->getFavIcon();

			// 	if (!$filter->favicon) {
			// 		$filter->image = $app->getIcon();
			// 	}
			// }
			// else
			// {

			// }

			$filters[] = $filter;
		}

		return $filters;
	}

	/**
	 * Retrieves a list of stream filters from the site.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @param 	int
	 * @param 	string
	 * @return	Array 	An array of SocialTableStreamFilter object.
	 */
	public function getFilters($uId, $uType = 'user')
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select('#__social_stream_filter');
		$sql->where('utype' , $uType);

		if ($uType != 'user') {
			$sql->where('uid' , $uId);
		} else {
			$sql->where('(');
			$sql->where('uid', $uId, '=', 'OR');
			$sql->where('global', 1, '=', 'OR');
			$sql->where(')');
		}

		// dump($sql->debug());

		$db->setQuery($sql);
		$items 	= $db->loadObjectList();

		if(!$items)
		{
			return $items;
		}

		$filters 	= array();

		foreach($items as $item)
		{
			$filter	= ES::table('StreamFilter');
			$filter->bind($item);

			$filters[] = $filter;
		}

		return $filters;
	}

	/**
	 * Method to change the stream created date
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function updateCreated($streamId, $date = null, $action = '')
	{
		$db = ES::db();
		$sql = $db->sql();

		if (is_null($date)) {
			$date = ES::date()->toMySQL();
		}

		$query = 'UPDATE `#__social_stream` set';
		$query .= ' `created` = ' . $db->Quote($date);

		// We need to update the modified as well to bump the stream
		$query .= ', `modified` = ' . $db->Quote($date);
		$query .= ' WHERE `id` = ' . $db->Quote($streamId);

		$sql->raw($query);
		$db->setQuery($sql);

		$db->query();

		return true;
	}

	/**
	 * Update stream.modified date.
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @param 	int
	 * @return	boolean
	 *
	 * the context can be 'stream' and when the context is stream, the uid is the stream.id
	 * the context can be 'activity' and when the context is activity, the uid is the stream_item.id
	 * we need to work accordingly based on the context passed in.
	 */

	public function updateModified($streamId, $user_id = '', $user_action = '')
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$now   = ES::date()->toMySQL();

		$updateQuery = '';

		$updateQuery = 'update `#__social_stream` set `modified` = ' . $db->Quote($now);

		if ($user_id && $user_action) {
			$updateQuery .= ', `last_userid` = ' . $db->Quote($user_id);
			$updateQuery .= ', `last_action` = ' . $db->Quote($user_action);
			$updateQuery .= ', `last_action_date` = ' . $db->Quote($now);
		}

		$updateQuery .= ' where `id` = ' . $db->Quote($streamId);

		$sql->raw($updateQuery);
		$db->setQuery($sql);

		$db->query();

		return true;
	}

	public function revertLastAction($streamId, $user_id = '', $user_action = '')
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		if ($user_id && $user_action) {
			// let check first if the last action of this stream is belong to the same person and same action or not.
			$query = 'select last_userid, last_action from `#__social_stream` where id = ' . $db->Quote($streamId);
			$sql->raw($query);

			$db->setQuery($sql);
			$item = $db->loadObject();

			if ($item->last_userid == $user_id && $item->last_action == $user_action) {
				// okay, we are reverting the same user action. now we need to get the 2nd last action and user id.
				$prev_last_action = '';
				$prev_last_userid = 0;
				$prev_last_action_date = '';

				$query = "select * from (";
				$query .= "	(select 'like' as `action`, `created_by`, `created` from `#__social_likes` where `stream_id` = " . $db->Quote($streamId) . " order by `id` desc limit 1)";
				$query .= "	union all";
				$query .= "	(select 'comment' as `action`, `created_by`, `created` from `#__social_comments` where `stream_id` = " . $db->Quote($streamId) . " order by `id` desc limit 1)";
				$query .= ") as x order by x.`created` desc limit 1";

				$sql->clear();
				$sql->raw($query);

				$db->setQuery($sql);
				$result = $db->loadObject();

				if ($result) {
					$prev_last_action = $result->action;
					$prev_last_userid = (int) $result->created_by;
					$prev_last_action_date = $result->created;
				}

				// now we have the data and we are ready to update the stream last action.
				$updateQuery = 'update `#__social_stream` set';
				$updateQuery .= ' `last_userid` = ' . $db->Quote($prev_last_userid);
				$updateQuery .= ', `last_action` = ' . $db->Quote($prev_last_action);

				if ($prev_last_action_date) {
					$updateQuery .= ', `last_action_date` = ' . $db->Quote($prev_last_action_date);
				}

				$updateQuery .= ' where `id` = ' . $db->Quote($streamId);

				$sql->clear();
				$sql->raw($updateQuery);
				$db->setQuery($sql);

				$db->query();
			}
		}

		return true;
	}

	/**
	 * This determines if the provided context_id is aggregated with other items
	 *
	 * @since	1.2
	 * @access	public
	 * @param	int 	The context_id
	 * @return	bool	True if it is aggregated with other items
	 */
	public function isAggregated($contextId, $contextType)
	{
		static $_cache = array();

		$key = $contextId . $contextType;

		if (! isset($_cache[$key])) {
			$db 	= ES::db();
			$sql 	= $db->sql();

			$sql->select('#__social_stream_item');
			$sql->column('uid');
			$sql->where('context_id', $contextId);
			$sql->where('context_type', $contextType);

			$db->setQuery($sql);

			$uid 	= $db->loadResult();

			if (!$uid){
				$_cache[$key] = false;
				return false;
			}

			$sql->clear();
			$sql->select('#__social_stream_item');
			$sql->column('COUNT(1)');
			$sql->where('uid', $uid);
			$db->setQuery($sql);

			$total 	= $db->loadResult();

			// If there's more than 1 item, we know it is being aggregated
			if ($total > 1) {
				$_cache[$key] = true;
			} else {
				$_cache[$key] = false;
			}
		}

		return $_cache[$key];
	}

	/**
	 * Determine if the provided contextId and type is part of the aggregated stream
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getAggregatedStreamId($contextId, $contextType)
	{
		static $_cache = array();

		$key = $contextId . $contextType;

		if (!isset($_cache[$key])) {
			$db = ES::db();
			$sql = $db->sql();

			$sql->select('#__social_stream_item');
			$sql->column('uid');
			$sql->where('context_id', $contextId);
			$sql->where('context_type', $contextType);

			$db->setQuery($sql);

			$uid = $db->loadResult();

			if (!$uid){
				$_cache[$key] = false;
				return false;
			}

			$sql->clear();
			$sql->select('#__social_stream_item');
			$sql->column('COUNT(1)');
			$sql->where('uid', $uid);
			$db->setQuery($sql);

			$total = $db->loadResult();

			// If there's more than 1 item, we know it is being aggregated
			if ($total > 1) {
				$_cache[$key] = $uid;
			} else {
				$_cache[$key] = false;
			}
		}

		return $_cache[$key];
	}

	/**
	 * Retrieves the stream id given the appropriate item contexts
	 *
	 * @since	1.2
	 * @access	public
	 * @param	int 	The context id
	 * @param	string	The context type
	 * @return	int 	The stream id
	 */
	public function getStreamId($contextId, $contextType, $actorId = null)
	{
		static $_cache = array();

		$key = $contextId . $contextType;

		if (!isset($_cache[$key])) {
			$db = ES::db();
			$sql = $db->sql();

			$sql->select('#__social_stream_item');
			$sql->column('uid');
			$sql->where('context_id', $contextId);
			$sql->where('context_type', $contextType);

			if ($actorId) {
				$sql->where('actor_id', $actorId);
			}

			$uid = $db->loadResult();

			$_cache[$key] = $uid;
		}

		return $_cache[$key];
	}

	/**
	 * Method to retrieve all associated context items of the stream
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getContextItems($streamId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.`context_type`, a.`context_id`';
		$query .= ' from `#__social_stream_item` as a';
		$query .= ' where a.`uid` = ' . $db->Quote($streamId);

		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Method to retrieve single context item that are associated with the stream
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getContextItem($streamId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select a.`context_type`, a.`context_id`';
		$query .= ' from `#__social_stream_item` as a';
		$query .= ' where a.`uid` = ' . $db->Quote($streamId);
		$query .= ' order by a.`id` limit 1';

		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadObject();

		return $result;
	}

	/**
	 * update stream privacy access
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function updateAccess($streamId, $privacy, $custom = null, $field = null)
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		if (is_null($field) || (!is_array($field) && !is_object($field))) {
			$field = array();
		}

		$totalField = count($field);

		$query = 'update `#__social_stream` set `access` = ' . $db->Quote($privacy);
		$query .= ', `custom_access` = ' . $db->Quote($custom);
		$query .= ', `field_access` = ' . $db->Quote($totalField);
		$query .= ' where `id` = ' . $db->Quote($streamId);

		$sql->raw($query);
		$db->setQuery($sql);

		$state = $db->query();

		if ($state && $field) {
			// $query = "delete from `#__social_stream_privacy_field` where `stream_id` = " . $db->Quote($streamId);
			// $db->setQuery($query);
			// $db->query();


			$query = "delete from `#__social_stream_privacy_field` where `stream_id` = " . $db->Quote($streamId);
			$db->setQuery($query);
			$db->query();

			$query = "insert into `#__social_stream_privacy_field` (`stream_id`, `field_key`, `field_value`) values ";

			$insertValues = array();
			foreach ($field as $item) {
				$fieldData = explode('|', $item);

				$fieldElement = $fieldData[0];
				$fieldKey = $fieldData[1];
				$fieldValues = isset($fieldData[2]) && $fieldData[2] ? explode(',', $fieldData[2]) : array();

				if ($fieldValues) {
					foreach ($fieldValues as $fieldValue) {
						$insertValues[] = "(" . $db->Quote($streamId) . ',' . $db->Quote($fieldKey) . ',' . $db->Quote($fieldValue) . ')';
					}
				}
			}

			if ($insertValues) {
				$insertQuery = implode(',', $insertValues);
				$query .= $insertQuery;

				$db->setQuery($query);
				$db->query();
			}

		}

		return $state;
	}

	/**
	 * delete stream and stream items based on stream id
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function deleteStreamItem($streamId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = array();
		$query[] = 'DELETE a, b FROM ' . $db->qn('#__social_stream') . ' AS a';
		$query[] = 'INNER JOIN ' . $db->qn('#__social_stream_item') . ' AS b';
		$query[] = 'ON a.' . $db->qn('id') . '=b.' . $db->qn('uid');
		$query[] = 'WHERE a.' . $db->qn('id') . '=' . $db->Quote($streamId);

		$query = implode(' ', $query);
		$sql->raw($query);

		$db->setQuery($sql);

		$state = $db->query();

		// We also want to remove reactions that are associated with the activity stream
		$query = array();
		$query[] = 'DELETE FROM ' . $db->qn('#__social_likes');
		$query[] = 'WHERE ' . $db->qn('stream_id') . '=' . $db->Quote($streamId);
		$db->setQuery($query);
		$db->Query();

		return $state;
	}

	/**
	 * trash stream and stream items based on stream id
	 *
	 * @since	1.3
	 * @access	public
	 * @param	array - stream ids
	 * @return  boolean
	 */
	public function trashStreamItem($streamIds)
	{
		$db = ES::db();
		$sql = $db->sql();

		if (empty($streamIds)) {
			return false;
		}

		$ids = implode(',', $streamIds);

		$query = "update `#__social_stream` as a inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
		$query .= " set a.`state` = " . SOCIAL_STREAM_STATE_TRASHED . ",";
		$query .= "		b.`state` = " . SOCIAL_STREAM_STATE_TRASHED;
		$query .= " where a.`id` IN ($ids)";

		$sql->raw($query);
		$db->setQuery($sql);
		$state = $db->query();

		return $state;
	}


	public function restoreStreamItem($streamIds, $type = 'trash')
	{
		$db = ES::db();
		$sql = $db->sql();

		if (empty($streamIds)) {
			return false;
		}

		$ids = implode(',', $streamIds);

		if ($type == 'trash') {
			$query = "update `#__social_stream` as a inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
			$query .= " set a.`state` = " . SOCIAL_STREAM_STATE_PUBLISHED . ",";
			$query .= "		b.`state` = " . SOCIAL_STREAM_STATE_PUBLISHED;
			$query .= " where a.`id` IN ($ids)";

			$sql->raw($query);
			$db->setQuery($sql);

			$state = $db->query();
		} else {
			// need to 'migrate' from history table

			$state = true;
		}

		return $state;
	}


	/**
	 * archive stream items into history table.
	 *
	 * @since	1.3
	 * @access	public
	 * @param	array - stream ids
	 * @return  boolean
	 */
	public function archive($streamIds)
	{
		$db = ES::db();
		$sql = $db->sql();

		if (empty($streamIds)) {
			return false;
		}

		$ids = implode(',', $streamIds);

		// lets archive stream_item first.
		$query = "insert into `#__social_stream_item_history` select a.* from `#__social_stream_item` as a where a.`uid` IN ($ids) ON DUPLICATE KEY UPDATE id = a.`id`";

		$sql->raw($query);
		$db->setQuery($sql);
		$state = $db->query();

		if ($state) {
			// now we archive stream table.
			//
			$columns = "`id`,`actor_id`,`alias`,`actor_type`,`post_as`,`created`,`modified`,`edited`,`title`,`content`,`context_type`,`verb`,`stream_type`,`sitewide`,`target_id`,";
			$columns .= "`location_id`,`mood_id`,`with`,`ispublic`,`cluster_id`,`cluster_type`,`cluster_access`,`params`,`state`,`privacy_id`,`access`,`custom_access`,`field_access`,`last_action`,";
			$columns .= "`last_userid`,`last_action_date`, `sticky_id`,`datafix`,`anywhere_id`";


			$query = "insert into `#__social_stream_history` ($columns) select $columns from `#__social_stream`";
			$query .= " where `#__social_stream`.`id` IN ($ids) ON DUPLICATE KEY UPDATE id = `#__social_stream`.`id`";

			$sql->clear();
			$sql->raw($query);
			$db->setQuery($sql);
			$state = $db->query();

			if ($state) {
				// okay everything is now archived. lets remove the data.

				$query = "delete a, b from `#__social_stream` as a";
				$query .= " inner join `#__social_stream_item` as b on a.`id` = b.`uid`";
				$query .= "where a.`id` IN ($ids)";

				$sql->clear();
				$sql->raw($query);
				$db->setQuery($sql);

				$state = $db->query();
			}

		}

		return $state;
	}


	/**
	 * restore archived items into stream table.
	 *
	 * @since	1.3
	 * @access	public
	 * @param	array - stream ids
	 * @return  boolean
	 */
	public function restoreArchivedItem($streamIds)
	{
		$db = ES::db();
		$sql = $db->sql();

		if (empty($streamIds)) {
			return false;
		}

		$ids = implode(',', $streamIds);

		// lets archive stream_item first.
		$query = "insert into `#__social_stream_item` select a.* from `#__social_stream_item_history` as a where a.`uid` IN ($ids) ON DUPLICATE KEY UPDATE id = a.`id`";

		$sql->raw($query);
		$db->setQuery($sql);
		$state = $db->query();

		if ($state) {
			// now we archive stream table.
			$columns = "`id`,`actor_id`,`alias`,`actor_type`,`post_as`,`created`,`modified`,`edited`,`title`,`content`,`context_type`,`verb`,`stream_type`,`sitewide`,`target_id`,";
			$columns .= "`location_id`,`mood_id`,`with`,`ispublic`,`cluster_id`,`cluster_type`,`cluster_access`,`params`,`state`,`privacy_id`,`access`,`custom_access`,`last_action`,";
			$columns .= "`last_userid`,`last_action_date`, `sticky_id`,`datafix`, `anywhere_id`";

			$query = "insert into `#__social_stream` ($columns) select $columns from `#__social_stream_history` as a where a.`id` IN ($ids) ON DUPLICATE KEY UPDATE id = a.`id`";

			$sql->clear();
			$sql->raw($query);
			$db->setQuery($sql);
			$state = $db->query();

			if ($state) {

				//now we update the stream.status to 'restored from archive'
				$query = "update `#__social_stream` as a set a.`state` = " . SOCIAL_STREAM_STATE_RESTORED;
				$query .= " where a.`id` IN ($ids)";

				$sql->clear();
				$sql->raw($query);
				$db->setQuery($sql);
				$state = $db->query();

				if ($state) {
					// okay everything is now restored. lets remove the data from history table.
					$query = "delete a, b from `#__social_stream_history` as a";
					$query .= " inner join `#__social_stream_item_history` as b on a.`id` = b.`uid`";
					$query .= "where a.`id` IN ($ids)";

					$sql->clear();
					$sql->raw($query);
					$db->setQuery($sql);
					$state = $db->query();
				}

			}

		}

		return $state;
	}

	/**
	 * restore archived items into stream table.
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function getItemsToArchive($months, $limit = 50)
	{
		$config = ES::config();
		$db = ES::db();
		$sql = $db->sql();

		$column = $this->streamPaginationSort;

		$now    = ES::date();

		// clean the registration temp data for records that exceeded 1 hour.
		$query = "select `id` from `#__social_stream`";
		$query .= " where `state` = 1";
		$query .= " and date_add(`$column` , INTERVAL $months MONTH) <= " . $db->Quote($now->toMySQL());
		$query .= " order by `modified` asc";
		$query .= "  limit " . $limit;

		$sql->raw($query);

		$db->setQuery($sql);

		$results = $db->loadColumn();

		return $results;
	}

	/**
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getStreamItemsCount($streamId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "select count(1) from `#__social_stream_item` where `uid` = '$streamId'";
		$sql->raw($query);

		$db->setQuery($sql);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isStreamAuthor($userId, $streamId)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "SELECT COUNT(1) FROM `#__social_stream` ";
		$query .= "WHERE `actor_id` = " . $db->Quote($userId);
		$query .= " AND `id` = " . $db->Quote($streamId);

		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Retrieve stream data used in GDPR data download request.
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getActivityStreamGDPR($userId, $excludeIds = array(), $limit = 20)
	{
		$db = ES::db();

		$sortDate = $this->streamPaginationSort;

		$query  = 'select a.' . $db->nameQuote('actor_id') . ', a.' . $db->nameQuote('title') . ', a.' . $db->nameQuote('content') . ', a.' . $db->nameQuote('stream_type') ;
		$query .= ', a.' . $db->nameQuote('location_id') . ', a.' . $db->nameQuote('params');
		$query .= ', a.' . $db->nameQuote('edited');
		$query .= ', a.' . $db->nameQuote('cluster_id') . ', a.' . $db->nameQuote('cluster_type') . ', a.' . $db->nameQuote('cluster_access');
		$query .= ', a.' . $db->nameQuote('privacy_id') . ', a.' . $db->nameQuote('access') . ', a.' . $db->nameQuote('custom_access');
		$query .= ', a.' . $db->nameQuote('modified') . ', b.' . $db->nameQuote('context_type') . ', b.' . $db->nameQuote('context_id') . ', b.' . $db->nameQuote('target_id');

		$query .= ', b.' . $db->nameQuote('created') . ', b.' . $db->nameQuote('id') . ', b.' . $db->nameQuote('uid') . ', b.' . $db->nameQuote('verb');
		$query .= ', b.' . $db->nameQuote('state');
		$query .= ', 0 as ' . $db->nameQuote('privacy');

		$query .= ', null as loc_id, null as loc_uid, null as loc_type, null as loc_user_id, null as loc_created, null as loc_short_address';
		$query .= ', null as loc_address, null as loc_longitude, null as loc_latitude, null as loc_params';
		$query .= ', null as md_id, null as md_namespace, null as md_namespace_uid, null as md_icon, null as md_verb, null as md_subject, null as md_custom';
		$query .= ', null as md_text, null as md_user_id, null as md_created, 0 as bookmarked, 0 as sticky, 0 as last_userid, 0 as last_action, ' . $db->Quote('') . ' as last_action_date';

		$query .= ',FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.' . $db->nameQuote('modified') . ')) / 60) AS ' . $db->nameQuote('min');
		$query .= ',FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.' . $db->nameQuote('modified') . ')) / 60 / 60) AS ' . $db->nameQuote('hour');
		$query .= ',FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.' . $db->nameQuote('modified') . ')) / 60 / 60 / 24) AS ' . $db->nameQuote('day');

		$query  .= ' from ' . $db->nameQuote('#__social_stream') . ' as a';
		$query  .= '   inner join ' . $db->nameQuote('#__social_stream_item') . ' as b ON a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('uid');


		$query .= " WHERE b.`state` IN(" . $db->Quote(SOCIAL_STREAM_STATE_PUBLISHED) . "," . $db->Quote(SOCIAL_STREAM_STATE_RESTORED) . ")";
		$query .= " AND a.`actor_id` = " . $db->Quote($userId);

		if ($excludeIds) {
			$query .= " AND b.`id` NOT IN (" . implode(',', $excludeIds) . ") ";
		}

		// exclude repost and tasks
		$excludeContexts = array('shares', 'tasks');
		$excludesContextList = '';
		foreach ($excludeContexts as $exContext) {
			$excludesContextList .= ($excludesContextList) ? ',' . $db->Quote($exContext) : $db->Quote($exContext);
		}
		$query .= ' and a.`context_type` NOT IN (' . $excludesContextList . ')';


		$query .= " ORDER BY a." . $sortDate . ' DESC';
		$query .= " LIMIT " . $limit;

		$db->setQuery($query);
		$results = $db->loadObjectList();

		// register the resultset.
		$this->setBatchActivityItems($results);

		return $results;
	}

	public function getUidItem($contextId, $contextType, $verb)
	{
		$db = ES::db();

		$query = "select * from `#__social_stream_item`";
		$query .= " where `context_type` = " . $db->Quote($contextType);
		$query .= " and `verb` = " . $db->Quote($verb);
		$query .= " and `context_id` = " . $db->Quote($contextId);
		$query .= " LIMIT 1";

		$db->setQuery($query);
		$item = $db->loadObject();

		return $item;
	}

	/**
	 * Update assets data from the link stream.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function updateAssetsData($streamId, $data)
	{
		$db = ES::db();

		$query = "UPDATE `#__social_stream_assets`";
		$query .= " SET `data` = " . $db->Quote($data);
		$query .= " WHERE `stream_id` = " . $db->Quote($streamId);
		$query .= " AND `type` = " . $db->Quote('links');

		$db->setQuery($query);
		$db->query();

		return true;
	}
}
