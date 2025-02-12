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

class SocialTranslations
{
	/**
	 * Translate a given wall of text
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string
	 * @return	
	 */
	public function translate($contents, $targetLanguage)
	{
		// Currently we only support bing
		require_once(__DIR__ . '/adapters/azure.php');

		$adapter = new SocialTranslationsAzure();

		$output = $adapter->translate($contents, $targetLanguage);
		
		return $output;
	}
}
