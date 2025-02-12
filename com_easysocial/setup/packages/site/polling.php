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
require_once(dirname(dirname(__DIR__)) . '/configuration.php');

if (!function_exists('dump')) {
	function dump()
	{
		$args = func_get_args();

		echo '<pre>';

		foreach ($args as $arg) {
			var_dump($arg);
		}
		echo '</pre>';
		exit;
	}
}

class EasySocialPolling
{
	private $jconfig = null;
	private $config = null;
	private $dbo = null;

	public function __construct()
	{
		$this->jconfig = $this->getJoomlaConfig();
		$this->dbo = $this->createDBConnection();
		$this->config = $this->getEasySocialConfig();

		$this->userId = (int) isset($_REQUEST['userId']) ? $_REQUEST['userId'] : 0;
		$this->mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
	}

	public function __destruct()
	{
		if (!$this->dbo) {
			return;
		}

		if ($this->jconfig->dbtype == 'mysqli') {
			mysqli_close($this->dbo);
		}

		if ($this->jconfig->dbtype == 'mysql'){
			mysql_close($this->dbo);
		}

		return;
	}

	/**
	 * Creates a new connection to the database
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function createDBConnection()
	{
		if ($this->jconfig->dbtype == 'mysqli') {
			$connection = mysqli_connect($this->jconfig->host, $this->jconfig->user, $this->jconfig->password, $this->jconfig->db);

			// Check connection
			if (mysqli_connect_errno()) {
				die('Unable to connect to database');
			}

			return $connection;
		}


		$connection = mysql_connect($this->jconfig->host, $this->jconfig->user, $this->jconfig->password);

		if (!$connection) {
			die('Unable to connect to database');
		}

		if (!mysql_select_db($this->jconfig->db)) {
			die('Unable to select database');
		}

		return $connection;
	}

	/**
	 * Escapes a text
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function escape($value)
	{
		if ($this->jconfig->dbtype == 'mysqli') {
			$escaped = '"' . mysqli_real_escape_string($this->dbo, $value) . '"';

			return $escaped;
		}

		$escaped = '\'' . mysql_real_escape_string($value) . '\'';
		return $escaped;
	}

	/**
	 * Get configuration from Joomla
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getJoomlaConfig()
	{
		$config = new JConfig();

		return $config;
	}

	/**
	 * Get configuration from EasySocial
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getEasySocialConfig()
	{
		static $config = null;

		if (is_null($config)) {

			$query = array();
			$query[] = 'SELECT `value` FROM `#__social_config` WHERE `type`=' . $this->escape('site');

			$result = $this->query($query);

			$config = json_decode($result['value']);
		}

		return $config;
	}

	/**
	 * Main execution method
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function execute()
	{
		$method = @$_REQUEST['method'];

		if (!$method) {
			throw new Exception('Invalid method');
		}

		$allowed = array('notifier', 'typing', 'typingState', 'checkConversationUpdate');

		if (!in_array($method, $allowed)) {
			throw new Exception('Invalid method provided');
		}

		if ($this->mode && $this->mode == 'SSE') {
			$method = $method . 'SSE';
		}

		$this->$method();
	}

	/**
	 * Check for any update on specified conversation
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function checkConversationUpdate()
	{
		$this->verify();

		$conversationId = (int) @$_REQUEST['conversationId'];
		$lastUpdate = urldecode(@$_REQUEST['lastUpdate']);

		// If conversations has been disabled, we should skip this
		if (!$this->config->conversations->enabled) {
			return $this->output(array('state' => 500, 'message' => 'Conversations disabled'));
		}

		if (!$conversationId) {
			return $this->output(array('state' => 500, 'message' => 'Invalid conversation id'));
		}

		$hasNew = $this->checkNewMessages($conversationId, $lastUpdate);
		$deletedMessages = $this->checkDeletedMessages($conversationId, $lastUpdate);
		$hasDeleted = $deletedMessages ? true : false;

		// New last updated timestamp
		$timestamp = gmdate('Y-m-d H:i:s');

		$output = array(
			'state' => 200,
			'timestamp' => $timestamp,
			'hasNew' => $hasNew,
			'hasDeleted' => $hasDeleted,
			'deletedMessages' => $deletedMessages
		);

		return $this->output($output);
	}

	/**
	 * Checks for new messages in a conversation
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function checkNewMessages($conversationId, $lastUpdate)
	{
		$this->verify();

		$result = $this->getNewMessages($conversationId, $lastUpdate);
		$hasNew = $result ? true : false;

		return $hasNew;
	}

	/**
	 * Checks for live deleted messages in conversations
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function checkDeletedMessages($conversationId, $lastUpdate)
	{
		$this->verify();

		$result = $this->getDeletedMessages($conversationId, $lastUpdate);
		$result = $result ? $result : false;

		return $result;
	}

	/**
	 * Checks for typing states
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function typing()
	{
		// Verify the user's request
		$this->verify();

		// If conversations has been disabled, we should skip this
		if (!$this->config->conversations->enabled) {
			return $this->output(array('state' => 500, 'message' => 'Conversations disabled'));
		}

		// typing / stop
		$typing = @$_REQUEST['typing'];
		$conversationId = (int) @$_REQUEST['conversationId'];

		if (!$conversationId) {
			return $this->output(array('state' => 500, 'message' => 'Invalid conversation id'));
		}

		// Store this data somewhere.
		$query = array();
		$query[] = 'UPDATE `#__social_conversations_participants` SET `typing`=' . $this->escape($typing);
		$query[] = 'WHERE `user_id`=' . $this->escape($this->userId);
		$query[] = 'AND `conversation_id`=' . $this->escape($conversationId);

		$this->query($query);

		return $this->output(array('state' => 200));
	}

	/**
	 * Checks for typing states
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function typingState()
	{
		// Verify the user's request
		$this->verify();

		// If conversations has been disabled, we should skip this
		if (!$this->config->conversations->enabled) {
			return $this->output(array('state' => 500, 'message' => 'Conversations disabled'));
		}

		// typing / stop
		$state = @$_REQUEST['state'];
		$conversationId = (int) @$_REQUEST['conversationId'];

		if (!$conversationId) {
			return $this->output(array('state' => 500, 'message' => 'Invalid conversation id'));
		}

		// Determines if anyone is typing under this conversation except for the current viewer
		$query = array();
		$query[] = 'SELECT `user_id` FROM `#__social_conversations_participants`';
		$query[] = 'WHERE `conversation_id`=' . $this->escape($conversationId);
		$query[] = 'AND `typing`=' . $this->escape(1);
		$query[] = 'AND `user_id` !=' . $this->escape($this->userId);

		$result = $this->query($query);

		if (!$result) {
			return $this->output(array('state' => 200, 'users' => null));
		}

		$users = array();

		foreach ($result as $userId) {
			$userId = (int) $userId;

			$users[] = $userId;
		}

		$names = $this->getNames($users);

		return $this->output(array('state' => 200, 'users' => $names));
	}

	/**
	 * Checks for notification counters
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function notifier()
	{
		$post = @$_POST['data'];

		$data = new stdClass();

		// Poll for new system notifications
		$this->getSystemNotifications($data);

		// Poll for new friend notifications
		$this->getFriendNotifications($data);

		// Poll for new conversations
		$this->getConversationNotifications($data);

		$this->output($data);
	}

	/**
	 * Checks for notification counters with SSE method
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function notifierSSE()
	{
		header("Content-Type: text/event-stream");
		header("Cache-Control: no-cache");
		header("X-Accel-Buffering: no");

		while (true) {

			$data = new stdClass();

			// Poll for new system notifications
			$this->getSystemNotifications($data);

			// Poll for new friend notifications
			$this->getFriendNotifications($data);

			// Poll for new conversations
			$this->getConversationNotifications($data);

			ob_start();
?>

<?php if (is_object($data) || is_array($data)) { ?>
data: <?php echo json_encode($data); ?>
<?php } else { ?>
data: <?php echo $data; ?>
<?php } ?>

<?php // Required to fulfill minimum buffer size on certain server. #4181 from ES ?>
buffer: <?php echo str_repeat(' ', 1024 * 64); ?>
<?php
			$contents = ob_get_contents();

			ob_end_clean();

			echo $contents;
			echo "\n\n";

			@ob_end_flush();
			@flush();

			// Some 3rd party plugin could block multiple request on same session. #4181 from ES
			// Refresh the session for each loop. #4181
			session_write_close();

			usleep(1000000);
		}

		exit;
	}

	/**
	 * Renders the output
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function output($data)
	{
		header('Content-type: application/json; UTF-8');

		echo json_encode($data);
		exit;
	}

	/**
	 * Create standard info object
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function createInfo()
	{
		$info = new stdClass();
		$info->total = -1;
		$info->data = '';

		return $info;
	}

	/**
	 * Given the last update time, check for new messages that the user hasn't seen yet
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getNewMessages($conversationId, $lastUpdate)
	{

		$query = array();
		$query[] = 'SELECT a.`id`';
		$query[] = 'FROM `#__social_conversations_message` AS a';
		$query[] = 'INNER JOIN `#__social_conversations_message_maps` AS b';
		$query[] = 'ON b.`message_id` = a.`id`';

		if ($this->config->users->blocking->enabled) {
			$query[] = ' LEFT JOIN `#__social_block_users` as bus';

			$query[] = ' ON (';
			$query[] = ' a.`created_by` = bus.`user_id`';
			$query[] = ' AND bus.`target_id` = ' . $this->escape($this->userId);
			$query[] = ') OR (';
			$query[] = ' a.`created_by` = bus.`target_id`';
			$query[] = ' AND bus.`user_id` = ' . $this->escape($this->userId) ;
			$query[] = ')';


		}

		$query[] = 'WHERE a.`conversation_id`=' . $this->escape((int) $conversationId);
		$query[] = 'AND a.`created` > ' . $this->escape($lastUpdate);
		$query[] = 'AND b.`user_id` = ' . $this->escape($this->userId);

		// Exclude current user to avoid duplicate message after reply.
		$query[] = 'AND a.`created_by` != ' . $this->escape($this->userId);

		if ($this->config->users->blocking->enabled) {
			$query[] = ' AND bus.`id` IS NULL';
		}

		$query[] = 'ORDER BY a.`created` ASC';

		$result = $this->query($query);

		return $result;
	}

	/**
	 * Check for new deleted message based on the given last update time.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getDeletedMessages($conversationId, $lastUpdate)
	{
		$query = array();
		$query[] = 'SELECT `message_id`';
		$query[] = 'FROM `#__social_conversations_message_deleted`';

		$query[] = 'WHERE `conversation_id` = ' . $this->escape((int) $conversationId);
		$query[] = 'AND `created` > ' . $this->escape($lastUpdate);
		$query[] = 'AND `user_id` = ' . $this->escape($this->userId);

		$query[] = 'ORDER BY `created` ASC';

		$result = $this->query($query);

		return $result;
	}

	/**
	 * Get conversations counter
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	private function getConversationNotifications(&$data)
	{
		if (!$this->config->notifications->conversation->enabled) {
			return;
		}

		$userId = $this->userId;

		$query = array();
		$query[] = 'select count(distinct a.id) AS `count`';
		$query[] = 'from `#__social_conversations` as a';
		$query[] = '	inner join `#__social_conversations_participants` as parti on parti.`conversation_id` = a.`id` and parti.`user_id` = ' . $this->escape($userId);
		$query[] = '	inner join `#__social_conversations_message` as b on a.`id` = b.`conversation_id`';
		$query[] = '	inner join `#__social_conversations_message_maps` as c on c.`message_id` = b.`id`';

		// We should not show the banned user's conversation #3683
		$query[] = 'INNER JOIN `#__users` AS `uu` ON `b`.`created_by` = `uu`.`id` and `uu`.`block` = ' . $this->escape(0);

		if ($this->config->users->blocking->enabled) {
			$query[] = '	LEFT JOIN `#__social_block_users` AS `bus`';
			$query[] = '		ON (a.`created_by` = bus.`user_id` AND bus.`target_id` = ' . $this->escape($userId);
			$query[] = '			OR a.`created_by` = bus.`target_id` AND bus.`user_id` = ' . $this->escape($userId) .')';
		}

		$query[] = 'where c.`user_id` = ' . $this->escape($userId);
		$query[] = 'and c.`state` = 1';
		$query[] = 'and c.`isread` = 0';

		if ($this->config->users->blocking->enabled) {
			$query[] = ' AND bus.`id` IS NULL';
		}

		$result = $this->query($query);
		$total = (int) $result['count'];

		$data->conversation = $this->createInfo();
		$data->conversation->total = $total;
	}

	/**
	 * Retrieves the name of a user
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getNames($users = array())
	{
		$column = $this->config->users->displayName == 'realname' ? 'name' : 'username';

		$query = array();
		$query[] = 'SELECT `' . $column . '` as `name` FROM `#__users` WHERE `id` IN(' . $this->escape(implode(' ', $users)) . ')';
		$names = $this->query($query);

		return $names;
	}

	/**
	 * Get notification counter for system notifications
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	private function getSystemNotifications(&$data)
	{
		if (!$this->config->notifications->system->enabled) {
			return;
		}

		$data->system = $this->createInfo();

		$query = array();
		$query[] = 'SELECT COUNT(1) AS `count` FROM `#__social_notifications` AS a';

		// We should not count banned user's notification #3683
		$query[] = ' INNER JOIN `#__users` AS u ON a.`actor_id` = u.`id` AND u.`block` = ' . $this->escape(0);

		if ($this->config->users->blocking->enabled) {
			$query[] = 'LEFT JOIN `#__social_block_users` AS `bus`';
			$query[] = ' ON (';
			$query[] = ' a.`actor_id` = bus.`user_id`';
			$query[] = ' AND bus.`target_id` = ' . $this->escape($this->userId);
			$query[] = ') OR (';
			$query[] = ' a.`actor_id` = bus.`target_id`';
			$query[] = ' AND bus.`user_id` = ' . $this->escape($this->userId) ;
			$query[] = ')';
		}

		$query[] = 'WHERE a.`target_id`=' . $this->escape($this->userId);
		$query[] = 'AND a.`target_type`=' . $this->escape('user');
		$query[] = 'AND a.`state`=' . $this->escape(0);

		if (!$this->config->badges->enabled) {
			$query[] = 'AND a.`type` != ' . $this->escape('badges');
		}

		$result = $this->query($query);
		$total = (int) $result['count'];

		$data->system->total = $total;
	}

	/**
	 * Get notification counter for friends notifications
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	private function getFriendNotifications(&$data)
	{
		if (!$this->config->notifications->friends->enabled) {
			return;
		}

		$query = array();
		$query[] = 'SELECT COUNT(1) AS `count` FROM `#__social_friends` AS a';

		$query[] = 'INNER JOIN `#__users` AS uu';
		$query[] = 'ON uu.`id` = a.`actor_id`';

		// Exclude esad users
		$query[] = 'INNER JOIN `#__social_profiles_maps` AS upm';
		$query[] = 'ON uu.`id` = upm.`user_id`';

		$query[] = 'INNER JOIN `#__social_profiles` AS up';
		$query[] = 'ON upm.`profile_id` = up.`id`';
		$query[] = 'AND up.`community_access`=' . $this->escape(1);

		if ($this->config->users->blocking->enabled) {
			$query[] = 'LEFT JOIN `#__social_block_users` AS bus';

			$query[] = ' ON (';
			$query[] = ' uu.`id` = bus.`user_id`';
			$query[] = ' AND bus.`target_id` = ' . $this->escape($this->userId);
			$query[] = ') OR (';
			$query[] = ' uu.`id` = bus.`target_id`';
			$query[] = ' AND bus.`user_id` = ' . $this->escape($this->userId) ;
			$query[] = ')';

		}

		$query[] = 'WHERE uu.`block`=' . $this->escape(0);

		if ($this->config->users->blocking->enabled) {
			$query[] = 'AND bus.`id` IS NULL';
		}

		$query[] = 'AND a.`target_id`=' . $this->escape($this->userId);
		$query[] = 'AND a.`state`=' . $this->escape(-1);

		$result = $this->query($query);
		$total = (int) $result['count'];

		$data->friend = $this->createInfo();
		$data->friend->total = $total;
	}

	/**
	 * Executes query based on mysql adapter
	 *
	 * @since	2.1.0
	 * @access	public
	 *
	*/
	public function query($query)
	{
		if (is_array($query)) {
			$query = implode(' ', $query);
		}

		// Replace the prefixes
		$query = str_ireplace('#__', $this->jconfig->dbprefix, $query);

		$result = array();

		if ($this->jconfig->dbtype == 'mysqli') {
			$response = $this->dbo->query($query);

			if ($response === false) {
				echo $query;exit;
				throw new Exception('Invalid sql query');
			}

			if (is_bool($response)) {
				return $response;
			}

			$result = mysqli_fetch_assoc($response);
			return $result;
		}

		// Otherwise we assume that it is mysql
		$response = mysql_query($query);

		if ($response === false) {
			echo $query;exit;
			throw new Exception('Invalid sql query');
		}

		if (is_bool($response)) {
			return $response;
		}

		$result = mysql_fetch_assoc($response);
		return $result;
	}

	/**
	 * Verify a user's hash request
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function verify()
	{
		$key = @$_REQUEST['key'];

		if (!$key) {
			return $this->output(array('state' => 500, 'message' => 'Invalid Request'));
		}

		$query = array();
		$query[] = 'SELECT `email`, `password` FROM `#__users` WHERE `id`=' . $this->escape($this->userId);
		$user = $this->query($query);

		if (!$user) {
			return $this->output(array('state' => 500, 'message' => 'Invalid Request'));
		}

		$hash = md5($user['email'] . $this->jconfig->secret . $user['password']);

		if ($key != $hash) {
			return $this->output(array('state' => 500, 'message' => 'Invalid Request'));
		}

		return true;
	}
}

$lib = new EasySocialPolling();
$lib->execute();
