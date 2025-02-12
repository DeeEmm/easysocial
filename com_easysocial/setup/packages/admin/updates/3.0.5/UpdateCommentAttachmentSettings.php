<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptUpdateCommentAttachmentSettings extends SocialMaintenanceScript
{
	public static $title = 'Update Comment Attachment Settings.';
	public static $description = 'Standardise the comment attachment setting with maxsize into config table.';

	public function main()
	{
		$config = ES::config();

		// Retrieve the current comment attachmnent setting on the site
		$commentAttachmentEnabled = $config->get('comments.attachments');

		// there got some possibility the value is integer if the user already save the setting before
		if (is_string($commentAttachmentEnabled)) {
			// Convert to boolean type
			$commentAttachmentEnabled = (bool) $commentAttachmentEnabled;
		}

		// If this config value is not boolean, we know this is fresh install.
		// If this config value is object, mean it already stored the respected value so do not need to procceed this script.
		if (!is_bool($commentAttachmentEnabled)) {
			return true;
		}

		$db = ES::db();

		$query = "SELECT `value` FROM " . $db->nameQuote('#__social_config');
		$query .= " WHERE " . $db->nameQuote('type') . " = " . $db->Quote('site');

		$db->setQuery($query);
		$result = $db->loadResult();

		if ($result) {
			$obj = ES::makeObject($result);

			// standardise the new setting key with the maxsize and comment attachment
			$obj->comments->attachments = new stdClass;
			$obj->comments->attachments->enabled = $commentAttachmentEnabled;
			$obj->comments->attachments->maxsize = 20;

			$jsonString = ES::makeJSON($obj);

			$query = "UPDATE " . $db->nameQuote('#__social_config');
			$query .= " SET " . $db->nameQuote('value') . " = " . $db->Quote($jsonString);
			$query .= " WHERE " . $db->nameQuote('type') . " = " . $db->Quote('site');

			$db->setQuery($query);
			$db->query();
		}

		return true;
	}
}
