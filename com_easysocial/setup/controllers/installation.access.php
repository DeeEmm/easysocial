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

class EasySocialControllerInstallationAccess extends EasySocialSetupController
{
	/**
	 * Install access rules on the site
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();
		$this->engine();

		// Scan and install alert files
		$model = ES::model('AccessRules');
		$path = JPATH_ADMINISTRATOR . '/components/com_easysocial/defaults/access';
		$files = JFolder::files($path, '.access$', true, true);

		$totalRules	= 0;

		if ($files) {
			foreach ($files as $file) {

				$model->install($file);

				$totalRules += 1;
			}
		}

		return $this->output( $this->getResultObj( JText::sprintf( 'COM_EASYSOCIAL_INSTALLATION_RULES_SUCCESS' , $totalRules ) , true ) );
	}
}
