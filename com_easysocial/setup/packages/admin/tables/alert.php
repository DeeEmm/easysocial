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

ES::import('admin:/tables/table');

class SocialTableAlert extends SocialTable
{
	/**
	 * The unique id of the alert
	 * @var int
	 */
	public $id = null;

	/**
	 * The element of the alert
	 * @var string
	 */
	public $element = null;

	/**
	 * Optional extension for the alert rule.
	 * @var string
	 */
	public $extension = null;

	/**
	 * The rulename of the alert
	 * @var string
	 */
	public $rule = null;

	/**
	 * The setting of email notification for this rule
	 * @var int(1/0)
	 */
	public $email = null;

	/**
	 * The setting of system notification for this rule
	 * @var int(1/0)
	 */
	public $system = null;

	/**
	 * The core state of the rule
	 * @var int(1/0)
	 */
	public $core = null;

	/**
	 * The app state of the rule
	 * @var int(1/0)
	 */
	public $app = null;

	/**
	 * Determines if this rule was created for fields
	 * @var int(1/0)
	 */
	public $field = null;

	/**
	 * The group for the app or field
	 * @var int(1/0)
	 */
	public $group = null;

	/**
	 * The created datetime of the rule
	 * @var datetime
	 */
	public $created = null;

	/**
	 * Published state of this alert rule.
	 * @var int(1/0)
	 */
	public $published = null;
	public $email_published = null;
	public $system_published = null;

	// Extended data for table class purposes
	public $users = array();

	public function __construct(& $db)
	{
		parent::__construct('#__social_alert' , 'id' , $db);
	}

	// Chainability
	public function loadUsers()
	{
		if (!$this->users) {
			$db = ES::db();
			$sql = $db->sql();

			$sql->select('#__social_alert_map');
			$sql->column('user_id', 'id');
			$sql->column('email');
			$sql->column('system');
			$sql->where('alert_id', $this->id);

			$db->setQuery($sql);

			$result = $db->loadObjectList();

			// Extract the id out as key
			foreach ($result as $row) {
				$this->users[$row->id] = $row;
			}
		}

		return $this;
	}

	public function loadLanguage()
	{
		ES::language()->loadSite();

		if (!empty($this->extension)) {
			ES::language()->load($this->extension , JPATH_ROOT);
			ES::language()->load($this->extension , JPATH_ADMINISTRATOR);
		}
	}

	/**
	 * Retrieves the title for this alert rule
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getTitle()
	{
		$this->loadLanguage();

		$element = str_ireplace('.' , '_' , $this->element);
		$rule = str_ireplace('.' , '_' , $this->rule);

		$text = $this->getExtension() . 'PROFILE_NOTIFICATION_SETTINGS_' . strtoupper($element) . '_' . strtoupper($rule);
		$text = JText::_($text);

		return $text;
	}

	/**
	 * Retrieves the title for this alert rule
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getDescription()
	{
		$this->loadLanguage();

		$element = str_ireplace('.' , '_' , $this->element);
		$rule = str_ireplace('.' , '_' , $this->rule);

		$text = $this->getExtension() . 'PROFILE_NOTIFICATION_SETTINGS_' . strtoupper($element) . '_' . strtoupper($rule) . '_DESC';
		$text = JText::_($text);

		return $text;
	}

	/**
	 * Retrieves the extension of this rule
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function getExtension()
	{
		$extension = 'COM_EASYSOCIAL_';

		if ($this->extension) {
			$extension = strtoupper($this->extension) . '_';
		}

		return $extension;
	}

	/**
	 * Retrieves a list of users
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The type of recipient result. 'email' or 'system'
	 * @return
	 */
	public function getUsers($type = '', $filter = array())
	{
		$this->loadUsers();

		if (!empty($type)) {
			$sets = array();

			$participants = $this->formatId($filter);
			$users = $this->formatId($this->users);

			foreach ($participants as $participant) {

				if ((in_array($participant, $users) && $this->users[$participant]->$type) || (!in_array($participant, $users) && $this->$type)) {
					$sets[] = $participant;
				}
			}

			// Array unique it
			$sets = array_unique($sets);

			return $sets;
		}

		return $this->users;
	}

