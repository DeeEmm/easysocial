<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptFixConversekitIntegration extends SocialMaintenanceScript
{
	public static $title = 'Fix integration with Conversekit 1.1.2 version';
	public static $description = 'Fix integration with Conversekit 1.1.2 version';

	public function main()
	{
		$ckVersion = '1.1.3';
		$process = false;

		// check if CK installed or not.
		$manifestPath = JPATH_ROOT . '/plugins/system/conversekit/conversekit.xml';

		if (JFile::exists($manifestPath)) {
			$xml = ES::get('Parser')->load($manifestPath);
			$currentVersion = $xml->xpath('version');
			$currentVersion = (string) $currentVersion[0];

			if (version_compare($currentVersion, $ckVersion, '<')) {
				$process = true;
			}
		}

		if ($process) {
			$newfilePath = rtrim(dirname(__FILE__), '/') . '/conversekit.patch';
			$ckFilePath = JPATH_ROOT . '/plugins/system/conversekit/conversekit.php';

			$contents = file_get_contents($newfilePath);
			JFile::write($ckFilePath, $contents);
		}

		return true;
	}
}
