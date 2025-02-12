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

class SocialSmileys extends EasySocial
{
	/**
	 * This class uses the factory pattern.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string			The image driver to use.
	 * @return	SocialImage		Returns itself for chaining.
	 */
	public static function factory()
	{
		$obj = new self();

		return $obj;
	}

	public function getEmojis()
	{
		static $icons = array();

		if (!$icons) {
			$model = ES::model('Emoticons');
			$emoticons = $model->getItems();

			$icons = array();

			foreach ($emoticons as $icon) {
				$table = ES::table('Emoticon');
				$table->bind($icon);

				$emoji = new stdClass;
				$emoji->key = $table->title;
				$emoji->image = JURI::root() . $table->icon;
				$emoji->icon = $table->getIcon();
				$emoji->command = '(' . $table->title . ')';

				$icons[$table->title] = $emoji;
			}
		}

		return $icons;
	}

	/**
	 * Generates a list of smileys
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function html()
	{
		$model = ES::model('Emoticons');
		$emoticons = $model->getItems();

		$icons = array();

		foreach ($emoticons as $icon) {
			$table = ES::table('Emoticon');
			$table->bind($icon);

			$icons[] = $table;
		}

		$theme = ES::themes();
		$theme->set('icons', $icons);
		$output = $theme->output('site/smileys/default');

		return $output;
	}
}
