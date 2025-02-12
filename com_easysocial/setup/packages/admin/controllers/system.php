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

class EasySocialControllerSystem extends EasySocialController
{
	/**
	 * Process EasySocial one-click upgrade
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function upgrade()
	{
		$model = ES::model('System');
		$state = $model->update();

		if ($state === false) {
			$this->info->set(null, $model->getError(), SOCIAL_MSG_ERROR);
			return ES::redirect('index.php?option=com_easysocial');
		}

		$this->info->set(null, 'EasySocial successfully updated to the latest version', SOCIAL_MSG_SUCCESS);
		return ES::redirect('index.php?option=com_easysocial');
	}
}
