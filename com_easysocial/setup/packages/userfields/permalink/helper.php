<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2020 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialFieldsUserPermalinkHelper
{
	/**
	 * Ensures that the user doesn't try to use a permalink from a menu alias
	 *
	 * @since	1.4
	 * @access	public
	 */
	public static function allowed($permalink)
	{
		$jConfig = ES::jConfig();

		// If sef isn't enabled, we shouldn't really need to worry about this.
		if (!$jConfig->getValue('sef')) {
			return true;
		}

		// Find any menu alias on the site which uses similar alias
		if (self::menuAliasExists($permalink)) {
			return false;
		}

		// check if value conflicts with system view alias or not.
		$sysViews = FRoute::getSystemViews();
		if ($permalink && in_array($permalink, $sysViews)) {
			return false;
		}

		return true;
	}

	public static function menuAliasExists($permalink)
	{
		$db = ES::db();
		$query = $db->sql();

		$query->select('#__menu');
		$query->column('COUNT(1)');
		$query->where('client_id', 0);
		$query->where('published', 1);
		$query->where('alias', $permalink);

		$db->setQuery($query);
		$exists = $db->loadResult() > 0 ? true : false;

		return $exists;
	}

	/**
	 * Determines if the permalink is a valid permalink
	 *
	 * @since	1.4
	 * @access	public
	 */
	public static function valid($permalink, $params)
	{
		$invalid = preg_match("#[<>\"'%;()\!&_ @\.]#i", $permalink);

		if (!$permalink || $invalid) {
			return false;
		}

		// Get a list of forbidden permalinks
		$forbidden = $params->get('forbidden');

		if (!$forbidden) {
			return true;
		}

		$words = explode(',', $forbidden);

		// Trim the forbidden words
		foreach ($words as $word) {
			$word = ESJString::trim($word);

			if ($word && ESJString::stristr($permalink, $word) !== false) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Ensure that permalink is unique
	 *
	 * @since	3.2.2
	 * @access	public
	 */
	public static function exists($permalink, $current = '', $currentUserId = '')
	{
		$db = ES::db();

		$safePermalink = JFilterOutput::stringURLSafe($permalink);

		$query = "SELECT `user_id` AS `id` FROM " . $db->nameQuote('#__social_users');
		$query .= " WHERE (`permalink` = " . $db->Quote($safePermalink);
		$query .= " OR `alias` = " . $db->Quote($safePermalink) . ")";

		if ($currentUserId) {
			// if current logged in user id supply, we will use it.
			$query .= " AND `user_id` != " . $db->Quote($currentUserId);

		} else if ($current) {
			$query .= " AND `permalink` != " . $db->Quote($current);
		}

		$query .= " UNION ALL";
		$query .= " SELECT `id` FROM " . $db->nameQuote('#__social_clusters');
		$query .= " WHERE `alias` = " . $db->Quote($safePermalink);

		$db->setQuery($query);
		$result = $db->loadResult();

		if ($result > 0) {
			return true;
		}

		// Do not allow them to use any "views"
		$views = JFolder::folders(JPATH_ROOT . '/components/com_easysocial/views');

		if (in_array($permalink, $views)) {
			return true;
		}

		return false;
	}
}
