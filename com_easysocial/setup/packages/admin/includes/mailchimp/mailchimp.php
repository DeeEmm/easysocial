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

jimport('joomla.html.parameter');

class SocialMailchimp
{
	static $instance = null;
	private $key = null;
	private $url = 'api.mailchimp.com/3.0/lists/';

	public function __construct($key)
	{
		$this->key = $key;

		if ($this->key) {

			$datacenter	= explode( '-' , $this->key);

			$this->url = 'https://' . $datacenter[1] . '.' . $this->url;
		}
	}


	/**
	 * This is a singleton object in which it can / should only be instantiated using the getInstance method.
	 *
	 * @since	2.2
	 * @access	public
	 */
	public static function factory($key)
	{
		return new self($key);
	}

	/**
	 * Allows caller to subscribe to a newsletter
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function subscribe($listId, $email, $firstName, $lastName = '', $useDoubleOptIn = true)
	{
        $memberId = md5(strtolower($email));
        $url = $this->url . $listId . '/members/' . $memberId;
        $status = $useDoubleOptIn ? 'pending' : 'subscribed';

        $json = json_encode([
            'email_address' => $email,
            'status' => $status,
            'merge_fields' => [
                'FNAME' => $firstName,
                'LNAME' => $lastName
            ]
        ]);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $this->key);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        $result = curl_exec($ch);
        curl_close($ch);

        return true;
	}

	/**
	 * Unsubscribe user from a list
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function unsubscribe($listId, $email)
	{
        $memberId = md5(strtolower($email));
        $url = $this->url . $listId . '/members/' . $memberId;

        $json = json_encode([
            'status' => 'unsubscribed'
        ]);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $this->key);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        $result = curl_exec($ch);
        curl_close($ch);

        return true;
	}
}
