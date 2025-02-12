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

jimport('joomla.filesystem.file');

class EasySocialControllerSidebars extends EasySocialController
{
	/**
	 * Get sidebar counters
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function renderSection()
	{
		ES::checkToken();

		$uid = $this->input->get('uid', 0, 'int');
		$utype = $this->input->get('type', '', 'default');
		$type = $this->input->get('renderType', '', 'default');
		$items = $this->input->get('items', array(), 'array');

		$allowed = array(SOCIAL_TYPE_VIDEO, SOCIAL_TYPE_AUDIO);

		if (!$type || !in_array($type, $allowed)) {
			$this->view->setMessage('COM_ES_INVALID_DATA_PROVIDED', ES_ERROR);
			return $this->view->call(__FUNCTION__);
		}

		$lib = ES::sidebars($type, $uid, $utype);
		$data = $lib->render($items);

		return $this->view->call(__FUNCTION__, $data);
	}
}
