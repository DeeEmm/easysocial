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

require_once(__DIR__ . '/abstract.php');

class SocialAppsController extends SocialAppsAbstract
{
	public function __construct($options = array())
	{
		parent::__construct($options);

		// Since we know all controllers will pass us the appId, we can then re-initialize
		$app = $this->getAppInstance();

		if ($app) {
			parent::__construct(array(
				'app' => $app
			));
		}
	}

	/**
	 * Allows caller to retrieve the app id from the request.
	 * Since we know all controller request expects `appId` from the query, we can get it from this key
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function getAppId()
	{
		static $appId = null;

		if (is_null($appId)) {
			$appId = $this->input->get('appId', 0, 'int');
		}

		return $appId;
	}

	/**
	 * Returns the current instance of the app given that we already know the app id from the request
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function getAppInstance()
	{
		static $instance = null;

		if (is_null($instance)) {
			$appId = $this->getAppId();

			if (!$appId) {
				$instance = false;

				return false;
			}

			$instance = ES::table('App');
			$instance->load($appId);
		}

		return $instance;
	}
}
