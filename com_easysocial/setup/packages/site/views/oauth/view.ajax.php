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

class EasySocialViewOauth extends EasySocialSiteView
{
	/**
	 * Validates the oauth id
	 *
	 * @since	2.0.20
	 * @access	public
	 */
	public function validateId()
	{
		$id = $this->input->get('id', '');

		$table = ES::table('OAuth');
		$exists = $table->load(array('oauth_id' => $id));

		// Ensure that current Facebook logged user profile id didn't block on the site.
		if ($exists) {

			$user = ES::user($table->uid);

			if ($user->state != SOCIAL_USER_STATE_ENABLED) {
				$exists = false;
			}
		}

		return $this->ajax->resolve($exists);
	}

	/**
	 * Renders the confirmation to revoke the oauth access
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmRevoke()
	{
		// Only required users allowed here.
		ES::requireLogin();

		$client = $this->input->get('client', '', 'string');
		$callback = $this->input->get('callbackUrl', '', 'default');

		$theme = ES::themes();
		$theme->set('callback', $callback);
		$theme->set('client', $client);

		$contents = $theme->output('site/oauth/dialogs/revoke');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Prevents site lockdown from messing up with ajax calls
	 *
	 * @since	2.0.21
	 * @access	public
	 */
	public function isLockDown()
	{
		return false;
	}
}
