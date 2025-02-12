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

class SocialVerification extends EasySocial
{
	/**
	 * Approves a verification request
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function approve($id)
	{
		$request = ES::table('Verification');
		$request->load($id);

		$request->state = ES_VERIFICATION_APPROVED;
		$request->store();

		// We need to update the user's verified state now
		if ($request->type == SOCIAL_TYPE_USER) {
			$table = ES::table('Users');
			$table->load($request->uid);

			$table->verified = true;
			$table->store();
		}

		if ($request->type != SOCIAL_TYPE_USER) {
			$cluster = ES::cluster($request->type, $request->uid);
			$cluster->verified = true;

			$cluster->save();
		}

		return $request;
	}

	/**
	 * Determines if user is allowed to request
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function canRequest($uid, $type)
	{
		$settingsType = $type . 's';

		$enabled = $this->config->get($settingsType . '.verification.enabled');
		$allowed = $this->config->get($settingsType . '.verification.requests');

		if ($type == SOCIAL_TYPE_USER && !$uid) {
			$uid = JFactory::getUser()->id;
		}

		$obj = ES::cluster($type, $uid);

		if (!$allowed || !$enabled || $obj->verified || $this->hasRequested($uid, $type)->state) {
			return false;
		}

		// Ensure that the user really can submit verification request on behalf of the object
		if (!$obj->canRequestVerification()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if user has previously requested before
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function hasRequested($uid = null, $type = SOCIAL_TYPE_USER)
	{
		if ($type == SOCIAL_TYPE_USER && !$uid) {
			$uid = JFactory::getUser()->id;
		}

		$obj = ES::cluster($type, $uid);

		$request = ES::table('Verification');
		$exists = $request->load([
				'uid' => $obj->id,
				'type' => $type
		]);


		// retrieve the verification state for this user
		$verifyState = $request->state;

		// Ensure this user set verified by admin from backend
		// Because the current process only update that verified state on the user table
		if (!$exists) {
			$verifyState = $obj->verified ? true : false;
		}

		$results = new stdClass();
		$results->state = $exists;
		$results->message = $verifyState ? 'COM_ES_ALREADY_VERIFIED_THIS_USER' : 'COM_ES_ALREADY_REQUEST_VERIFICATION';

		return $results;
	}

	/**
	 * Rejects a verification request
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function reject($id)
	{
		$request = ES::table('Verification');
		$request->load($id);

		$request->delete();

		return $request;
	}

	/**
	 * Generates a new request
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function request($message, $ip = null, $uid = null, $type = SOCIAL_TYPE_USER)
	{
		$request = ES::table('Verification');
		$request->uid = $uid;
		$request->type = $type;
		$request->created_by = $this->my->id;
		$request->message = $message;
		$request->created = JFactory::getDate()->toSql();
		$request->state = ES_VERIFICATION_REQUEST;
		$request->ip = $ip;
		$request->params = '';
		$state = $request->store();

		if ($state) {
			$this->notify($request);
		}

		return $request;
	}

	/**
	 * Notify admin on new verification request
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function notify($request)
	{
		$params = [
			'requester' => $this->my->getName(),
			'verificationMessage' => $request->message,
			'avatar' => $this->my->getAvatar(SOCIAL_AVATAR_LARGE),
			'itemPermalink' => $this->my->getPermalink(true, true),
			'permalink' => JURI::root() . 'administrator/index.php?option=com_easysocial&view=users&layout=verifications',
			'alerts' => false
		];

		if ($request->type != SOCIAL_TYPE_USER) {
			$cluster = ES::cluster($request->type, $request->uid);

			$params['requester'] = $cluster->getTitle();
			$params['avatar'] = $cluster->getAvatar();
			$params['itemPermalink'] = $cluster->getPermalink();
			$params['permalink'] = JURI::root() . 'administrator/index.php?option=com_easysocial&view=' . $cluster->getTypePlural() . '&layout=verifications';
		}

		$subject = JText::sprintf('COM_ES_EMAILS_USER_VERIFICATION_REQUEST_SUBHEADING', $params['requester']);

		// Get a list of super admins on the site.
		$usersModel = ES::model('Users');
		$admins = $usersModel->getSystemEmailReceiver();

		foreach ($admins as $admin) {

			$params['adminName'] = $admin->name;

			$mailer = ES::mailer();
			$mailTemplate = $mailer->getTemplate();
			$mailTemplate->setRecipient($admin->name, $admin->email);
			$mailTemplate->setTitle($subject);
			$mailTemplate->setTemplate('site/verifications/request', $params);
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			$state = $mailer->create($mailTemplate);
		}

		return true;
	}
}
