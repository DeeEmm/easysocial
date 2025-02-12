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

class SocialException extends Exception
{
	private static $codeMap = [
		ES_ERROR => 400,
		SOCIAL_MSG_SUCCESS => 200,
		SOCIAL_MSG_WARNING => 200,
		SOCIAL_MSG_INFO => 100
	];

	public function __construct($message, $type = ES_ERROR, $previous = null)
	{
		if (is_integer($type)) {
			$code = $type;
		}

		if (is_string($type)) {
			$code = isset(self::$codeMap[$type]) ? self::$codeMap[$type] : null;
		}

		if (is_array($type)) {
			$code = $type[0];
		}

		$message = JText::_($message);

		// Construct exception so we can retrieve the rest of the properties
		parent::__construct($message, $code, $previous);
	}
}
