<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/model');

class EasySocialModelActivities extends EasySocialModel
{
	public function __construct()
	{
		parent::__construct('activities');
		$this->initStates();
	}

	/**
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function initStates()
	{
		$config = ES::config();
		$mainframe = JFactory::getApplication();

		parent::initStates();

		// Default limit
		$limit = ES::getLimit('activitylog.pagination');

		$this->setState('limit', $limit);
	}

	/**
	 * hide activities item given the context type and context id.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function toggle($id, $userId)
	{
		$db = ES::db();

		$itemTbl = ES::table('StreamItem');
		if (! $itemTbl->load($id)) {
			return false;
		}

		$uid = $itemTbl->uid;

		$query = 'select count(1) from ' . $db->nameQuote('#__social_stream_hide');
		$query .= ' where ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);
		$query .= ' and ' . $db->nameQuote('uid') . ' = ' . $db->Quote($id); // for activity log we use stream item's id as the uid
		$query .= ' and ' . $db->nameQuote('type') . ' = ' . $db->Quote(SOCIAL_STREAM_HIDE_TYPE_ACTIVITY);

		$db->setQuery($query);
		$result = $db->loadResult();

		$action = '';
		if ($result) {
			// Record found! This mean we need to unhide the item.
			$delSQL = 'delete from ' . $db->nameQuote('#__social_stream_hide');
			$delSQL .= ' where ' . $db->nameQuote('uid') . ' = ' . $db->Quote($id);
			$delSQL .= ' and ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);
			$delSQL .= ' and ' . $db->nameQuote('type') . ' = ' . $db->Quote(SOCIAL_STREAM_HIDE_TYPE_ACTIVITY);

			$db->setQuery($delSQL);
			if ($db->query()) {
				// since we are doing the unhide, we need to unhide the stream as well, if there is any.
				$delSQL = 'delete from  ' . $db->nameQuote('#__social_stream_hide');
				$delSQL .= ' where ' . $db->nameQuote('uid') . ' = ' . $db->Quote($uid);
				$delSQL .= ' and ' . $db->nameQuote('user_id') . ' = ' . $db->Quote($userId);
				$delSQL .= ' and ' . $db->nameQuote('type') . ' = ' . $db->Quote(SOCIAL_STREAM_HIDE_TYPE_STREAM);

				$db->setQuery($delSQL);
				$db->query();
			}

		} else {
			// record not found. We need to hide this activity!
			$tbl = ES::table('StreamHide');
			$tbl->uid = $id;
			$tbl->user_id = $userId;
			$tbl->type = SOCIAL_STREAM_HIDE_TYPE_ACTIVITY;
			$tbl->store();

			// since we are doing hiding, we need to check if this activity has one item or more. if only one, then we need to hide the stream as well.
			$query = 'select count(1) from ' . $db->nameQuote('#__social_stream_item');
			$query .= ' where ' . $db->nameQuote('uid') . ' = ' . $db->Quote($uid);
			$query .= ' and ' . $db->nameQuote('id') . ' != ' . $db->Quote($id);

			$db->setQuery($query);
			$result = $db->loadResult();

			if (! $result) {
				$tbl = ES::table('StreamHide');
				$tbl->uid = $uid;
				$tbl->user_id = $userId;
				$tbl->type = SOCIAL_STREAM_HIDE_TYPE_STREAM;
				$tbl->store();
			}
		}

		return true;
	}

	/**
	 * Deletes activity item.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function delete($id)
	{
		$itemTbl = ES::table('StreamItem');
		if (! $itemTbl->load($id)) {
			return false;
		}

		$db  = ES::db();
		$uid = $itemTbl->uid;

		if ($itemTbl->delete()) {
			// now we need to check if this stream item has more items or not. if not, then we need to delete the stream item as well.
			$query = 'select count(1) from  ' . $db->nameQuote('#__social_stream_item');
			$query .= ' where ' . $db->nameQuote('uid') . ' = ' . $db->Quote($uid);

			$db->setQuery($query);
			$result = $db->loadResult();

			if (! $result) {
				// empty result. this mean the stream only has one activity item. lets remove the stream as well.
				$delSQL = 'delete from ' . $db->nameQuote('#__social_stream');
				$delSQL .= ' where ' . $db->nameQuote('id') . ' = ' . $db->Quote($uid);

				$db->setQuery($delSQL);
				$db->query();

				//now we remove data from hide table if there is any.
				// activity stream.
				$delSQL = 'delete from ' . $db->nameQuote('#__social_stream_hide');
				$delSQL .= ' where ' . $db->nameQuote('uid') . ' = ' . $db->Quote($uid);
				$delSQL .= ' and ' . $db->nameQuote('type') . ' = ' . $db->Quote(SOCIAL_STREAM_HIDE_TYPE_STREAM);

				$db->setQuery($delSQL);
				$db->query();
			}

			//now we remove item from hide table if there is any
			// activity item.
			$delSQL = 'delete from ' . $db->nameQuote('#__social_stream_hide');
			$delSQL .= ' where ' . $db->nameQuote('uid') . ' = ' . $db->Quote($id);
			$delSQL .= ' and ' . $db->nameQuote('type') . ' = ' . $db->Quote(SOCIAL_STREAM_HIDE_TYPE_ACTIVITY);

			$db->setQuery($delSQL);
			$db->query();
		}

		return true;
	}


	/**
	 * Deletes stream items given the context type and context id.
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getItems($options)
	{
		$db = ES::db();
		$sql = $db->sql();

		$uId = $options['uId'];
		$uType = isset($options['uType']) ? $options['uType'] : SOCIAL_TYPE_USER;
		$context = isset($options['context']) ? $options['context'] : SOCIAL_STREAM_CONTEXT_TYPE_ALL;
		$filter = isset($options['filter']) ? $options['filter'] : 'all';
		$max = isset($options['max']) ? $options['max'] : '';
		$limitstart = isset($options['limitstart']) ? $options['limitstart'] : '';


		$CountHeader = 'select count(1)';

		$header  = 'select a.' . $db->nameQuote('actor_id') . ', a.' . $db->nameQuote('title') . ', a.' . $db->nameQuote('content') . ', a.' . $db->nameQuote('stream_type') ;
		$header .= ', a.' . $db->nameQuote('location_id') . ', a.' . $db->nameQuote('params');
		$header .= ', a.' . $db->nameQuote('edited');
		$header .= ', a.' . $db->nameQuote('cluster_id') . ', a.' . $db->nameQuote('cluster_type') . ', a.' . $db->nameQuote('cluster_access');
		$header .= ', a.' . $db->nameQuote('privacy_id') . ', a.' . $db->nameQuote('access') . ', a.' . $db->nameQuote('custom_access');
		$header .= ', a.' . $db->nameQuote('modified') . ', b.' . $db->nameQuote('context_type') . ', b.' . $db->nameQuote('context_id') . ', b.' . $db->nameQuote('target_id');

		$header .= ', b.' . $db->nameQuote('created') . ', b.' . $db->nameQuote('id') . ', b.' . $db->nameQuote('uid') . ', b.' . $db->nameQuote('verb');
		$header .= ', b.' . $db->nameQuote('state');
		$header .= ', p.' . $db->nameQuote('value') . ' as ' . $db->nameQuote('privacy');

		$header .= ', l.id as loc_id, l.uid as loc_uid, l.type as loc_type, l.user_id as loc_user_id, l.created as loc_created, l.short_address as loc_short_address';
		$header .= ',l.address as loc_address, l.longitude as loc_longitude, l.latitude as loc_latitude, l.params as loc_params';
		$header .= ',md.id as md_id, md.namespace as md_namespace,md.namespace_uid as md_namespace_uid, md.icon as md_icon, md.verb as md_verb, md.subject as md_subject, md.custom as md_custom';
		$header .= ',md.text as md_text, md.user_id as md_user_id, md.created as md_created, sbm.id as bookmarked, 0 as sticky, 0 as last_userid, 0 as last_action, ' . $db->Quote('') . ' as last_action_date';

		$header .= ',FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.' . $db->nameQuote('modified') . ')) / 60) AS ' . $db->nameQuote('min');
		$header .= ',FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.' . $db->nameQuote('modified') . ')) / 60 / 60) AS ' . $db->nameQuote('hour');
		$header .= ',FLOOR((UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(a.' . $db->nameQuote('modified') . ')) / 60 / 60 / 24) AS ' . $db->nameQuote('day');

		$query  = ' from ' . $db->nameQuote('#__social_stream') . ' as a';
		$query  .= '   left join ' . $db->nameQuote('#__social_stream_item') . ' as b ON a.' . $db->nameQuote('id') . ' = b.' . $db->nameQuote('uid');

		//privacy
		$query  .= '   left join ' . $db->nameQuote('#__social_privacy_items') . ' as p ON b.' . $db->nameQuote('id') . ' = p.' . $db->nameQuote('uid');
		$query  .= ' and p.' . $db->nameQuote('type') . ' = ' . $db->Quote('activity');

		// joining location table
		$query .= 'LEFT JOIN ' . $db->nameQuote('#__social_locations') . ' AS l ON a.' . $db->nameQuote('location_id') . ' = l.' . $db->nameQuote('id');

		// joining mood table
		$query .= 'LEFT JOIN ' . $db->nameQuote('#__social_moods') . ' AS md ON a.' . $db->nameQuote('mood_id') . ' = md.' . $db->nameQuote('id');

		// joining bookmark table
		$query .= 'LEFT JOIN ' . $db->nameQuote('#__social_bookmarks') . ' AS sbm ON a.' . $db->nameQuote('id') . ' = sbm.' . $db->nameQuote('uid') . ' and sbm.' . $db->nameQuote('type') . ' = ' . $db->Quote('stream');
		$query .= ' and sbm.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($uId);


		$query  .= ' where b.' . $db->nameQuote('actor_type') . ' = ' . $db->Quote($uType);

		// we do not what activities from cluster
		$query .= ' and (a.' . $db->nameQuote('cluster_id') . '=' . $db->Quote('0') . ' OR ' . $db->nameQuote('cluster_access') . ' = ' . $db->Quote('1') . ')';


		if ($filter != 'hidden') {
			$query  .= ' and b.' . $db->nameQuote('actor_id') . ' = ' . $db->Quote($uId);
		}

		if ($context != SOCIAL_STREAM_CONTEXT_TYPE_ALL) {
			$query  .= ' and b.' . $db->nameQuote('context_type') . ' = ' . $db->Quote($context);
		}


		//filter out unpublished apps.
		$viewer = $uId;
		$excludeUserApps = $this->getUnAccessilbleUserApps($viewer, SOCIAL_APPS_GROUP_USER);
		$excludeGroupApps = $this->getUnAccessilbleUserApps($viewer, SOCIAL_APPS_GROUP_GROUP);
		$excludeEventApps = $this->getUnAccessilbleUserApps($viewer, SOCIAL_APPS_GROUP_EVENT);
		$excludePageApps = $this->getUnAccessilbleUserApps($viewer, SOCIAL_APPS_GROUP_PAGE);

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

		if ($cond) {
			$query .= " AND ";
			$query .= implode(" AND ", $cond);
		}

		if ($filter == 'hidden') {
			$query .= ' and exists (';
			$query .= '     select sh.' . $db->nameQuote('id') . ' from ' . $db->nameQuote('#__social_stream_hide') . ' AS sh';
			$query .= '     where sh.' . $db->nameQuote('uid') . ' = b.' . $db->nameQuote('id');
			$query .= '     and sh.' . $db->nameQuote('type') . ' = ' . $db->Quote(SOCIAL_STREAM_HIDE_TYPE_ACTIVITY);
			$query .= '     and sh.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($uId);
			$query .= ')';
		} else {
			$query .= ' and not exists (';
			$query .= '     select sh.' . $db->nameQuote('id') . ' from ' . $db->nameQuote('#__social_stream_hide') . ' AS sh';
			$query .= '     where sh.' . $db->nameQuote('uid') . ' = b.' . $db->nameQuote('id');
			$query .= '     and sh.' . $db->nameQuote('type') . ' = ' . $db->Quote(SOCIAL_STREAM_HIDE_TYPE_ACTIVITY);
			$query .= '     and sh.' . $db->nameQuote('user_id') . ' = ' . $db->Quote($uId);
			$query .= ')';
		}


		$countSQL = $CountHeader . $query;

		// echo $countSQL;exit;

		$query  .= ' order by b.' . $db->nameQuote('created') . ' desc';
		if ($max) {
			$query  .= ' limit ' . $max;
		}

		$mainSQL = $header . $query;

		// echo $mainSQL;
		// exit;

		if ($max) {
			$sql->raw($mainSQL);
			$db->setQuery($sql);

			$result = $db->loadObjectList();

			return $result;
		}

		if ($limitstart) {
			$this->setState('limitstart', $limitstart);
		}

		$this->setTotal($countSQL);

		$result = $this->getData($mainSQL);

		// var_dump($result);exit;

		return $result;
	 }

	/**
	 *
	 * @since   1.0
	 * @access  private
	 */
	private function getUnpublishedUserApps()
	{
		$db = ES::db();
		$sql = $db->sql();
		$apps = array();

		$query = "select `element` from `#__social_apps`";
		$query .= " where `type` = 'apps'";
		$query .= ' AND `group`=' . $db->Quote(SOCIAL_APPS_GROUP_USER);
		$query .= " and `state` = 0";

		$sql->raw($query);
		$db->setQuery($sql);

		$apps = $db->loadColumn();

		return $apps;
	}

