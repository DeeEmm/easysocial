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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

ES::import( 'admin:/views/views' );

class EasySocialViewSettings extends EasySocialAdminView
{
	/**
	 * Displays dialog to confirm reset settings
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function export()
	{
		// Get the real file name
		$fileName = 'settings.json';

		$config = ES::config();

		// Export the config object
		$contents = $config->toString();

		// Get the file size
		$size = strlen( $contents );

		$actionlog = ES::actionlog();
		$actionlog->log('COM_ES_ACTION_LOG_SETTINGS_EXPORTED', 'settings');

		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename='. $fileName );
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . $size );
		ob_clean();
		flush();
		echo $contents;
		exit;
	}
}