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

jimport('joomla.mail.helper');

class EasySocialControllerSharing extends EasySocialController
{
	/**
	 * Shares content with a 3rd party email recipient
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function send()
	{
		ES::checkToken();

		$token = $this->input->get('token', '', 'default');
		$recipients = $this->input->get('recipients', array(), 'array');

		// Ensure that the recipients is an array
		if (is_string($recipients)) {
			$recipients = explode(',', ES::string()->escape($recipients));
		}

		// Check for the recipients validity
		if (is_array($recipients)) {

			foreach ($recipients as $recipient) {
				$recipient = ES::string()->escape($recipient);
				$isValidEmail = JMailHelper::isEmailAddress($recipient);

				if (!$isValidEmail) {
					return $this->view->call(__FUNCTION__, false, JText::_('COM_EASYSOCIAL_SHARING_EMAIL_INVALID_RECIPIENT'));
				}
			}
		}

		// Ensure that the contents are properly escaped
		$content = $this->input->get('content', '', 'default');
		$content = ES::string()->escape($content);

		// Check for valid data
		if (!$recipients) {
			return $this->view->call(__FUNCTION__, false, JText::_('COM_EASYSOCIAL_SHARING_EMAIL_NO_RECIPIENTS'));
		}

		// Ensure that a token is provided
		if (!$token) {
			return $this->view->call(__FUNCTION__, false, JText::_('COM_EASYSOCIAL_SHARING_EMAIL_INVALID_TOKEN'));
		}

		// Get the current session
		$session = JFactory::getSession();

		// Get the number of emails allowed to share
		$limit = $this->config->get('sharing.email.limit', 0);

		// Get the current timestamp
		$now = ES::date()->toUnix();

		// Get the time
		$time = $session->get('easysocial.sharing.email.time');
		$count = $session->get('easysocial.sharing.email.count');

		if (is_null($time)) {
			$session->set('easysocial.sharing.email.time', $now);
			$time = $now;
		}

		if (is_null($count)) {
			$session->set('easysocial.sharing.email.count', 0);
		}

		// Get the time difference
		$diff = $now - $time;

		if ($diff <= 3600) {
			if ($limit > 0 && $count >= $limit) {
				return $this->view->call(__FUNCTION__, false, JText::_('COM_EASYSOCIAL_SHARING_EMAIL_SHARING_LIMIT_MAXED'));
			}

			$count++;
			$session->set('easysocial.sharing.email.count', $count);
		} else {
			$session->set('easysocial.sharing.email.time', $now);
			$session->set('easysocial.sharing.email.count', 1);
		}

		$lib = ES::sharing();
		$lib->sendLink($recipients, $token, $content);

		return $this->view->call(__FUNCTION__, true);
	}
}
