<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialCronHooksRelease
{
	public function execute(&$states)
	{
		$states[] = $this->processBannedUserRelease();
	}

	/**
	 * archive stream items
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function processBannedUserRelease()
	{
		$model = ES::model('Users');
		$results = $model->getExpiredBannedUsers();

		if ($results) {

			// preload the user
			ES::user($results);

			foreach($results as $id) {
				$user = ES::user($id);
				$user->unblock();
			}

			// now we need to unset the block flag in users table.
			$state = $model->updateBlockInterval($results, '0');

			if ($state) {
				return JText::sprintf( 'COM_EASYSOCIAL_CRONJOB_USERS_RELEASE_PROCESSED', count($results) );
			}
		}

		return JText::_( 'COM_EASYSOCIAL_CRONJOB_USERS_RELEASE_NOTHING_TO_EXECUTE' );
	}

}