	/**
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getPagination($reload = null)
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination)) {
			jimport('joomla.html.pagination');
			$this->pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->pagination;
	}

	/**
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * Retrieves a list of apps
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getApps()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = array();
		$query[] = 'SELECT * FROM `#__social_apps`';
		$query[] = 'WHERE `type`=' . $db->Quote(SOCIAL_APPS_TYPE_APPS);
		$query[] = 'AND `group`=' . $db->Quote(SOCIAL_APPS_GROUP_USER);
		$query[] = 'AND `state`=' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		$query[] = 'AND `element` IN(SELECT DISTINCT `context_type` FROM `#__social_stream_item`)';

		// Glue the query back
		$query = implode(' ', $query);
		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$apps = array();

		foreach ($result as $row) {
			$app = ES::table('App');
			$app->bind($row);

			// Get the app object
			$obj = $app->getAppClass();

			if (!$obj) {
				continue;
			}

			// We need to check if the app has disable the filter or not
			if (!$obj->hasStreamFilter()) {
				continue;
			}

			$apps[] = $app;
		}

		return $apps;
	}

	/**
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getNextLimit($limitstart)
	{
		$nextlimit = '';
		$total = $this->getTotal();

		if ($total) {
			$pagination = $this->getPagination();

			if ($pagination) {
				$nextlimit = ($total > $limitstart + $pagination->limit) ? $limitstart + $pagination->limit : '';
			}
		} else {
			$nextlimit = '';
		}

		return $nextlimit;
	}

	/**
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getHiddenApps($userId)
	{
		$db = ES::db();

		$sql = $db->sql();

		$sql->select('#__social_stream_hide');
		$sql->where('user_id', $userId);
		$sql->where('uid', '0');
		$sql->where('actor_id', '0');

		$db->setQuery($sql);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function getHiddenActors($userId)
	{
		$db = ES::db();

		$sql = $db->sql();

		$sql->select('#__social_stream_hide');
		$sql->where('user_id', $userId);
		$sql->where('uid', '0');
		$sql->isnull('context');

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function unhideapp($context, $id)
	{
		if (empty($id)) {
			return false;
		}

		$db = ES::db();

		$delQuery = 'delete from ' . $db->nameQuote('#__social_stream_hide') . ' where ' . $db->nameQuote('id') . ' = ' . $db->Quote($id);

		$db->setQuery($delQuery);
		$db->query();

		return true;
	}

	/**
	 *
	 * @since   1.0
	 * @access  public
	 */
	public function unhideactor($actor, $id)
	{
		if (empty($id)) {
			return false;
		}

		$db = ES::db();

		$delQuery = 'delete from ' . $db->nameQuote('#__social_stream_hide') . ' where ' . $db->nameQuote('id') . ' = ' . $db->Quote($id);

		$db->setQuery($delQuery);
		$db->query();

		return true;
	}

