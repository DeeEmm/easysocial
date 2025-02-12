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

class SocialCronHooksHoneypot extends EasySocial
{
	public function execute(&$states)
	{
		$states[] = $this->processHoneypotKeyRenewal();
	}

	/**
	 * Update the honeypot key daily
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function processHoneypotKeyRenewal()
	{
		if (!$this->config->get('honeypot.autoswitch')) {
			return;
		}

		// Determine if it is time we need to update
		$last = $this->config->get('honeypot.last_switch');
		$update = false;
		$now = time();

		if ($last == '') {
			$update = true;
		}

		if ($last) {
			$diff = $now - $last;
			$update = $diff > 86400;
		}


		// Update the honeypot key used
		if ($update) {
			$honeypot = ES::honeypot();
			$key = $honeypot->generateKey();

			$data = array(
				'honeypot.last_switch' => $now,
				'registrations.honeypotkey' => $key
			);

			$model = ES::model('Config');
			$model->updateConfig($data);

			return 'Updated honeypot key';
		}

		return 'Nothing to be updated in honeypot';
	}
}