	public function registerUser($user_id)
	{
		$table = ES::table('alertmap');
		$loaded = $table->loadByAlertId($this->id, $user_id);

		if (!$loaded) {
			$table->alert_id = $this->id;
			$table->user_id = $user_id;
			$table->email = $this->email;
			$table->system = $this->system;

			$state = $table->store();

			if (!$state) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Apps email template namespace
	 * apps/{group}/{element}/alerts.{rulename}
	 *
	 * Apps email template path
	 * apps/{group}/{element}/themes/{default/themeName}/emails/{html/text}/alerts.{rulename}
	 *
	 * Core email template namespace
	 * site/{element}/alerts.{rulename}
	 *
	 * Core email template path
	 * site/themes/{wireframe/themeName}/emails/{html/text}/{element}/alerts.{rulename}
	 *
	 * @since 1.0
	 * @access	public
	 */
	public function getMailTemplateName()
	{
		$base = 'site';

		$group = !empty($this->group) ? $this->group : SOCIAL_TYPE_USER;

		if ($this->app) {
			$base = 'apps/' . $group;
		}

		if ($this->field) {

		}

		$base = $this->app > 0 ? 'apps/user' : 'site';

		$path = $base . '/' . $this->element . '/alerts.' . $this->rule;

		return $path;
	}

	/**
	 * Apps sample title
	 * APP_ELEMENT_RULENAME_ALERTTYPE_TITLE
	 *
	 * Core sample title
	 * COM_EASYSOCIAL_ELEMENT_RULENAME_ALERTTYPE_TITLE
	 *
	 * @since 1.0
	 * @access	public
	 */
	public function getNotificationTitle($type)
	{
		$this->loadLanguage();

		$segments = array();

		$segments[] = $this->app > 0 ? 'APP' : 'COM_EASYSOCIAL';

		$segments[] = strtoupper($this->element);
		$segments[] = strtoupper($this->rule);
		$segments[] = strtoupper($type);
		$segments[] = 'TITLE';

		// We do not want to JText this here
		// Notifications are now generated live and translate live instead of storing the translated string into the database
		// $title = JText::_(implode('_', $segments));
		$title = implode('_', $segments);

		return $title;
	}

	/**
	 * Master function to send notifications to users
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function send($participants, $emailOptions = array(), $systemOptions = array())
	{
		if ($emailOptions !== false) {
			$this->sendEmail($participants, $emailOptions);
		}

		if ($systemOptions !== false) {
			$this->sendSystem($participants, $systemOptions);
		}

		return true;
	}

	/**
	 * Used to send notication as batch basis to all cluster's members
	 *
	 * @since	2.0.15
	 * @access	public
	 */
	public function sendClusterMembers($clusterId, $emailOptions = [], $systemOptions = [], $exclude = [])
	{
		if ($emailOptions !== false && ($this->email || !$this->email && $this->email_published)) {
			$this->sendClusterEmail($clusterId, $emailOptions, $exclude);
		}

		if ($systemOptions !== false && $this->system) {
			$this->sendClusterSystem($clusterId, $systemOptions, $exclude);
		}

		return true;
	}

	/**
	 * Used to send email notication as batch basis to all cluster's members
	 *
	 * @since 2.0
	 * @access  public
	 */
	public function sendClusterEmail($clusterId, $options = [], $exclude = [])
	{
		if (is_object($options)) {
			$options = ES::makeArray($options);
		}

		// If params is not set, just give it an empty array
		if (!isset($options['params'])) {
			$options['params'] = [];
		} else {
			// If params is already set, it is possible that it might be object or registry object
			$options['params'] = ES::makeArray($options['params']);
		}

		// Assign any non-table key into params automatically
		$columns = ES::db()->getTableColumns('#__social_mailer');
		foreach ($options as $key => $val) {
			if (!in_array($key, $columns)) {
				$options['params'][$key] = $val;
			}
		}

		// Set default title if no title is passed in
		if (!isset($options['title'])) {
			$options['title'] = $this->getNotificationTitle('email');
		}

		// Set default template if no template is passed in
		if (!isset($options['template'])) {
			$options['template'] = $this->getMailTemplateName();
		}

		if (!isset($options['html'])) {
			$options['html'] = 1;
		}

		// Init a few default widely used parameter
		$user = ES::user();
		if (!isset($options['params']['actor'])) {
			$options['params']['actor'] = $user->getName();
		}

		if (!isset($options['params']['posterName'])) {
			$options['params']['posterName'] = $user->getName();
		}

		if (!isset($options['params']['posterAvatar'])) {
			$options['params']['posterAvatar'] = $user->getAvatar();
		}

		if (!isset($options['params']['posterLink'])) {
			$options['params']['posterLink'] = $user->getPermalink(true, true);
		}

		$params = $options['params'];
		if (is_object($params) && $params instanceof SocialRegistry) {
			$params = $params->toString();
		}

		// Convert params to json string
		if (is_object($params) || is_array($params)) {
			// Encode parameters to get the JSON string.
			$json = ES::json();
			$params = $json->encode($params);
		}

		$title = $options['title'];
		$template = $options['template'];
		$isHtml = $options['html'];

		$jConfig = ES::jconfig(); // joomla config
		$config = ES::config();

		$defaultFromName = $config->get('notifications.general.fromname', '');
		$defaultFromEmail = $config->get('notifications.general.fromemail', '');

		$defaultFromName = $defaultFromName ? $defaultFromName : $jConfig->getValue('fromname');
		$defaultFromEmail = $defaultFromEmail ? $defaultFromEmail : $jConfig->getValue('mailfrom');

		$sid = isset($options['sid']) ? $options['sid'] : 0;
		$senderName = isset($options['sender_name']) ? $options['sender_name'] : $defaultFromName;
		$senderEmail = isset($options['sender_email']) ? $options['sender_email'] : $defaultFromEmail;
		$replyToEmail = isset($options['replyto_email']) ? $options['replyto_email'] : $defaultFromEmail;
		$priority = isset($options['priority']) ? $options['priority'] : SOCIAL_MAILER_PRIORITY_NORMAL;
		$created = ES::date()->toSql();
		$state = SOCIAL_NOTIFICATION_STATE_UNREAD;

		// Prepare for schedule posts.
		$scheduled = isset($options['scheduled']) ? $options['scheduled'] : false;
		
		if ($scheduled) {
			$state = SOCIAL_NOTIFICATION_STATE_SCHEDULED;
		}

		// since this is a batch sql, we can only get the forntend site language.
		$langCode = JFactory::getLanguage()->getTag();

		$db = ES::db();
		$sql = $db->sql();

		$ruleId = $this->id; // rule id

		$query = "insert into `#__social_mailer` (`sid`, `sender_name`, `sender_email`, `replyto_email`, `title`, `template`, `html`, `state`, `created`, `params`,";
		$query .= " `priority`, `language`, `recipient_name`, `recipient_email`) ";
		$query .= " select " . $db->Quote($sid) . ", " . $db->Quote($senderName) . ", " . $db->Quote($senderEmail) . ", " . $db->Quote($replyToEmail) . ", " . $db->Quote($title) . ", ";
		$query .= $db->Quote($template) . ", " . $db->Quote($isHtml) . ", " . $db->Quote($state) . ", " . $db->Quote($created) . ", " . $db->Quote($params) . ", ";
		$query .= $db->Quote($priority) . ", " . $db->Quote($langCode) . ", x.`name`, x.`email` from (";
		$query .= "		select distinct b.`email`, b.`name`,";
		$query .= "			(select `email` from `#__social_alert_map` where `alert_id` = " . $db->Quote($ruleId);
		$query .= "				 and `user_id` = a.`uid` union select `email` from `#__social_alert` where `id` = " . $db->Quote($ruleId) . " limit 1) as `notify_email`";
		$query .= "       from `#__social_clusters_nodes` as a";
		$query .= "       inner join `#__users` as b on a.`uid` = b.`id` and a.`type` = " . $db->Quote('user');

		$query .= "     where b.`block` = " . $db->quote(0);
		$query .= "     and a.`state` = " . $db->quote(1);

		if ($clusterId) {
			if (is_array($clusterId)) {
				$query .= "     and a.`cluster_id` IN (" . implode(',', $clusterId) . ")";
			} else {
				$query .= "     and a.`cluster_id` = " . $db->Quote($clusterId);
			}
		}


		if ($exclude) {
			if (is_array($exclude)) {
				$tmpString = implode(',', $exclude);
				$query .= " and b.id not in (" . $tmpString . ")";
			} else {
				$query .= " and b.id != " . $db->Quote($exclude);
			}
		}

		// do not get any members that subscribed to email digest
		if ($clusterId) {
			if (is_array($clusterId)) {
				$query .= "		and not exists (select `user_id` from `#__social_clusters_subscriptions` as cs where cs.`user_id` = b.`id` and cs.cluster_id IN (" . implode(',', $clusterId) . "))";
			} else {
				$query .= "		and not exists (select `user_id` from `#__social_clusters_subscriptions` as cs where cs.`user_id` = b.`id` and cs.cluster_id = ". $db->Quote($clusterId) . ")";
			}
		}

		$query .= " ) as x";
		$query .= " where x.`notify_email` = " . $db->Quote('1');

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		return true;
	}

	/**
	 * Used to send system notication as batch basis to all cluster's members
	 *
	 * @since	2.0.15
	 * @access	public
	 */
	public function sendClusterSystem($clusterId, $options = array(), $exclude = array())
	{
		if (is_object($options)) {
			$options = ES::makeArray($options);
		}

		// If params is not set, just give it an empty array
		if (!isset($options['params'])) {
			$options['params'] = array();
		}

		// Assign any non-table key into params automatically
		$columns = ES::db()->getTableColumns('#__social_notifications');
		foreach ($options as $key => $val) {
			if (!in_array($key, $columns)) {
				$options['params'][$key] = $val;
			}
		}

		if (!isset($options['uid'])) {
			$options['uid'] = 0;
		}

		if (!isset($options['type'])) {
			$options['type'] = $this->element;
		}

		if (!isset($options['cmd'])) {
			$options['cmd'] = $options['type'] . '.' . $this->rule;
		}

		if (!isset($options['title'])) {
			$options['title'] = $this->getNotificationTitle('system');
		}

		if (!isset($options['actor_id'])) {
			$options['actor_id'] = ES::user()->id;
		}

		if (!isset($options['actor_type'])) {
			$options['actor_type'] = SOCIAL_TYPE_USER;
		}

		if (!isset($options['target_type'])) {
			$options['target_type'] = SOCIAL_TYPE_USER;
		}

		if (!isset($options['url'])) {
			$options['url'] = ES::getURI(true);
		}

		$uid = $options['uid'];
		$type = $options['type'];
		$cmd = $options['cmd'];
		$title = $options['title'];
		$actorId = $options['actor_id'];
		$actorType = $options['actor_type'];
		$targetType = $options['target_type'];
		$url = $options['url'];
		$params = ES::makeJSON($options['params']);

		$content = isset($options['content']) ? $options['content'] : '';
		$contextType = isset($options['context_type']) ? $options['context_type'] : '';
		$contextIds = isset($options['context_ids']) ? $options['context_ids'] : '';
		$image = isset($options['image']) ? $options['image'] : '';
		$created = ES::date()->toSql();
		$state = SOCIAL_NOTIFICATION_STATE_UNREAD;
		$sid = isset($options['sid']) ? $options['sid'] : 0;

		// Prepare for schedule posts.
		$scheduled = isset($options['scheduled']) ? $options['scheduled'] : false;

		if ($scheduled) {
			$state = SOCIAL_NOTIFICATION_STATE_SCHEDULED;
		}

		$db = ES::db();
		$sql = $db->sql();

		$ruleId = $this->id; // rule id

		$query = "insert into `#__social_notifications` (`uid`, `sid`, `type`, `context_ids`, `context_type`, `cmd`, `title`, `content`,";
		$query .= " `image`, `created`, `state`, `actor_id`, `actor_type`, `params`, `url`, `target_type`, `target_id`)";

		$query .= " select " . $db->Quote($uid) . ", " . $db->Quote($sid) . ", " . $db->Quote($type) . ", " . $db->Quote($contextIds) . ", " . $db->Quote($contextType) . ", ";
		$query .= $db->Quote($cmd) . ", " . $db->Quote($title) . ", " . $db->Quote($content) . ", " . $db->Quote($image) . ", " . $db->Quote($created) . ", ";
		$query .= $db->Quote($state) . ", " . $db->Quote($actorId) . ", " . $db->Quote($actorType) . ", " . $db->Quote($params) . ", " . $db->Quote($url) . ", ";
		$query .= $db->Quote($targetType) . ", x.`id` from (";
		$query .= "     select distinct b.`id`,";
		$query .= "         (select `system` from `#__social_alert_map` where `alert_id` = " . $db->Quote($ruleId);
		$query .= "              and `user_id` = a.`uid` union select `system` from `#__social_alert` where `id` = " . $db->Quote($ruleId) . " limit 1) as `notify_system`";
		$query .= "       from `#__social_clusters_nodes` as a";
		$query .= "       inner join `#__users` as b on a.`uid` = b.`id` and a.`type` = " . $db->Quote('user');

		$query .= "     where b.`block` = " . $db->quote(0);
		$query .= "     and a.`state` = " . $db->quote(1);

		if ($clusterId) {
			if (is_array($clusterId)) {
				$query .= "     and a.`cluster_id` IN (" . implode(',', $clusterId) . ")";
			} else {
				$query .= "     and a.`cluster_id` = " . $db->Quote($clusterId);
			}
		}

		if ($exclude) {
			if (is_array($exclude)) {
				$tmpString = implode(',', $exclude);
				$query .= " and b.id not in (" . $tmpString . ")";
			} else {
				$query .= " and b.id != " . $db->Quote($exclude);
			}
		}

		$query .= " ) as x";
		$query .= " where x.`notify_system` = " . $db->Quote('1');

		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

		return true;
	}

	/**
	 * Used to send notication as batch basis to all cluster's members
	 *
	 * @since	2.0.15
	 * @access	public
	 */
	public function sendProfileMembers($profileIds, $emailOptions = array(), $systemOptions = array(), $exclude = array())
	{
		if ($emailOptions !== false && $this->email) {
			$this->sendProfileMemberEmail($profileIds, $emailOptions, $exclude);
		}

		if ($systemOptions !== false && $this->system) {
			$this->sendProfileMemberSystem($profileIds, $systemOptions, $exclude);
		}

		return true;
	}


	/**
	 * Used to send email notication as batch basis to all profile's members
	 *
	 * @since 2.1.8
	 * @access  public
	 */
	public function sendProfileMemberEmail($profileIds = array(), $options = array(), $exclude = array())
	{
		if (is_object($options)) {
			$options = ES::makeArray($options);
		}

		// If params is not set, just give it an empty array
		if (!isset($options['params'])) {
			$options['params'] = array();
		} else {
			// If params is already set, it is possible that it might be object or registry object
			$options['params'] = ES::makeArray($options['params']);
		}

		// Assign any non-table key into params automatically
		$columns = ES::db()->getTableColumns('#__social_mailer');
		foreach ($options as $key => $val) {
			if (!in_array($key, $columns)) {
				$options['params'][$key] = $val;
			}
		}

		// Set default title if no title is passed in
		if (!isset($options['title'])) {
			$options['title'] = $this->getNotificationTitle('email');
		}

		// Set default template if no template is passed in
		if (!isset($options['template'])) {
			$options['template'] = $this->getMailTemplateName();
		}

		if (!isset($options['html'])) {
			$options['html'] = 1;
		}

		// Init a few default widely used parameter
		$user = ES::user();
		if (!isset($options['params']['actor'])) {
			$options['params']['actor'] = $user->getName();
		}

		if (!isset($options['params']['posterName'])) {
			$options['params']['posterName'] = $user->getName();
		}

		if (!isset($options['params']['posterAvatar'])) {
			$options['params']['posterAvatar'] = $user->getAvatar();
		}

		if (!isset($options['params']['posterLink'])) {
			$options['params']['posterLink'] = $user->getPermalink(true, true);
		}

		$params = $options['params'];
		if (is_object($params) && $params instanceof SocialRegistry) {
			$params = $params->toString();
		}

		// Convert params to json string
		if (is_object($params) || is_array($params)) {
			// Encode parameters to get the JSON string.
			$json = ES::json();
			$params = $json->encode($params);
		}

		$title = $options['title'];
		$template = $options['template'];
		$isHtml = $options['html'];

		$jConfig = ES::jconfig(); // joomla config
		$config = ES::config();

		$defaultFromName = $config->get('notifications.general.fromname', '');
		$defaultFromEmail = $config->get('notifications.general.fromemail', '');

		$defaultFromName = $defaultFromName ? $defaultFromName : $jConfig->getValue('fromname');
		$defaultFromEmail = $defaultFromEmail ? $defaultFromEmail : $jConfig->getValue('mailfrom');

		$senderName = isset($options['sender_name']) ? $options['sender_name'] : $defaultFromName;
		$senderEmail = isset($options['sender_email']) ? $options['sender_email'] : $defaultFromEmail;
		$replyToEmail = isset($options['replyto_email']) ? $options['replyto_email'] : $defaultFromEmail;
		$priority = isset($options['priority']) ? $options['priority'] : SOCIAL_MAILER_PRIORITY_NORMAL;
		$created = ES::date()->toSql();
		$state = SOCIAL_NOTIFICATION_STATE_UNREAD;
		$sid = isset($options['sid']) ? $options['sid'] : 0;

		// Prepare for schedule posts.
		$scheduled = isset($options['scheduled']) ? $options['scheduled'] : false;
		
		if ($scheduled) {
			$created = $scheduled;
			$state = SOCIAL_NOTIFICATION_STATE_SCHEDULED;
		}

		// since this is a batch sql, we can only get the forntend site language.
		$langCode = JFactory::getLanguage()->getTag();

		$db = ES::db();
		$sql = $db->sql();

		$ruleId = $this->id; // rule id

		$query = "insert into `#__social_mailer` (`sid`, `sender_name`, `sender_email`, `replyto_email`, `title`, `template`, `html`, `state`, `created`, `params`,";
		$query .= " `priority`, `language`, `recipient_name`, `recipient_email`) ";
		$query .= " select " . $db->Quote($sid) . ", " . $db->Quote($senderName) . ", " . $db->Quote($senderEmail) . ", " . $db->Quote($replyToEmail) . ", " . $db->Quote($title) . ", ";
		$query .= $db->Quote($template) . ", " . $db->Quote($isHtml) . ", " . $db->Quote($state) . ", " . $db->Quote($created) . ", " . $db->Quote($params) . ", ";
		$query .= $db->Quote($priority) . ", " . $db->Quote($langCode) . ", x.`name`, x.`email` from (";
		$query .= "		select b.`email`, b.`name`,";
		$query .= "			(select `email` from `#__social_alert_map` where `alert_id` = " . $db->Quote($ruleId);
		$query .= "				 and `user_id` = a.`user_id` union select `email` from `#__social_alert` where `id` = " . $db->Quote($ruleId) . " limit 1) as `notify_email`";

		$query .= "       from `#__social_profiles_maps` as a";
		$query .= "       inner join `#__users` as b on a.`user_id` = b.`id`";

		$query .= "     where b.`block` = " . $db->quote(0);
		$query .= "     and a.`state` = " . $db->quote(1);
		if ($profileIds) {
			$query .= "     and a.`profile_id` IN (" . implode(',', $profileIds) . ")";
		}

		if ($exclude) {
			if (is_array($exclude)) {
				$tmpString = implode(',', $exclude);
				$query .= " and b.id not in (" . $tmpString . ")";
			} else {
				$query .= " and b.id != " . $db->Quote($exclude);
			}
		}

		$query .= " ) as x";
		$query .= " where x.`notify_email` = " . $db->Quote('1');

		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		return true;
	}

	/**
	 * Used to send system notication as batch basis to all cluster's members
	 *
	 * @since	2.1.8
	 * @access	public
	 */
	public function sendProfileMemberSystem($profileIds, $options = array(), $exclude = array())
	{
		if (is_object($options)) {
			$options = ES::makeArray($options);
		}

		// If params is not set, just give it an empty array
		if (!isset($options['params'])) {
			$options['params'] = array();
		}

		// Assign any non-table key into params automatically
		$columns = ES::db()->getTableColumns('#__social_notifications');
		foreach ($options as $key => $val) {
			if (!in_array($key, $columns)) {
				$options['params'][$key] = $val;
			}
		}

		if (!isset($options['uid'])) {
			$options['uid'] = 0;
		}

		if (!isset($options['type'])) {
			$options['type'] = $this->element;
		}

		if (!isset($options['cmd'])) {
			$options['cmd'] = $options['type'] . '.' . $this->rule;
		}

		if (!isset($options['title'])) {
			$options['title'] = $this->getNotificationTitle('system');
		}

		if (!isset($options['actor_id'])) {
			$options['actor_id'] = ES::user()->id;
		}

		if (!isset($options['actor_type'])) {
			$options['actor_type'] = SOCIAL_TYPE_USER;
		}

		if (!isset($options['target_type'])) {
			$options['target_type'] = SOCIAL_TYPE_USER;
		}

		if (!isset($options['url'])) {
			$options['url'] = ES::getURI(true);
		}

		$uid = $options['uid'];
		$type = $options['type'];
		$cmd = $options['cmd'];
		$title = $options['title'];
		$actorId = $options['actor_id'];
		$actorType = $options['actor_type'];
		$targetType = $options['target_type'];
		$url = $options['url'];
		$params = ES::makeJSON($options['params']);

		$sid = isset($options['sid']) ? $options['sid'] : 0;
		$content = isset($options['content']) ? $options['content'] : '';
		$contextType = isset($options['context_type']) ? $options['context_type'] : '';
		$contextIds = isset($options['context_ids']) ? $options['context_ids'] : '';
		$image = isset($options['image']) ? $options['image'] : '';
		$created = ES::date()->toSql();
		$state = SOCIAL_NOTIFICATION_STATE_UNREAD;

		// Prepare for scheduled posts.
		$scheduled = isset($options['scheduled']) ? $options['scheduled'] : false;
		
		if ($scheduled) {
			$created = $scheduled;
			$state = SOCIAL_NOTIFICATION_STATE_SCHEDULED;
		}

		$db = ES::db();
		$sql = $db->sql();

		$ruleId = $this->id; // rule id

		$query = "insert into `#__social_notifications` (`uid`, `sid`, `type`, `context_ids`, `context_type`, `cmd`, `title`, `content`,";
		$query .= " `image`, `created`, `state`, `actor_id`, `actor_type`, `params`, `url`, `target_type`, `target_id`)";

		$query .= " select " . $db->Quote($uid) . ", " . $db->Quote($sid) . ", " . $db->Quote($type) . ", " . $db->Quote($contextIds) . ", " . $db->Quote($contextType) . ", ";
		$query .= $db->Quote($cmd) . ", " . $db->Quote($title) . ", " . $db->Quote($content) . ", " . $db->Quote($image) . ", " . $db->Quote($created) . ", ";
		$query .= $db->Quote($state) . ", " . $db->Quote($actorId) . ", " . $db->Quote($actorType) . ", " . $db->Quote($params) . ", " . $db->Quote($url) . ", ";
		$query .= $db->Quote($targetType) . ", x.`id` from (";
		$query .= "     select b.`id`,";
		$query .= "         (select `system` from `#__social_alert_map` where `alert_id` = " . $db->Quote($ruleId);
		$query .= "              and `user_id` = a.`user_id` union select `system` from `#__social_alert` where `id` = " . $db->Quote($ruleId) . " limit 1) as `notify_system`";

		$query .= "       from `#__social_profiles_maps` as a";
		$query .= "       inner join `#__users` as b on a.`user_id` = b.`id`";

		$query .= "     where b.`block` = " . $db->quote(0);
		$query .= "     and a.`state` = " . $db->quote(1);

		if ($profileIds) {
			$query .= "     and a.`profile_id` IN (" . implode(',', $profileIds) . ")";
		}

		if ($exclude) {
			if (is_array($exclude)) {
				$tmpString = implode(',', $exclude);
				$query .= " and b.id not in (" . $tmpString . ")";
			} else {
				$query .= " and b.id != " . $db->Quote($exclude);
			}
		}

		$query .= " ) as x";
		$query .= " where x.`notify_system` = " . $db->Quote('1');

		$sql->raw($query);

		$db->setQuery($sql);
		$db->query();

		return true;
	}


	/**
	 * Apps email title (assuming that app itself have already loaded the language file before calling this function)
	 * APP_{ELEMENT}_{RULENAME}_EMAIL_TITLE
	 *
	 * Apps email template namespace
	 * apps/{group}/{element}/alerts.{rulename}
	 *
	 * Apps email template path
	 * apps/{group}/{element}/themes/{default/themeName}/emails/{html/text}/alerts.{rulename}
	 *
	 * Core email title
	 * COM_EASYSOCIAL_{ELEMENT}_{RULENAME}_EMAIL_TITLE
	 *
	 * Core email template namespace
	 * site/{element}/alerts.{rulename}
	 *
	 * Core email template path
	 * site/themes/{wireframe/themeName}/emails/{html/text}/{element}/alert.{rulename}
	 *
	 * @since 1.0
	 * @access	public
	 */
	public function sendEmail($participants, $options = array())
	{
		$users	= $this->getUsers('email', $participants);

		if (empty($users)) {
			return true;
		}

		if (is_object($options)) {
			$options = ES::makeArray($options);
		}

		// If params is not set, just give it an empty array
		if (!isset($options['params'])) {
			$options['params'] = array();
		} else {
			// If params is already set, it is possible that it might be object or registry object
			$options['params'] = ES::makeArray($options['params']);
		}

		// Assign any non-table key into params automatically
		$columns = ES::db()->getTableColumns('#__social_mailer');

		foreach ($options as $key => $val) {
			if (!in_array($key, $columns)) {
				$options['params'][$key] = $val;
			}
		}

		// Set default title if no title is passed in
		if (!isset($options['title'])) {
			$options['title'] = $this->getNotificationTitle('email');
		}

		// Set default template if no template is passed in
		if (!isset($options['template'])) {
			$options['template'] = $this->getMailTemplateName();
		}

		if (!isset($options['html'])) {
			$options['html'] = 1;
		}

		$mailer = ES::mailer();

		$data = new SocialMailerData();

		$data->set('title', $options['title']);
		$data->set('template', $options['template']);
		$data->set('html', $options['html']);

		// Allow caller to send in contents if they wish to use contents over using a template.
		if (isset($options['content'])) {
			$data->setContent($options['content']);
		}

		if (isset($options['params'])) {
			$data->setParams($options['params']);
		}

		// If priority is set, set the priority
		if (isset($options['priority'])) {
			$data->set('priority' , $options['priority']);
		}

		if (isset($options['sender_name'])) {
			$data->set('sender_name', $options['sender_name']);
		}

		if (isset($options['sender_email'])) {
			$data->set('sender_email', $options['sender_email']);
		}

		if (isset($options['replyto_email'])) {
			$data->set('replyto_email', $options['replyto_email']);
		}

		if (isset($options['scheduled']) && $options['scheduled']) {
			$data->set('state', SOCIAL_NOTIFICATION_STATE_SCHEDULED);
		}

		if (isset($options['sid'])) {
			// We'll need to pass this so scheduled post can be handled properly.
			$data->sid = $options['sid'];
		}

		// The caller might be passing in 'params' as a SocialRegistry object. Just need to standardize them here.
		// Ensure that the params if set is a valid array
		if (isset($options['params']) && is_object($options['params'])) {
			$options['params']	= ES::makeArray($options['params']);
		}

		// Init a few default widely used parameter
		$user = ES::user();
		if (!isset($options['params']['actor'])) {
			$options['params']['actor'] = $user->getName();
		}

		if (!isset($options['params']['posterName'])) {
			$options['params']['posterName'] = $user->getName();
		}

		if (!isset($options['params']['posterAvatar'])) {
			$options['params']['posterAvatar'] = $user->getAvatar();
		}

		if (!isset($options['params']['posterLink'])) {
			$options['params']['posterLink'] = $user->getPermalink(true, true);
		}

		foreach ($users as $uid) {
			$user = ES::user($uid);

			// If user has been blocked, skip this altogether.
			if ($user->block) {
				continue;
			}

			// If user do not have community access, skip this altogether.
			if (!$user->hasCommunityAccess()) {
				continue;
			}

			// Get the params
			$params 	= $options['params'];

			// Set the language of the email
			$data->setLanguage($user->getLanguage());

			// Detect the "name" in the params. If it doesn't exist, set the target's name.
			if (is_array($params)) {
				if (!isset($params['recipientName'])) {
					$params['recipientName']	= $user->getName();
				}

				if (!isset($params['recipientAvatar'])) {
					$params['recipientAvatar'] 	= $user->getAvatar();
				}

				$data->setParams($params);
			}

			$data->set('recipient_name', $user->getName());
			$data->set('recipient_email', $user->email);

			$mailer->create($data);
		}

		return true;
	}

	/**
	 * Apps system title (assuming that app itself have already loaded the language file before calling this function)
	 * APP_ELEMENT_RULENAME_EMAIL_TITLE
	 *
	 * Core system title
	 * COM_EASYSOCIAL_ELEMENT_RULENAME_SYSTEM_TITLE
	 *
	 * @since 1.0
	 * @access	public
	 */
	public function sendSystem($participants, $options = array())
	{
		$users = $this->getUsers('system', $participants);

		if (empty($users)) {
			return false;
		}

		if (is_object($options)) {
			$options = ES::makeArray($options);
		}

		// If params is not set, just give it an empty array
		if (!isset($options['params'])) {
			$options['params']	= array();
		}

		// Assign any non-table key into params automatically
		$columns = ES::db()->getTableColumns('#__social_notifications');
		foreach ($options as $key => $val) {
			if (!in_array($key, $columns)) {
				$options['params'][$key] = $val;
			}
		}

		if (!isset($options['uid'])) {
			$options['uid'] = 0;
		}

		if (!isset($options['type'])) {
			$options['type'] = $this->element;
		}

		if (!isset($options['cmd'])) {
			$options['cmd'] = $options['type'] . '.' . $this->rule;
		}

		if (!isset($options['title'])) {
			$options['title'] = $this->getNotificationTitle('system');
		}

		if (!isset($options['actor_id'])) {
			$options['actor_id'] = ES::user()->id;
		}

		if (!isset($options['actor_type'])) {
			$options['actor_type'] = SOCIAL_TYPE_USER;
		}

		if (!isset($options['target_type'])) {
			$options['target_type'] = SOCIAL_TYPE_USER;
		}

		if (!isset($options['url'])) {
			$options['url']	= ES::getURI(true);
		}

		$notification = ES::notification();
		$data = $notification->getTemplate();

		$data->setObject($options['uid'], $options['type'], $options['cmd']);
		$data->setTitle($options['title']);

		// Only bind content if it's being set
		if (isset($options['content'])) {
			$data->setContent($options['content']);
		}

		// Determines if caller wants aggregation to happen for this system notifications.
		if (isset($options['aggregate']) && $options['aggregate']) {
			$data->setAggregation();
		}

		// Determines if the app wants to set a context_type
		if (isset($options['context_type'])) {
			$data->setContextType($options['context_type']);
		}

		// Determines if the app wants to set a context_type
		if (isset($options['context_ids'])) {
			$data->setContextId($options['context_ids']);
		}

		if (isset($options['actor_id'])) {
			$data->setActor($options['actor_id'], $options['actor_type']);
		}

		if (isset($options['image'])) {
			$data->setImage($options['image']);
		}

		if (isset($options['params'])) {
			$data->setParams(ES::makeJSON($options['params']));
		}

		if (isset($options['url'])) {
			$data->setUrl($options['url']);
		}

		// Prepare for scheduled notification.
		if (isset($options['scheduled']) && $options['scheduled']) {
			$data->state = SOCIAL_NOTIFICATION_STATE_SCHEDULED;
		}

		// We'll need to pass this sid so scheduled notification can be processed properly.
		if (isset($options['sid'])) {
			$data->sid = $options['sid'];
		}

		foreach ($users as $uid) {
			if (!empty($uid)) {
				$data->setTarget($uid, $options['target_type']);

				$notification->create($data);
			}
		}

		return true;
	}

	public function getApp()
	{
		static $app = array();

		if ($this->app == 0) {
			return false;
		}

		if (!isset($app[$this->element])) {
			$table = ES::table('app');
			$state = $table->load(array('element' => $this->element, 'group' => $this->group, 'type' => SOCIAL_APPS_TYPE_APPS));

			if (!$state) {
				$app[$this->element] = false;
			}
			else {
				$app[$this->element] = $table;
			}
		}

		return $app[$this->element];
	}

	private function formatId($participants)
	{
		$users = array();

		if ($participants) {
			foreach ($participants as $user) {
				if (is_object($user)) {
					$users[] = $user->id;
				}

				if (is_string($user) || is_int($user)) {
					$users[] = $user;
				}
			}
		}

		return $users;
	}

	/**
	 * Used in REST api to export alert data.
	 *
	 * @since	3.2.1
	 * @access	public
	 */
	public function toExportData()
	{
		$properties = get_object_vars($this);
		$data = array();

		foreach ($properties as $key => $value) {
			if ($key[0] != '_') {
				$data[$key] = $value;
			}

			$data['title'] = $this->getTitle();
			$data['description'] = $this->getDescription();

			// dump($this->published);

			//unset unnessary data.
			unset($data['created']);
			unset($data['users']);
			unset($data['published']);
			unset($data['privacy']);
		}

		$data = ES::makeObject($data);
		return $data;
	}
}
