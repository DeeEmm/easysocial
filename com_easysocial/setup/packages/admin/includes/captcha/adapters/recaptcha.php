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

/**
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          https://developers.google.com/recaptcha/docs/php
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * @copyright Copyright (c) 2014, Google Inc.
 * @link      http://www.google.com/recaptcha
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class SocialRecaptchaResponse
{
	public $success;
	public $errorCodes;
}

class SocialCaptchaReCaptcha extends EasySocial
{
	private static $_signupUrl = "https://www.google.com/recaptcha/admin";
	private static $_siteVerifyUrl = "https://www.google.com/recaptcha/api/siteverify?";
	private $_secret;
	private static $_version = "php_1.0";
	private $options = array();

	public function __construct($options = array())
	{
		parent::__construct();

		$this->options = $options;
	}

	/**
	 * Encodes the given data into a query string format.
	 *
	 * @param array $data array of string elements to be encoded.
	 *
	 * @return string - encoded request.
	 */
	private function _encodeQS($data)
	{
		$req = "";
		foreach ($data as $key => $value) {
			$req .= $key . '=' . urlencode(stripslashes($value)) . '&';
		}

		// Cut the last '&'
		$req=substr($req, 0, strlen($req)-1);
		return $req;
	}

	/**
	 * Submits an HTTP GET to a reCAPTCHA server.
	 *
	 * @param string $path url path to recaptcha server.
	 * @param array  $data array of parameters to be sent.
	 *
	 * @return array response
	 */
	private function _submitHTTPGet($path, $data)
	{
		$req = $this->_encodeQS($data);
		$response = file_get_contents($path . $req);
		return $response;
	}

	/**
	 * Inject the google script tag on the page
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function loadHeaders()
	{
		static $loaded = false;

		if (!$loaded) {

			$this->doc->addScript('//www.google.com/recaptcha/api.js?onload=recaptchaCallback&render=explicit&hl=' . $this->options['language']);
			$loaded = true;
		}

		return $loaded;
	}

	/**
	 * Displays the recaptcha html code
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public function html($elementId = '', $invisible = false)
	{
		$uid = uniqid();
		$theme = ES::themes();

		$theme->set('elementId', $elementId);
		$theme->set('invisible', $invisible);
		$theme->set('key', $this->options['public']);
		$theme->set('theme', $this->options['theme']);
		$theme->set('invisiblePosition', $this->options['invisible_position']);
		$theme->set('uid', $uid);
		$theme->set('language', $this->options['language']);

		$output = $theme->output('site/captcha/recaptcha');

		return $output;
	}

	/**
	 * Alias for verifyResponse
	 *
	 * @since   1.3
	 * @access  public
	 */
	public function checkAnswer($remoteIp, $response)
	{
		return $this->verifyResponse($remoteIp, $response);
	}

	/**
	 * Calls the reCAPTCHA siteverify API to verify whether the user passes
	 * CAPTCHA test.
	 *
	 * @param string $remoteIp   IP address of end user.
	 * @param string $response   response string from recaptcha verification.
	 *
	 * @return SocialRecaptchaResponse
	 */
	public function verifyResponse($remoteIp, $response)
	{
		// Discard empty solution submissions
		if ($response == null || strlen($response) == 0) {
			$recaptchaResponse = new SocialRecaptchaResponse();
			$recaptchaResponse->success = false;
			$recaptchaResponse->errorCodes = 'missing-input';
			return $recaptchaResponse;
		}

		$getResponse = $this->_submitHttpGet(
			self::$_siteVerifyUrl,
			array (
				'secret' => $this->options['secret'],
				'remoteip' => $remoteIp,
				'v' => self::$_version,
				'response' => $response
			)
		);
		$answers = json_decode($getResponse, true);
		$recaptchaResponse = new SocialRecaptchaResponse();

		if (trim($answers ['success']) == true) {
			$recaptchaResponse->success = true;
		} else {
			$recaptchaResponse->success = false;
		}

		return $recaptchaResponse;
	}
}
