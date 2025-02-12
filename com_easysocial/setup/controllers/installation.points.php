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

class EasySocialControllerInstallationPoints extends EasySocialSetupController
{
	/**
	 * Install points on the site
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
		$archivePath = $tmpPath . '/points.zip';

		// Where the badges should reside after extraction
		$path = $tmpPath . '/points';

		// Extract badges
		$state = $this->extractArchive($archivePath , $path);

		if( !$state )
		{
			return $this->output( $this->getResultObj( JText::_( 'COM_EASYSOCIAL_INSTALLATION_ERROR_EXTRACT_POINTS' ) , false ) );
		}

		$this->engine();

		// Retrieve the points model to scan for the path
		$model 	= ES::model( 'Points' );

		// Scan and install badges
		$points = JFolder::files( $path , '.points' , true , true );

		$totalPoints 	= 0;

		if( $points )
		{
			foreach( $points as $point )
			{
				$model->install( $point );

				$totalPoints 	+= 1;
			}
		}

		return $this->output( $this->getResultObj( JText::sprintf( 'COM_EASYSOCIAL_INSTALLATION_POINTS_SUCCESS' , $totalPoints ) , true ) );
	}
}
