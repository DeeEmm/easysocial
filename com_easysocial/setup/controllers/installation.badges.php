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

class EasySocialControllerInstallationBadges extends EasySocialSetupController
{
	/**
	 * Install badges on the site
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		// Get the temporary path from the server.
		$tmpPath = $this->input->get('path', '', 'default');

		// There should be a queries.zip archive in the archive.
		$archivePath = $tmpPath . '/badges.zip';

		// Where the badges should reside after extraction
		$path = $tmpPath . '/badges';

		// Extract badges
		$state = $this->extractArchive($archivePath, $path);

		if (!$state) {
			return $this->output( $this->getResultObj( JText::_( 'COM_EASYSOCIAL_INSTALLATION_ERROR_EXTRACT_BADGES' ) , false ) );
		}

		$this->engine();

		$model = ES::model('Badges');

		// Scan and install badges
		$badges = JFolder::files($path, '.badge$', true, true);

		$totalBadges = 0;

		if ($badges) {
			foreach ($badges as $badge) {
				$model->install($badge);

				$totalBadges += 1;
			}
		}

		// After installing the badge, copy the badges folder over to ADMIN/com_easysocial/defaults/
		JFolder::copy($path, JPATH_ADMINISTRATOR . '/components/com_easysocial/defaults/badges', '', true);

		return $this->output($this->getResultObj(JText::sprintf('COM_EASYSOCIAL_INSTALLATION_BADGES_SUCCESS', $totalBadges), true));
	}
}
