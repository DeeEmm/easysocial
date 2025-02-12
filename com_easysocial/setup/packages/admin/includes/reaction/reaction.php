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

class SocialReaction extends EasySocial
{
	public $table = null;
	public $total = null;

	public function __construct($action)
	{
		parent::__construct();

		$this->table = ES::table('Reaction');

		if (is_string($action)) {
			$this->table->load($action);
		}

		if ($action instanceof SocialTableReaction) {
			$this->table = $action;
		}

		if (is_object($action)) {
			$action = (array) $action;
		}

		if (is_array($action)) {
			$this->table->bind($action);
		}
	}

	public static function factory($action)
	{
		return new self($action);
	}

	/**
	 * Retrieves the reaction's action
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getKey()
	{
		$key = strtolower($this->table->action);

		return $key;
	}

	/**
	 * Generates the reaction text
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getText()
	{
		$key = strtoupper($this->getKey());
		$text = JText::_('COM_ES_REACTION_' . $key);

		return $text;
	}

	/**
	 * Retrieves the total of reaction
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getTotal()
	{
		if (!$this->total) {
			return 0;
		}

		return $this->total;
	}

	/**
	 * Retrieves a list of users that reacted to this particular reaction
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function getUsers()
	{

	}

	/**
	 * Allows caller to set the total number of reactions for this particular reaction
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function setTotal($total)
	{
		$this->total = $total;
	}

	/**
	 * Retrieve the unicode value of the reactions
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getUnicode()
	{
		static $unicode = array();

		if (!isset($unicode[$this->table->action])) {

			$map = array(
				'like' => '👍',
				'happy' => '😀',
				'love' => '😍',
				'angry' => '😡',
				'wow' => '😮',
				'sad' => '😭'
			);

			$unicode[$this->table->action] = isset($map[$this->table->action]) ? $map[$this->table->action] : '';
		}

		return $unicode[$this->table->action];
	}

	/**
	 * Retrieve the image version of the reactions
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getImage($useRelative = true)
	{
		static $image = array();

		if (!isset($image[$this->table->action])) {
			$image[$this->table->action] = '';

			$path = 'media/com_easysocial/images/reactions/src/' . $this->table->action . '.svg';

			if (JFile::exists(SOCIAL_JOOMLA . '/' . $path)) {
				$image[$this->table->action] = $path;

				if (!$useRelative) {
					$image[$this->table->action] = rtrim(JURI::root(), '/') . '/' . ltrim($path, '/');
				}
			}
		}

		return $image[$this->table->action];
	}
}
