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

class SocialHoneyPot extends EasySocial
{
	/**
	 * Retrieves the honeypot key to be used in the form
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getKey()
	{
		$key = $this->config->get('registrations.honeypotkey');
		return $key;
	}

	/**
	 * Generates a random word
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function generateKey($length = 8)
	{
		$string = '';
		$vowels = array("a","e","i","o","u");

		$consonants = array(
			'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
			'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
		);

		// Seed it
		srand((double) microtime() * 1000000);

		$max = $length/2;

		for ($i = 1; $i <= $max; $i++) {
			$string .= $consonants[rand(0,19)];
			$string .= $vowels[rand(0,4)];
		}

		return $string;
	}

	/**
	 * Determines if the spammer is trapped
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function isTrapped($type)
	{
		$key = $this->getKey();

		$value = $this->input->get($key, '', 'default');

		if ($value) {

			if ($this->config->get('honeypot.logging')) {
				$this->log($type);
			}

			return true;
		}

		return false;
	}

	/**
	 * Generates a log for honeypot
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function log($type)
	{
		$data = $this->input->post->getArray();

		$table = ES::table('Honeypot');
		$table->type = $type;
		$table->key = $this->getKey();
		$table->data = json_encode($data);
		$table->created = JFactory::getDate()->toSql();
		$table->store();

		return $table;
	}
}
