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

require_once(__DIR__ . '/adapters/guzzle.php');

class SocialConnector extends EasySocial
{
	public $adapter	= null;

	public function __construct($url = '')
	{
		$this->adapter = new SocialConnectorGuzzle($url);
	}

	public static function factory($url = '')
	{
		$connector = new self($url);

		return $connector;
	}

	public function init()
	{
		return $this;
	}

	public function getResult($url = null, $withHeaders = false)
	{
		return $this->adapter->getResult($url, $withHeaders);
	}

	public function getResults()
	{
		return $this->adapter->getResults();
	}

	public function addUrl($url)
	{
		$this->url = $url;

		$this->adapter->addUrl($url);

		return $this;
	}

	public function addLength($length)
	{
		$this->adapter->addLength($length);

		return $this;
	}

	public function useHeaders()
	{
		$this->adapter->useHeadersOnly();

		return $this;
	}

	public function connect()
	{
		$this->adapter->execute();

		return $this;
	}

	/**
	 * Maps back the call method functions to the helper.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __call($method, $args)
	{
		$refArray = array();

		if ($args) {

			foreach ($args as &$arg) {
				$refArray[]	=& $arg;
			}
		}

		return call_user_func_array(array($this->adapter, $method), $refArray);
	}
}
