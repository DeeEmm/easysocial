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

jimport('joomla.application.component.model');

ES::import('admin:/includes/model');

class EasySocialModelAudios extends EasySocialModel
{
	public function __construct($config = array())
	{
		parent::__construct('audios', $config);
	}

	/**
	 * Initializes all the generic states from the form
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function initStates()
	{
		$filter = $this->getUserStateFromRequest('filter', 'all');
		$ordering = $this->getUserStateFromRequest('ordering', 'id');
		$direction = $this->getUserStateFromRequest('direction', 'ASC');

		$this->setState('filter', $filter);

		parent::initStates();

		// Override the ordering behavior
		$this->setState('ordering', $ordering);
		$this->setState('direction', $direction);
	}

	/**
	 * Retrieves a list of profiles that has access to a genre
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getGenreAccess($genreId, $type = 'create')
	{
		$db = ES::db();

		$sql = $db->sql();
		$sql->select('#__social_audios_genres_access');
		$sql->column('profile_id');
		$sql->where('genre_id', $genreId);
		$sql->where('type', $type);

		$db->setQuery($sql);

		$ids = $db->loadColumn();

		return $ids;
	}

	/**
	 * Determines if the genre is already exists
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function isGenreExists($genre)
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_audios_genres');
		$sql->where('alias', strtolower($genre));

		$db->setQuery($sql->getTotalSql());

		$result = $db->loadResult();

		return !empty($result);
	}

	/**
	 * Inserts new access for a cluster genre
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function insertGenreAccess($genreId, $type = 'create', $profiles = array())
	{
		$db = ES::db();

		// Delete all existing access type first
		$sql = $db->sql();
		$sql->delete('#__social_audios_genres_access');
		$sql->where('genre_id', $genreId);
		$sql->where('type', $type);

		$db->setQuery($sql);
		$db->Query();

		if (!$profiles) {
			return;
		}

		foreach ($profiles as $id) {
			$sql->clear();
			$sql->insert('#__social_audios_genres_access');
			$sql->values('genre_id', $genreId);
			$sql->values('type', $type);
			$sql->values('profile_id', $id);

			$db->setQuery($sql);
			$db->Query();
		}

		return true;
	}

	/**
	 * Retrieves the total user's audios available on site
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getTotalUserAudios($userId = null)
	{
		$user = ES::user($userId);
		$userId = $user->id;

		$db = $this->db;
		$sql = $db->sql();

		$query = array();
		$query[] = "select count(1) from `#__social_audios` as a";
		$query[] = "where a.state = " . $db->Quote(SOCIAL_AUDIO_PUBLISHED);
		$query[] = "and a.user_id = " . $db->Quote($userId);

		// Should not include scheduled audios here.
		$query[] = "and a.`scheduled` = " . $db->Quote(SOCIAL_AUDIO_UNSCHEDULED);

		$sql->raw($query);
		$this->db->setQuery($sql);
		$total = (int) $this->db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total pending audios available on site
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getTotalPendingAudios($userId = null)
	{
		$user = ES::user($userId);
		$userId = $user->id;

		$sql = $this->db->sql();

		$query = "select count(1) from `#__social_audios` as a";
		$query .= " where a.`state` = " . $this->db->Quote(SOCIAL_AUDIO_PENDING);

		// Should not include pending scheduled.
		$query .= " and a.`scheduled` = " . $this->db->Quote(SOCIAL_AUDIO_UNSCHEDULED);
		
		$query .= " and a.`user_id` = " . $this->db->Quote($userId);
		$query .= " and a.`type` = " . $this->db->Quote('user');

		$sql->raw($query);
		$this->db->setQuery($sql);
		$total = (int) $this->db->loadResult();

		return $total;
	}


	/**
	 * Retrieves the total featured audios available on site
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getTotalFeaturedAudios($options = array())
	{
		$db = $this->db;
		$sql = $this->db->sql();
		$config = ES::config();
		$viewer = ES::user();

		$uid = (int) $this->normalize($options, 'uid', null);
		$type = $this->normalize($options, 'type', null);
		$userid = (int) $this->normalize($options, 'userid', null);
		$privacy = $this->normalize($options, 'privacy', true);
		$viewer = ES::user();

		if (!$config->get('privacy.enabled') && $type == 'user') {
			$privacy = false;
		}

		$query = array();
		$query[] = 'SELECT COUNT(1) FROM `#__social_audios` AS a';

		// Blocking of users
		// $query[] = $this->getUserBlockingJoinQuery($viewer);

		$blockOptions = array();
		if ($uid && $type && $type != SOCIAL_TYPE_USER) {
			$blockOptions['clusterColumnAlias'] = 'uid';
			$blockOptions['respectClusterColumnType'] = true;
		}
		$query[] = $this->getUserBlockingJoinQuery($viewer, 'bus', 'user_id', 0, $blockOptions);

		// Retrieve audios from clusters as well
		if (!$type) {
			$query[] = $this->getClusterPrivacyJoinQuery();
		}

		$query[] = 'WHERE a.`state` = ' . $db->Quote(SOCIAL_AUDIO_PUBLISHED);
		$query[] = 'AND a.`featured` = ' . $db->Quote(SOCIAL_AUDIO_FEATURED);

		// Should not include scheduled audios here.
		$query[] = "and a.`scheduled` = " . $db->Quote(SOCIAL_AUDIO_UNSCHEDULED);

		if ($userid) {
			$query[] = 'AND a.`user_id` = ' . $db->Quote($userid);
		}

		if ($uid && $type) {
			$query[] = 'AND a.`uid` = ' . $db->Quote($uid);
			$query[] = 'AND a.`type` = ' . $db->Quote($type);
		}

		$query[] = $this->getUserBlockingClauseQuery($viewer);

		// Site admins should never be constrained to the privacy
		$isSiteAdmin = $viewer->isSiteAdmin();

		if (!$isSiteAdmin) {
			if ($privacy) {
				$query[] = $this->getPrivacyQuery($viewer->id, 'audios');
			}

			// Retrieve audios from clusters as well for global listings
			if (!$uid && !$type) {
				$query[] = $this->getClusterPrivacyClauseQuery($viewer->id);
			}
		}

		$sql->raw($query);
		$this->db->setQuery($sql);
		$total = (int) $this->db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the total audios available on site
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getTotalAudios($options = array())
	{
		$db = $this->db;
		$sql = $this->db->sql();
		$config = ES::config();
		$viewer = ES::user()->id;

		$uid = (int) $this->normalize($options, 'uid', null);
		$type = $this->normalize($options, 'type', null);
		$userid = (int) $this->normalize($options, 'userid', null);
		$state = $this->normalize($options, 'state', SOCIAL_AUDIO_PUBLISHED);
		$privacy = $this->normalize($options, 'privacy', true);
		$day = $this->normalize($options, 'day', false);
		$viewer = ES::user();
		$isSiteAdmin = $viewer->isSiteAdmin();
		$clauses = array();
		$includeUnpublishedGenre = $this->normalize($options, 'includeUnpublishedGenre', false);

		$query = array();
		$query[] = 'SELECT COUNT(1) FROM `#__social_audios` AS a';

		// Filtering audios for clusters
		if (!$isSiteAdmin && $privacy && $type && $type != 'user') {
			$query[] = 'INNER JOIN `#__social_clusters` AS `cls`';
			$query[] = 'ON a.`uid` = cls.`id`';
			$query[] = 'AND a.`type` = cls.`cluster_type`';
		}

		if (!$includeUnpublishedGenre) {
			$query[] = 'LEFT JOIN `#__social_audios_genres` as `gen`';
			$query[] = 'ON gen.`id` = a.`genre_id`';
		}

		// Blocking of users
		// $query[] = $this->getUserBlockingJoinQuery($viewer);

		$blockOptions = array();
		if ($uid && $type && $type != SOCIAL_TYPE_USER) {
			$blockOptions['clusterColumnAlias'] = 'uid';
			$blockOptions['respectClusterColumnType'] = true;
		}

		// When viewer is a logged in user, we need to check against the blocking features
		$query[] = $this->getUserBlockingJoinQuery($viewer, 'bus', 'user_id', 0, $blockOptions);

		// Make sure only publish and not scheduled audios get picked.
		$clauses[] = 'a.`scheduled` = ' . $db->Quote('0');

		if (!$includeUnpublishedGenre) {
			$clauses[] = 'gen.`state` = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		}

		// Retrieve audios from clusters as well
		if (!$uid && !$type) {
			$query[] = $this->getClusterPrivacyJoinQuery();
		}

		if ($state != 'all') {
			$clauses[] = 'a.`state`=' . $db->Quote($state);
		}

		if ($userid) {
			$clauses[] = "a.user_id = " . $db->Quote($userid);
		}

		if ($uid && $type) {
			$clauses[] = "a.uid = " . $this->db->Quote($uid);
			$clauses[] = "a.type = " . $this->db->Quote($type);
		}

		// Blocking of users
		$query[] = $this->getUserBlockingClauseQuery($viewer);

		if ($day) {
			$start = $day . ' 00:00:01';
			$end = $day . ' 23:59:59';
			$clauses[] = '(a.`created` >= ' . $this->db->Quote($start) . ' and a.`created` <= ' . $this->db->Quote($end) . ')';
		}

		// When viewing audios from a cluster, we also need to check their privacy
		if (!$isSiteAdmin && $privacy && $type && $type != 'user') {
			$tmp = "(";
			$tmp .= " (cls.`type` IN (1,4)) OR";
			$tmp .= " (cls.`type` > 1) AND " . $this->db->Quote($viewer->id) . " IN (select scn.`uid` from `#__social_clusters_nodes` as scn where scn.`cluster_id` = a.`uid` and scn.`type` = " . $this->db->Quote(SOCIAL_TYPE_USER) . " and scn.`state` = 1)";
			$tmp .= ")";

			$clauses[] = $tmp;
		}

		// Sit admins should not be constrained by the privacy
		if (!$isSiteAdmin && $privacy && ($type == 'user' || !$type)) {
			$clauses[] = $this->getPrivacyQuery($viewer->id, 'audios', 'a', '');
		}

		// Retrieve audios from clusters as well for global listings
		if (!$uid && !$type) {
			$tmp = $this->getClusterPrivacyClauseQuery($viewer->id, 'a', 'cls', '');

			if ($tmp) {
				$clauses[] = $tmp;
			}
		}


		if ($clauses) {
			if (count($clauses) == 1) {
				$query[] = "where " . $clauses[0];
			} else if (count($clauses) > 1) {

				$whereCond = array_shift($clauses);

				$query[] = "where " . $whereCond;
				$query[] = "and " . implode(" and ", $clauses);
			}
		}

		// echo str_ireplace('#__', 'jos_', implode(' ', $query));
		// exit;

		$sql->raw($query);

		$this->db->setQuery($sql);
		$total = (int) $this->db->loadResult();

		return $total;
	}

	/**
	 * Retrieves the list of audios for the back end
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getItems()
	{
		$sql = $this->db->sql();

		$filter = $this->getState('filter');
		$search = $this->getState('search');

		$sql->select('#__social_audios');

		if ($filter != 'all') {
			$sql->where('state', $filter);
		}

		if ($search) {
			$sql->where('title', '%' . $search . '%', 'LIKE');
		}

		// Determine the ordering
		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction = $this->getState('direction');
			$sql->order($ordering, $direction);
		}

		// Set the total records for pagination.
		$this->setTotal($sql->getTotalSql());

		$result = $this->getData($sql);

		if (!$result) {
			return $result;
		}

		$audios = array();

		foreach ($result as $row) {

			$tmp = (array) $row;

			$row = ES::table('Audio');
			$row->bind($tmp);

			$audio = ES::audio($row);

			$audios[] = $audio;
		}

		return $audios;
	}

	/**
	 * Retrieves a list of audios from the site
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getAudiosForCron($options = array())
	{
		// search criteria
		$filter = $this->normalize($options, 'filter', '');
		$sort = $this->normalize($options, 'sort', 'latest');
		$limit = $this->normalize($options, 'limit', false);

		$db = ES::db();
		$sql = $db->sql();

		$query[] = "select a.* from `#__social_audios` as a";

		if ($filter == 'processing') {
			$query[] = 'WHERE a.`state`=' . $db->Quote(SOCIAL_AUDIO_PROCESSING);
		} else {
			$query[] = "where a.`state` = " . $db->Quote(SOCIAL_AUDIO_PENDING);
		}

		if ($sort) {
			switch ($sort) {
				case 'popular':
					$query[] = "order by a.hits desc";
					break;

				case 'alphabetical':
					$query[] = "order by a.title asc";
					break;

				case 'random':
					$query[] = "order by RAND()";
					break;

				case 'likes':
					$query[] = "order by likes desc";
					break;

				case 'commented':
					$query[] = "order by totalcomments desc";
					break;

				case 'latest':
				default:
					$query[] = "order by a.created desc";
					break;
			}
		}

		if ($limit) {
			$query[] = "limit $limit";
		}

		$query = implode(' ', $query);
		$sql->raw($query);

		$db->setQuery($sql);
		$results = $db->loadObjectList();

		$audios = array();

		if ($results) {
			foreach ($results as $row) {
				$audio = ES::audio($row->uid, $row->type);
				$audio->load($row);

				$audios[] = $audio;
			}
		}

		return $audios;
	}

	/**
	 * Retrieves the list of items which stored in Amazon
	 *
	 * @since   2.1.6
	 * @access  public
	 */
	public function getAudiosStoredExternally($storageType = 'amazon', $options = array())
	{
		// Get the number of files to process at a time
		$config = ES::config();
		$limit = $config->get('storage.amazon.limit', 10);

		$db = ES::db();
		$sql = $db->sql();
		$sql->select('#__social_audios');
		$sql->where('storage', $storageType);

		// If there's an exclusion list, exclude it
		$exclusion = isset($options['exclusion']) ? $options['exclusion'] :'';

		if (!empty($exclusion)) {

			// Ensure that it's an array
			$exclusion = ES::makeArray($exclusion);

			foreach ($exclusion as $id) {
				$sql->where('id', $id, '!=', 'AND');
			}
		}

		$sql->limit($limit);

		$db->setQuery($sql);

		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Retrieves the list of items which stored in joomla and ready to sync to remote storage
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function getAudiosForRemoteUpload($exclusion = array(), $limit = 10)
	{

		// Get the number of files to process at a time
		$config = ES::config();

		$db = ES::db();

		$query = "SELECT a.* FROM `#__social_audios` as a";
		$query .= " WHERE a.`state` = " . $db->Quote(SOCIAL_AUDIO_PUBLISHED);
		$query .= " AND a.`storage` = " . $db->Quote(SOCIAL_STORAGE_JOOMLA);

		if ($exclusion) {

			$exclusion = ES::makeArray($exclusion);
			$exclusionIds = array();

			foreach ($exclusion as $exclusionId) {
				$exclusionIds[] = $db->Quote($exclusionId);
			}

			$exclusionIds = implode(',', $exclusionIds);

			$query .= 'AND a.`id` NOT IN (' . $exclusionIds . ')';
		}

		$query .= " ORDER BY a.`created` asc";
		$query .= " LIMIT $limit";

		$db->setQuery($query);

		$result = $db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$audios = array();

		foreach ($result as $row) {
			$audio = ES::audio($row->uid, $row->type);
			$audio->load($row);

			$cluster = $audio->getCluster();

			$audio->creator = $audio->getAudioCreator($cluster);

			$audios[] = $audio;
		}

		return $audios;
	}

	/**
	 * Returns the total number of items for the current query
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function getTotal()
	{
		return $this->total;
	}

	/**
	 * Retrieves a list of audios from the site
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getAudios($options = [])
	{
		$db = ES::db();
		$sql = $db->sql();
		$config = ES::config();

		// search criteria
		$privacy = $this->normalize($options, 'privacy', true);

		// If privacy has been disabled, we should always disable privacy checks
		if (!$config->get('privacy.enabled')) {
			$privacy = false;
		}

		$filter = $this->normalize($options, 'filter', '');
		$featured = $this->normalize($options, 'featured', null);
		$genre = $this->normalize($options, 'genre', '');
		$sort = $this->normalize($options, 'sort', 'latest');
		$maxlimit = $this->normalize($options, 'maxlimit', 0);
		$limit = $this->normalize($options, 'limit', false);
		$includeFeatured = $this->normalize($options, 'includeFeatured', null);

		$storage = $this->normalize($options, 'storage', false);
		$uid = $this->normalize($options, 'uid', null);
		$type = $this->normalize($options, 'type', null);
		$source = $this->normalize($options, 'source', false);
		$userid = $this->normalize($options, 'userid', null);
		$hashtags = $this->normalize($options, 'hashtags', null);
		$includeUnpublishedGenre = $this->normalize($options, 'includeUnpublishedGenre', false);

		$useLimit = true;
		$viewer = ES::user();
		$isSiteAdmin = $viewer->isSiteAdmin();
		$query = array();

		$query[] = "select a.*";

		if ($sort == 'likes') {
			$query[] = ", (select count(1) from `#__social_likes` as exb where exb.uid = a.id and exb.type = 'audios.user.create') as likes";
		}

		if ($sort == 'commented') {
			$query[] = ", (select count(1) from `#__social_comments` as exb where exb.uid = a.id and exb.element = 'audios.user.create') as totalcomments";
		}

		$query[] = 'FROM `#__social_audios` AS a';

		if (!is_null($type) && $type != 'user') {
			$query[] = 'INNER JOIN `#__social_clusters` AS `cls`';
			$query[] = 'ON a.`uid` = cls.`id`';
			$query[] = 'AND a.`type` = cls.`cluster_type`';
		}

		if (!$includeUnpublishedGenre) {
			$query[] = 'LEFT JOIN `#__social_audios_genres` as `gen`';
			$query[] = 'ON gen.`id` = a.`genre_id`';
		}

		// Retrieve audios from clusters as well
		if (!$uid && !$type) {
			$query[] = $this->getClusterPrivacyJoinQuery();
		}

		if ($hashtags) {
			$query[] = " inner join`#__social_tags` as hashtag on a.`id` = hashtag.`target_id`";
		}

		// Users blocking feature
		// $query[] = $this->getUserBlockingJoinQuery($viewer);

		$blockOptions = array();
		if ($uid && $type && $type != SOCIAL_TYPE_USER) {
			$blockOptions['clusterColumnAlias'] = 'uid';
			$blockOptions['respectClusterColumnType'] = true;
		}

		// When viewer is a logged in user, we need to check against the blocking features
		$query[] = $this->getUserBlockingJoinQuery($viewer, 'bus', 'user_id', 0, $blockOptions);

		if ($filter == 'pending') {
			$query[] = "where a.`state` = " . $db->Quote(SOCIAL_AUDIO_PENDING);

			// Should not include scheduled audios here.
			$query[] = "and a.`scheduled` = " . $db->Quote(SOCIAL_AUDIO_UNSCHEDULED);
		}

		if ($filter == 'processing') {
			$query[] = 'WHERE a.`state`=' . $db->Quote(SOCIAL_AUDIO_PROCESSING);
		}

		if ($filter != 'pending' && $filter != 'processing') {
			$query[] = "where a.`state` = " . $db->Quote(SOCIAL_AUDIO_PUBLISHED);

			// Should not include scheduled audios here.
			$query[] = "and a.`scheduled` = " . $db->Quote(SOCIAL_AUDIO_UNSCHEDULED);
		}

		if (!$includeUnpublishedGenre) {
			$query[] = 'and gen.`state` = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		}

		if ($uid && $type) {
			$query[] = 'AND a.`uid`=' . $db->Quote($uid);
			$query[] = 'AND a.`type`=' . $db->Quote($type);
		}

		if ($filter == 'mine') {
			$query[] = "and a.`user_id` = " . $db->Quote($viewer->id);
		}

		if ($filter == 'pending' && $userid) {
			$query[] = "and a.`user_id` = " . $db->Quote($userid);
		}

		if ($filter == SOCIAL_TYPE_USER) {
			$query[] = "and a.`user_id` = " . $db->Quote($userid);
		}

		if ($genre) {
			if (!is_array($genre)) {
				// This is to fixed data that came from module setting which have this format 6:6:alias
				$genre = (int) $genre;
				$genre = ES::makeArray($genre);
			}

			$query[] = "and a.`genre_id` IN (" . implode(',', $genre) . ")";
		}

		if ($hashtags) {
			$hashtagQuery = array();

			$tags = explode(',', $hashtags);

			if ($tags) {
				if (count($tags) == 1) {
					$query[] = 'AND hashtag.`title` =' . $db->Quote($tags[0]);
				} else {
					$totalTags = count($tags);
					$tagQuery = '';

					for ($t = 0; $t < $totalTags; $t++) {
						$tagQuery .= ($t < $totalTags - 1) ? ' (hashtag.`title` = ' . $db->Quote($tags[$t]) . ') OR ' : ' (hashtag.`title` = ' . $db->Quote($tags[$t]) . ')';
					}

					$query[] = 'AND (' . $tagQuery . ')';
				}

				$query[] = 'AND hashtag.`target_type` = ' . $db->Quote('audio');
			}
		}

		$exclusion = $this->normalize($options, 'exclusion', null);

		if ($exclusion) {

			$exclusion = ES::makeArray($exclusion);
			$exclusionIds = array();

			foreach ($exclusion as $exclusionId) {
				$exclusionIds[] = $db->Quote($exclusionId);
			}

			$exclusionIds = implode(',', $exclusionIds);

			$query[] = 'AND a.' . $db->qn('id') . ' NOT IN (' . $exclusionIds . ')';
		}

		// featured filtering
		if ($filter == 'featured') {
			$query[] = "and a.`featured` = " . $db->Quote(SOCIAL_AUDIO_FEATURED);
		}

		// featured audio only
		if (!$includeFeatured && !is_null($featured) && $featured !== '') {
			$query[] = "and a.`featured` = " . $db->Quote((int) $featured);
		}

		if ($storage !== false) {
			$query[] = 'AND a.`storage` = ' . $db->Quote($storage);
		}

		if ($source !== false) {
			$query[] = 'AND a.`source`=' . $db->Quote($source);
		}

		$query[] = $this->getUserBlockingClauseQuery($viewer);

		if (!$isSiteAdmin) {

			if ($privacy) {
				// cluster privacy
				if ($type && $type != 'user') {
					$query[] = " AND (";
					$query[] = " (cls.`type` IN (1,4)) OR";
					$query[] = " (cls.`type` > 1) AND " . $db->Quote($viewer->id) . " IN (select scn.`uid` from `#__social_clusters_nodes` as scn where scn.`cluster_id` = a.`uid` and scn.`type` = " . $db->Quote(SOCIAL_TYPE_USER) . " and scn.`state` = 1)";
					$query[] = ")";
				}


				if (!$type || $type == 'user') {
					$query[] = $this->getPrivacyQuery($viewer->id, 'audios');
				}
			}

			// Retrieve audios from clusters as well
			if (!$uid && !$type) {
				$query[] = $this->getClusterPrivacyClauseQuery($viewer->id);
			}
		}

		$navigationType = $this->normalize($options, 'navigationType', null);
		$created = $this->normalize($options, 'created', null);

		if ($navigationType && $created) {
			// Need to use its own ordering for navigation
			$sort = false;

			if ($navigationType == 'prev') {
				$query[] = 'AND a.`created` < ' . $db->Quote($created);
				$query[] = 'ORDER BY a.`created` DESC';
			}

			if ($navigationType == 'next') {
				$query[] = 'AND a.`created` > ' . $db->Quote($created);
				$query[] = 'ORDER BY a.`created` ASC';
			}
		}

		if (!$maxlimit && $limit) {

			$totalQuery = implode(' ', $query);

			// Set the total number of items.
			$this->setTotal($totalQuery, true);
		}

		// the sorting must come after the privacy checking to have better sql performance.
		if ($sort) {
			switch ($sort) {
				case 'popular':
					$query[] = "order by a.`hits` desc";
					break;

				case 'alphabetical':
					$query[] = "order by a.`title` asc";
					break;

				case 'random':

					$rndColumns = array('a.id', 'a.title', 'a.hits', 'a.featured', 'a.title');
					$rndSorts = array('asc', 'desc', 'desc', 'asc', 'asc', 'desc');

					$rndColumn = $rndColumns[array_rand($rndColumns)];
					$rndSort = $rndSorts[array_rand($rndSorts)];

					$query[] = "order by $rndColumn $rndSort";
					break;

				case 'likes':
					$query[] = "order by likes desc";
					break;

				case 'commented':
					$query[] = "order by totalcomments desc";
					break;

				case 'latest':
				default:

					if ($config->get('audio.sortbehavior') === 'created') {
						$query[] = "order by a.`" . $config->get('audio.sortbehavior') . "` desc";
					}

					if ($config->get('audio.sortbehavior') === 'assigned_date') {
						$query[] = "order by a.`assigned_date` desc, a.`created` desc";
					}

					break;
			}
		}

		if ($maxlimit) {
			$useLimit = false;
			$query[] = "limit $maxlimit";
		}

		if (!$maxlimit && $limit) {
			// Get the limitstart.
			$limitstart = $this->input->get('limitstart', 0, 'int');
			$limitstart = $limit ? floor($limitstart / $limit) * $limit : 0;

			$this->setState('limit', $limit);
			$this->setState('limitstart', $limitstart);

			$query[] = "limit $limitstart, $limit";
		}

		$query = implode(' ', $query);
		// echo str_replace('#_', 'jos', $query);
		
		$sql->clear();
		$sql->raw($query);

		$this->db->setQuery($sql);
		$result = $this->db->loadObjectList();


		if (!$result) {
			return $result;
		}

		$audios = array();

		foreach ($result as $row) {
			$audio = ES::audio($row->uid, $row->type);
			$audio->load($row);

			$cluster = $audio->getCluster();

			$audio->creator = $audio->getAudioCreator($cluster);

			$audios[] = $audio;
		}

		return $audios;
	}

	/**
	 * Overriding parent getData method so that we can specify if we need the limit or not.
	 * If using the pagination query, child needs to use this method.
	 *
	 * @since   2.1
	 * @access  public
	 */
	protected function getData($query, $useLimit = true)
	{
		if ($useLimit) {
			return parent::getData($query);
		} else {
			$this->db->setQuery($query);
		}

		return $this->db->loadObjectList();
	}

	/**
	 * Retrieves the default genre
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getDefaultGenre()
	{
		$db = $this->db;
		$sql = $db->sql();

		$sql->select('#__social_audios_genres');
		$sql->where('default', 1);

		$db->setQuery($sql);

		$result = $db->loadObject();

		if (!$result) {
			return false;
		}

		$genre = ES::table('AudioGenre');
		$genre->bind($result);

		return $genre;
	}

	/**
	 * Retrieves a list of audio genres from the site
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getGenres($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = array();
		$query[] = 'SELECT a.* FROM ' . $db->qn('#__social_audios_genres') . ' AS a';

		// Filter for respecting creation access
		$respectAccess = $this->normalize($options, 'respectAccess', false);
		$profileId = $this->normalize($options, 'profileId', 0);

		if ($respectAccess && $profileId) {
			$query[] = 'LEFT JOIN ' . $db->qn('#__social_audios_genres_access') . ' AS b';
			$query[] = 'ON a.id = b.genre_id';
		}

		$query[] = 'WHERE 1 ';

		// Filter for searching genres
		$search = $this->normalize($options, 'search', '');

		if ($search) {
			$query[] = 'AND ';
			$query[] = $db->qn('title') . ' LIKE ' . $db->Quote('%' . $search . '%');
		}

		// Respect genre creation access
		if ($respectAccess && $profileId) {
			$query[] = 'AND (';
			$query[] = '(b.`profile_id`=' . $db->Quote($profileId) . ')';
			$query[] = 'OR';
			$query[] = '(a.' . $db->qn('id') . ' NOT IN (SELECT `genre_id` FROM `#__social_audios_genres_access`))';
			$query[] = ')';
		}

		// Ensure that the audio are published
		$state = $this->normalize($options, 'state', true);

		// Ensure that all the genres are listed in backend
		$adminView = $this->normalize($options, 'administrator', false);

		if (!$adminView) {
			$query[] = 'AND ' . $db->qn('state') . '=' . $db->Quote($state);
		}

		$ordering = $this->normalize($options, 'ordering', '');
		$direction = $this->normalize($options, 'direction', '');

		if ($ordering) {
			$query[] = ' ORDER BY ' . $db->qn($ordering) . ' ' . $direction;
		}

		$query = implode(' ', $query);

		$pagination = $this->normalize($options, 'pagination', true);

		// Set the total records for pagination.
		$totalSql = str_ireplace('a.*', 'COUNT(1)', $query);
		$this->setTotal($totalSql);

		// We need to go through our paginated library
		$result = $this->getData($query, $pagination);

		if (!$result) {
			return $result;
		}

		$genres = array();

		foreach ($result as $row) {
			$genre = ES::table('AudioGenre');
			$genre->bind($row);

			$genres[] = $genre;
		}

		return $genres;
	}

	/**
	 * Retrieves the total number of audios from a genre
	 *
	 * @since   3.2.0
	 * @access  public
	 */
	public function getTotalAudiosFromGenre($genreId, $cluster = false, $uid = null, $type = null)
	{
		$db = $this->db;
		$sql = $this->db->sql();
		$config = ES::config();
		$viewer = ES::user();
		$privacy = $config->get('privacy.enabled') ? true : false;

		$query = array();
		$query[] = 'SELECT COUNT(1) FROM `#__social_audios` AS a';

		// Blocking of users
		// $query[] = $this->getUserBlockingJoinQuery($viewer);

		$blockOptions = array();
		if ($uid && $type && $type != SOCIAL_TYPE_USER) {
			$blockOptions['clusterColumnAlias'] = 'uid';
			$blockOptions['respectClusterColumnType'] = true;
		}

		// When viewer is a logged in user, we need to check against the blocking features
		$query[] = $this->getUserBlockingJoinQuery($viewer, 'bus', 'user_id', 0, $blockOptions);
		

		// Retrieve audios from clusters as well
		if (!$cluster && !$uid && !$type) {
			$query[] = $this->getClusterPrivacyJoinQuery();
		}

		$query[] = 'WHERE a.`state` = ' . $db->Quote(SOCIAL_AUDIO_PUBLISHED);
		$query[] = 'AND a.`genre_id` =' . $db->Quote($genreId);

		// Should not include scheduled audios here.
		$query[] = "and a.`scheduled` = " . $db->Quote(SOCIAL_AUDIO_UNSCHEDULED);

		if ($uid && $type) {
			$query[] = 'AND a.`uid`=' . $db->Quote($uid);
			$query[] = 'AND a.`type`=' . $db->Quote($type);

			// cluster do not use privacy access column
			if ($cluster) {
				$privacy = false;
			}
		}

		// Blocking of users
		$query[] = $this->getUserBlockingClauseQuery($viewer);

		if ($privacy) {
			$query[] = $this->getPrivacyQuery($viewer->id, 'audios');
		}

		// Retrieve videos from clusters as well provided that we are retrieving global listing of videos
		if (!$uid && !$type) {
			$query[] = $this->getClusterPrivacyClauseQuery($viewer->id);
		}

		$sql->raw($query);
		$this->db->setQuery($sql);
		$total = $this->db->loadResult();

		return $total;
	}

	/**
	 * Determines if the audio should be associated with the stream item
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getStreamId($audioId, $verb)
	{
		$db     = ES::db();
		$sql    = $db->sql();

		$sql->select('#__social_stream_item', 'a');
		$sql->column('a.uid');
		$sql->where('a.context_type', SOCIAL_TYPE_AUDIOS);
		$sql->where('a.context_id', $audioId);
		$sql->where('a.verb', $verb);

		$db->setQuery($sql);

		$uid    = (int) $db->loadResult();

		return $uid;
	}

	/**
	 * Get audio from stream
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getStreamAudio($streamId)
	{
		$db = ES::db();

		$query = "select a.* from `#__social_audios` as a";
		$query .= " inner join `#__social_stream_item` as b on a.`id` = b.`context_id`";
		$query .= " where b.`uid` = " . $db->Quote($streamId);
		$query .= " and a.`state` = " . $db->Quote(SOCIAL_STATE_PUBLISHED);

		$db->setQuery($query);
		$row = $db->loadObject();

		$audio = ES::audio($row->uid, $row->type);
		$audio->load($row);

		return $audio;
	}

	/**
	 * Update audios genre ordering
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function updateGenresOrdering($id, $order)
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = "update `#__social_audios_genres` set ordering = " . $db->Quote($order);
		$query .= " where id = " . $db->Quote($id);

		$sql->raw($query);

		$db->setQuery($sql);
		$state = $db->query();

		return $state;
	}

	/**
	 * update audio from stream
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function updateStreamAudio($streamId, $data)
	{
		$db = ES::db();
		$config = ES::config();

		$streamItem = ES::table('StreamItem');
		$streamItem->load(array('uid' => $streamId));

		// audio id before we update
		$existingId = $streamItem->context_id;
		$oldAudio = ES::table('audio');
		$oldAudio->load($existingId);

		// determine if this is a new audio
		$isNewAudio = false;
		if ($oldAudio->source == $data['source']) {
			$isNewAudio = $oldAudio->source == 'link' ? $oldAudio->path != $data['link'] : $existingId != $data['id'];
		} else {
			$isNewAudio = true;
		}

		if ($isNewAudio) {

			$audio = ES::audio();

			// Save options for the audio library
			$saveOptions = array();

			// If this is a link source, we just load up a new audio library
			if ($data['source'] == 'link') {
				$data['link'] = $audio->format($data['link']);
			}

			// If this is an audio upload, the id should be provided because audio are created first.
			if ($data['source'] == 'upload') {
				$id = $data['id'];

				$audio = ES::audio();
				$audio->load($id);

				// audio library needs to know that we're storing this from the story
				$saveOptions['story'] = true;

				// We cannot publish the audio if auto encoding is disabled
				if ($config->get('audio.autoencode')) {
					$data['state'] = SOCIAL_AUDIO_PUBLISHED;
				}
			}

			$data['uid'] = $oldAudio->uid;
			$data['type'] = $oldAudio->type;
			$data['user_id'] = $oldAudio->user_id;

			// update isnew flag
			$audio->table->isnew = 0;

			// saving new audio
			unset($data['id']);
			$audio->save($data, array(), $saveOptions);

			// update existing stream item
			$streamItem->context_id = $audio->id;
			$streamItem->store();

			// delete existing audio
			$oldAudio->delete();

		} else {
			// 1. update title, description and genre for existing audio.
			$oldAudio->title = $data['title'];
			$oldAudio->artist = $data['artist'];
			$oldAudio->album = $data['album'];
			$oldAudio->description = $data['description'];
			$oldAudio->genre_id = $data['genre_id'];

			// make sure title has no leading / ending space
			$oldAudio->title = ESJString::trim($oldAudio->title);

			// replace two or more spacing in between words into one spacing only.
			$oldAudio->title = preg_replace('#\s{2,}#',' ',$oldAudio->title);

			// check for video title uniqueness accross user.
			// since title in audio also used as permalink alias,
			// we need to unsure the uniqueness of the title from a user.
			$check = true;
			$i = 0;

			do {
				if ($this->isTitleExists($oldAudio->title, $oldAudio->user_id, $oldAudio->id)) {
					$oldAudio->title = $oldAudio->title . '-' . ++$i;
					$check = true;
				} else {
					$check = false;
				}
			} while ($check);

			$state = $oldAudio->store();
			if ($state) {
				$audio = ES::audio($oldAudio);
				$audio->syncIndex();
			}
		}

		return true;
	}

	/**
	 * Searches for a user's audio.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function search($id, $term, $options = array())
	{
		$config = ES::config();
		$db = ES::db();

		$query = array();
		$query[] = 'SELECT a.' . $db->nameQuote('id') . ' FROM ' . $db->nameQuote('#__social_audios') . ' AS a';
		$query[] = 'WHERE a.' . $db->nameQuote('title') . ' LIKE ' . $db->Quote('%' . $term . '%');

		// If this is for playlist, we only allow uploaded audio
		if (isset($options['playlist']) && $options['playlist']) {
			$query[] = 'AND a.' . $db->nameQuote('source') . ' = ' . $db->Quote('upload');
			$query[] = 'AND a.' . $db->nameQuote('state') . ' = ' . $db->Quote(SOCIAL_STATE_PUBLISHED);
		}

		if (isset($options['exclude']) && $options['exclude']) {
			$excludeIds = '';

			if (!is_array($options['exclude'])) {
				$options['exclude'] = explode(',', $options['exclude']);
			}

			foreach ($options['exclude']  as $id) {
				$excludeIds .= (empty($excludeIds)) ? $db->Quote($id) : ', ' . $db->Quote($id);
			}

			$query[] = 'AND a.' . $db->nameQuote('id') . ' NOT IN (' . $excludeIds . ')';
		}

		// Glue back query.
		$query = implode(' ', $query);

		$limit = isset($options['limit']) ? $options['limit'] : false;
		$limitstart = isset($options['limitstart']) ? $options['limitstart'] : '10';

		if ($limit) {

			// get the total count.
			$replaceStr = 'SELECT b.' . $db->nameQuote('id') . ' FROM ';
			$totalSQL = str_replace($replaceStr, 'SELECT COUNT(1) FROM ', $query);

			$db->setQuery($totalSQL);
			$this->total = $db->loadResult();

			// now we append the limit
			$query .= " LIMIT $limitstart, $limit";
		}

		$db->setQuery($query);

		$results = $db->loadColumn();

		if (!$results) {
			return false;
		}
		$audios = array();

		foreach ($results as $result) {
			$table = ES::table('Audio');
			$table->load($result);
			$audios[] = ES::audio($table);
		}

		return $audios;
	}

	/**
	 * Retrieves a list of audios from a particular user for GDPR
	 *
	 * @since   2.2
	 * @access  public
	 */
	public function getAudiosGDPR($options = array())
	{
		$db = ES::db();
		$sql = $db->sql();

		$limit = $this->normalize($options, 'limit', false);
		$userid = $this->normalize($options, 'userid', null);

		$query = array();

		$query[] = "select *";
		$query[] = "from `#__social_audios`";
		$query[] = "where `user_id` = " . $db->Quote($userid);

		$exclusion = $this->normalize($options, 'exclusion', null);

		if ($exclusion) {

			$exclusion = ES::makeArray($exclusion);
			$exclusionIds = array();

			foreach ($exclusion as $exclusionId) {
				$exclusionIds[] = $db->Quote($exclusionId);
			}

			$exclusionIds = implode(',', $exclusionIds);

			$query[] = 'AND ' . $db->qn('id') . ' NOT IN (' . $exclusionIds . ')';
		}

		if ($limit) {
			$totalQuery = implode(' ', $query);

			// Set the total number of items.
			$this->setTotal($totalQuery, true);
		}

		// Get the limitstart.
		$limitstart = JFactory::getApplication()->input->get('limitstart', 0, 'int');
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$query[] = "limit $limitstart, $limit";

		$query = implode(' ', $query);

		$sql->clear();
		$sql->raw($query);

		$this->db->setQuery($sql);
		$result = $this->db->loadObjectList();

		if (!$result) {
			return $result;
		}

		$audios = array();

		foreach ($result as $row) {
			$audio = ES::audio($row->uid, $row->type);
			$audio->load($row);

			$cluster = $audio->getCluster();

			$audio->creator = $audio->getAudioCreator($cluster);

			$audios[] = $audio;
		}

		return $audios;
	}

	/**
	 * Method to check if audios's title already exists or not
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function isTitleExists($title, $userId, $ignoreAudioId = 0)
	{
		$db = ES::db();

		$query = "select id from `#__social_audios`";
		$query .= " where `user_id` = " . $db->Quote($userId);
		$query .= " and `title` = " . $db->Quote($title);

		if ($ignoreAudioId) {
			$query .= " and `id` != " . $db->Quote($ignoreAudioId);
		}

		$db->setQuery($query);
		$found = $db->loadResult();

		return $found ? true : false;
	}

	/**
	 * Retrieve the navigation for the single audio item
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getNavigation(SocialAudio $audio)
	{
		$navigation = ['prev','next'];

		$options = array();
		$options['exclusion'] = $audio->id;
		$options['maxlimit'] = 1;
		$options['created'] = $audio->created;
		$options['uid'] = $audio->uid;
		$options['type'] = $audio->type;

		foreach ($navigation as $type) {
			$options['navigationType'] = $type;

			$audio = $this->getAudios($options);

			$navigation[$type] = isset($audio[0]) && $audio[0] ? $audio[0] : '';
		}

		$navigation = (object) $navigation;

		if (!$navigation->prev && !$navigation->next) {
			return false;
		}

		return $navigation;
	}
}
