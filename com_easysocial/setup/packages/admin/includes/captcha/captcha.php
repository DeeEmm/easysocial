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

class SocialCaptcha
{
	private $adapter = null;

	public function __construct($adapter, $options = array())
	{
		$adapter = strtolower($adapter);
		$file = dirname(__FILE__) . '/adapters/' . $adapter . '.php';

		if (!JFile::exists($file)) {
			return false;
		}

		// Include adapter's file.
		require_once($file);

		$class = 'SocialCaptcha' . ucfirst($adapter);

		if (!class_exists($class)) {
			return false;
		}

		// Now we will have to create an instance of the class.
		$this->adapter = new $class($options);

		return $this;
	}

	/**
	 * Factory method
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function factory($adapter, $options = array())
	{
		$instance = new self( $adapter, $options);

		return $instance;
	}

	/**
	 * Retrieves the output from a captcha library.
	 *
	 * @deprecated	3.3.0
	 */
	public function getHTML($elementId = '', $invisible = false)
	{
		return $this->html($elementId, $invisible);
	}

	/**
	 * Generates the html for the recaptcha
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function html($elementId = '', $invisible = false)
	{
		return $this->adapter->html($elementId, $invisible);
	}

	/**
	 * Verifies the answer provided
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function checkAnswer($challenge, $response)
	{
		return $this->adapter->checkAnswer( $challenge , $response );
	}
}
