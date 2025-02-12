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

class EasySocialViewEasySocial extends EasySocialAdminView
{
	/**
	 * Main method to display the dashboard view.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Add heading here.
		$this->setHeading('COM_EASYSOCIAL_HEADING_DASHBOARD', 'COM_EASYSOCIAL_DESCRIPTION_DASHBOARD');

		// Get total albums
		$photosModel = ES::model('Albums');
		$totalAlbums = $photosModel->getTotalAlbums();

		// Get mailer model
		$mailerModel = ES::model('Mailer');
		$mailStats = $mailerModel->getDeliveryStats();

		// profiles signup data
		$profilesModel = ES::model('Profiles');
		$signupData = $profilesModel->getRegistrationStats();

		// Get reports model
		$reportsModel = ES::model('Reports');
		$totalReports = $reportsModel->getReportCount();

		// Get total videos
		$videosModel = ES::model('Videos');
		$totalVideos = $videosModel->getTotalVideos(array('state' => 'all'));

		// Get total audio files
		$audiosModel = ES::model('Audios');
		$totalAudios = $audiosModel->getTotalAudios(array('state' => 'all'));

		// Get total pending users
		$usersModel = ES::model('Users');

		// Get total events
		$eventsModel = ES::model('Events');
		$totalEvents = $eventsModel->getTotalEvents();

		// Get total number of groups
		$groupsModel = ES::model('Groups');
		$totalGroups = $groupsModel->getTotalGroups();

		$xAxes = array();

		foreach ($signupData->dates as $date) {
			$xAxes[] = ES::date($date)->format(JText::_('COM_EASYSOCIAL_DATE_DM'));
		}

		// Add translation on the profile title
		foreach ($signupData->profiles as $profile) {
			$profile->title = JText::_($profile->title);
		}

		// Get app updates notice
		$appUpdates = $this->getAppsRequiringUpdates();

		$installedVersion = ES::getLocalVersion();

		$this->set('installedVersion', $installedVersion);
		$this->set('totalAudios', $totalAudios);
		$this->set('appUpdates', $appUpdates);
		$this->set('totalEvents', $totalEvents);
		$this->set('totalVideos', $totalVideos);
		$this->set('totalReports', $totalReports);
		$this->set('mailStats', $mailStats);
		$this->set('axes', $xAxes);
		$this->set('signupData', $signupData);
		$this->set('totalUsers', $usersModel->getTotalUsers());
		$this->set('totalOnline', $usersModel->getTotalOnlineUsers());
		$this->set('totalGroups', $totalGroups);
		$this->set('totalAlbums', $totalAlbums);

		// Add Joomla button
		if ($this->my->authorise('core.admin', 'com_easysocial')) {
			JToolbarHelper::preferences( 'com_easysocial' );
		}

		echo parent::display('admin/easysocial/default');
	}

	/**
	 * Get a list of apps from the app store that we can track for version updates
	 *
	 * @since	2.0.9
	 * @access	public
	 */
	public function getAppsRequiringUpdates()
	{
		$total = 0;

		$model = ES::model('Store');
		$apps = $model->getAppsRequiringUpdates();

		if ($apps !== false) {
			$total = count($apps);
		}

		return $total;
	}
}
