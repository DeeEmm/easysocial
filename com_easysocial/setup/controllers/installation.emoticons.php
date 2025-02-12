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

require_once(__DIR__ . '/controller.php');

class EasySocialControllerInstallationEmoticons extends EasySocialSetupController
{
	/**
	 * Install emoticons default data
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function execute()
	{
		$this->checkDevelopmentMode();

		$this->engine();

		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_emoticons');
		$sql->column('id');
		$sql->limit(0, 1);

		$db->setQuery($sql);
		$id = $db->loadResult();

		// We don't have to do anything since there's already a default emoticons
		if ($id) {
			$result = $this->getResultObj('Skipping emoticons installation since emoticons already exists on the site', true);
			return $this->output($result);
		}

		$library = SOCIAL_LIB . '/bbcode/adapters/decoda/library/config/emoticons.json';

		$contents = file_get_contents($library);
		$result = json_decode($contents);

		$insertValues = array();
		$count = 1;

		$now = ES::date()->toSql();

		foreach ($result as $key => $value) {
			$icon = 'media/com_easysocial/images/icons/emoji/' . $key . '.png';
			$insertValues[] = "(" . $db->Quote($count) . ", " . $db->Quote($key) . ", " . $db->Quote($icon) . ", 1, " . $db->Quote($now) . ")";
			$count++;
		}

		$query = "INSERT INTO `#__social_emoticons` (`id`, `title`, `icon`, `state`, `created`) VALUES " . implode(',', $insertValues);

		$db->setQuery($query);
		$this->query($db);

		return $this->output($this->getResultObj(JText::_('Emoticons initialized successfully'), true));
	}
}
