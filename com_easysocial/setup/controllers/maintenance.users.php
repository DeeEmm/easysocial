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

require_once(__DIR__ . '/controller.php');

class EasySocialControllerMaintenanceUsers extends EasySocialSetupController
{
	public $limit = 100;

	public function __construct()
	{
		parent::__construct();

		$this->engine();
	}

	/**
	 * Synchronize users on the site
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function sync()
	{
		// Fetch first $limit items to be processed.
		$db = ES::db();
		$query = array();

		$query[] = 'SELECT a.' . $db->nameQuote( 'id' ) . ' FROM ' . $db->nameQuote( '#__users' ) . ' AS a';
		$query[] = 'WHERE a.' . $db->nameQuote( 'id' ) . ' NOT IN( SELECT b.' . $db->nameQuote( 'user_id' ) . ' FROM ' . $db->nameQuote( '#__social_users' ) . ' AS b )';
		$query[] = 'LIMIT 0,' . $this->limit;

		$db->setQuery($query);
		$items = $db->loadObjectList();

		$totalItems = count($items);

		// Nothing to process here.
		if (!$items) {

			$result = new stdClass();
			$result->state 	= 1;

			$result = $this->getResultObj('Great! No users on the site that needs to be updated.', 1, 'success');
			return $this->output($result);
		}

		// Initialize all these users.
		$users = ES::user($items);

		// we need to sync the user into indexer
		foreach ($users as $user) {
			$indexer = ES::get('Indexer');

			$contentSnapshot = array();
			$contentSnapshot[] = $user->getName('realname');

			$idxTemplate = $indexer->getTemplate();

			$content = implode( ' ', $contentSnapshot );
			$idxTemplate->setContent( $user->getName( 'realname' ), $content );

			$url = ''; //FRoute::_( 'index.php?option=com_easysocial&view=profile&id=' . $user->id );
			$idxTemplate->setSource($user->id, SOCIAL_INDEXER_TYPE_USERS, $user->id, $url);

			$date = ES::date();
			$idxTemplate->setLastUpdate( $date->toMySQL() );

			$indexer->index( $idxTemplate );
		}

		// Detect if there are any more records.
		$query = array();
		$query[] = 'SELECT COUNT(1) FROM ' . $db->nameQuote( '#__users' ) . ' AS a';
		$query[] = 'WHERE a.' . $db->nameQuote( 'id' ) . ' NOT IN( SELECT b.' . $db->nameQuote( 'user_id' ) . ' FROM ' . $db->nameQuote( '#__social_users' ) . ' AS b )';

		$db->setQuery($query);
		$total = $db->loadResult();
		$result = $this->getResultObj(JText::sprintf('Synchronized %1s users on the site.', $totalItems), 2, 'success');

		return $this->output($result);
	}

	/**
	 * Retrieves the total number of users that needs to be synchronized with EasySocial
	 * as they do not have a record in `#__social_users`
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function getTotal()
	{
		$db = ES::db();

		$query = "select count(1) from `#__users` as a";
		$query .= " where not exists (select id from `#__social_users` as b where b.`user_id` = a.`id`)";

		$db->setQuery($query);

		$total = $db->loadResult();

		if ($total > $this->limit) {
			$total = ceil($total / $this->limit);
		}

		return $this->output($total);
	}
}
