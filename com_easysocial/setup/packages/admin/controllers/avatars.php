<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerAvatars extends EasySocialController
{
	/**
	 * Sets an avatar as the default avatar.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setDefault()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');

		$avatar = ES::table('DefaultAvatar');
		$avatar->load($id);

		if (!$id || !$avatar->id) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILES_DEFAULT_AVATAR_INVALID_ID');
		}

		$avatar->setDefault();

		$this->view->setMessage('COM_EASYSOCIAL_PROFILES_DEFAULT_AVATAR_SET_DEFAULT_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}

	/**
	 * Delete's an avatar from the system.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function delete()
	{
		ES::checkToken();

		$id = $this->input->get('id', 0, 'int');
		$avatar = ES::table('DefaultAvatar');
		$avatar->load($id);

		if (!$id || !$avatar->id) {
			return $this->view->exception('COM_EASYSOCIAL_PROFILES_DEFAULT_AVATAR_INVALID_ID');
		}

		if (!$avatar->delete()) {
			return $this->view->exception($avatar->getError());
		}

		$this->view->setMessage('COM_EASYSOCIAL_PROFILES_DEFAULT_AVATAR_DELETED_SUCCESSFULLY');
		return $this->view->call(__FUNCTION__);
	}
}