	/**
	 * Generate sql used in unaccessible apps.
	 *
	 * @since	2.1
	 * @access	public
	 */
	private function generateUnAccessibleAppsSQL($excludeAppsContainer, &$cond)
	{
		$db = ES::db();

		if ($excludeAppsContainer) {
			$container = [];
			$userContainer = [];
			$appOnly = [];
			$appWithVerbs = [];

			foreach ($excludeAppsContainer as $group => $excludeApps) {
				if (!$excludeApps) {
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
				$conditions = [];

				foreach ($appOnly as $group => $apps) {
					$tmpString = '';

					foreach ($apps as $app) {
						$tmpString .= ($tmpString) ? ',' . $db->Quote($app) : $db->Quote($app);
					}

					$tmpCond = ' NOT (a.' . $db->nameQuote('context_type') . ' IN (' . $tmpString . ') and';
					if ($group == SOCIAL_APPS_GROUP_USER) {
						$tmpCond .= ' a.' . $db->nameQuote('cluster_type') . ' is null)';
					} else {
						$tmpCond .= ' a.' . $db->nameQuote('cluster_type') . ' = ' . $db->Quote($group) . ')';
					}

					$cond[] = $tmpCond;
				}
			}

			if ($appWithVerbs) {
				$conditions = [];

				foreach ($appWithVerbs as $group => $apps) {
					foreach ($apps as $app => $verbs) {
						if (empty($verbs)) {
							continue;
						}

						$verbString = '';

						foreach ($verbs as $verb) {
							$verbString .= ($verbString) ? ',' . $db->Quote($verb) : $db->Quote($verb);
						}

						$tmpCond = ' NOT (a.' . $db->nameQuote('context_type') . ' = ' . $db->Quote($app) . ' and';
						$tmpCond .= ' a.' . $db->nameQuote('verb') . ' IN (' . $verbString . ') and ';
						if ($group == SOCIAL_APPS_GROUP_USER) {
							$tmpCond .= ' a.' . $db->nameQuote('cluster_type') . ' is null)';
						} else {
							$tmpCond .= ' a.' . $db->nameQuote('cluster_type') . ' = ' . $db->Quote($group) . ')';
						}

						$cond[] = $tmpCond;
					}
				}
			}
		}
	}

	/**
	 * get unaccessible apps.
	 *
	 * @since	2.1
	 * @access	public
	 */
	private function getUnAccessilbleUserApps($userId = null, $group =  SOCIAL_APPS_GROUP_USER, $perspective = null)
	{
		static $_cache = array();

		if (! isset($_cache[$group])) {

			$db = ES::db();
			$sql = $db->sql();
			$exclude = array();

			$query = "select `element` from `#__social_apps`";
			$query .= " where `type` = 'apps'";
			$query .= " and `state` = 0";
			if ($group && $group != 'all') {
				$query .= ' and `group` = ' . $db->Quote($group);
			}

			$sql->raw($query);
			$db->setQuery($sql);

			$results = $db->loadColumn();

			if ($results) {
				foreach ($results as $app) {
					$exclude[$app] = true;
				}
			}

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
}
