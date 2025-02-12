<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(__DIR__ . '/library.php');

class SocialAkismet extends EasySocial
{
	private $akismet = null;
	private $ip = null;

	public function __construct($key, $url)
	{
		parent::__construct();

		$this->akismet = new Akismet($url, $key);

		return $this;
	}

	public static function factory($key, $url)
	{
		return new self($key, $url);
	}

	/**
	 * Checks the content to verify that it is not a spam
	 *
	 * @since	2.2.0
	 * @access	public
	 */
	public function check($content, $creator = null)
	{
		if (is_null($creator)) {
			$creator = ES::user();
		}

		$data = array(
			'user_ip' => $this->getUserIp(),
			'referrer' => @$_SERVER['HTTP_REFERER'],
			'author' => $creator->getName(),
			'email' => $creator->email,
			'body' => $content
		);

		$this->akismet->setComment($data);

		// If there are errors, we just assume that everything is fine so the entire
		// operation will still work correctly.
		if ($this->akismet->errorsExist()) {
			return false;
		}

		$spam = $this->akismet->isSpam();

		return $spam;
	}

	/**
	 * Responsible to get user's current IP address
	 *
	 * @since   2.2.0
	 * @access  public
	 */
	public function getUserIp()
	{
		if (!$this->ip) {

			if (getenv('HTTP_CLIENT_IP')) {
				$this->ip = getenv('HTTP_CLIENT_IP');
				return $this->ip;
			}

			if(getenv('HTTP_X_FORWARDED_FOR')) {
				$this->ip = getenv('HTTP_X_FORWARDED_FOR');
				return $this->ip;
			}

			if(getenv('HTTP_X_FORWARDED')) {
				$this->ip = getenv('HTTP_X_FORWARDED');
				return $this->ip;
			}

			if(getenv('HTTP_FORWARDED_FOR')) {
				$this->ip = getenv('HTTP_FORWARDED_FOR');
				return $this->ip;
			}
			
			if(getenv('HTTP_FORWARDED')) {
			   $this->ip = getenv('HTTP_FORWARDED');
			   return $this->ip;
			}

			if(getenv('REMOTE_ADDR')) {
				$this->ip = getenv('REMOTE_ADDR');
				return $this->ip;
			}
		}

		return $this->ip;
	}
}
