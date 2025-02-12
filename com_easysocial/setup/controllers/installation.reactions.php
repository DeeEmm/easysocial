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

class EasySocialControllerInstallationReactions extends EasySocialSetupController
{
	/**
	 * Install reactions default data
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

		$sql->select('#__social_reactions');
		$sql->column('id');
		$sql->limit(0, 1);

		$db->setQuery($sql);
		$id = $db->loadResult();

		// We don't have to do anything since there's already a default reactions
		if ($id) {
			$result = $this->getResultObj('Skipping reactions installation since reactions already exists on the site', true);
			return $this->output($result);
		}

		$query = "INSERT INTO `#__social_reactions` (`id`, `action`, `published`, `created`, `params`) VALUES
					(1, 'like', 1, '0000-00-00 00:00:00', ''),
					(2, 'happy', 1, '0000-00-00 00:00:00', ''),
					(3, 'love', 1, '0000-00-00 00:00:00', ''),
					(4, 'angry', 1, '0000-00-00 00:00:00', ''),
					(5, 'wow', 1, '0000-00-00 00:00:00', ''),
					(6, 'sad', 1, '0000-00-00 00:00:00', '');";

		$db->setQuery($query);
		$this->query($db);

		return $this->output($this->getResultObj(JText::_('Reactions initialized successfully'), true));
	}
}
