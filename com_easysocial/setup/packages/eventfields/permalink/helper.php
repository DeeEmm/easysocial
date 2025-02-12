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

class SocialFieldsEventPermalinkHelper
{
    /**
     * Ensures that the user doesn't try to use a permalink from a menu alias
     *
     * @since   1.4
     * @access  public
     */
    public static function allowed($permalink)
    {
        // check if value conflicts with system view alias or not.
        $sysViews = FRoute::getSystemViews();
        if ($permalink && in_array($permalink, $sysViews)) {
            return false;
        }

        return true;
    }

    public static function valid($permalink, $params)
    {
        if (empty($permalink) || preg_match("#[<>\"'%;()\!&_@\.]#i", $permalink)) {
            return false;
        }

        $forbidden = $params->get('forbidden');

        if (!empty($forbidden)) {
            $words = explode(',', $forbidden);

            foreach ($words as $word) {
                $word = trim($word);

                if (ESJString::stristr($permalink, $word) !== false) {
                    return false;
                }
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
    public static function exists($permalink)
    {
        $db = ES::db();

		$query = "SELECT `id` FROM " . $db->nameQuote('#__social_clusters');
		$query .= " WHERE `alias` = " . $db->Quote($permalink);
		$query .= " AND `cluster_type` = " . $db->Quote(SOCIAL_TYPE_EVENT);

		$query .= " UNION ALL";

		$query .= " SELECT `user_id` AS `id` FROM " . $db->nameQuote('#__social_users');
		$query .= " WHERE (`permalink` = " . $db->Quote($permalink);
		$query .= " OR `alias` = " . $db->Quote($permalink) . ")";

		$db->setQuery($query);
		$result = $db->loadResult();

		if ($result > 0) {
			return true;
		}

        // Do not allow them to use any "views"
        $views = JFolder::folders(JPATH_ROOT . '/components/com_easysocial/views');

        if (in_array($permalink , $views)) {
            return true;
        }

        return false;
    }
}
