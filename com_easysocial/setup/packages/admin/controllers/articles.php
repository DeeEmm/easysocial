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

class EasySocialControllerArticles extends EasySocialController
{
	/**
	 * Process articles suggestions
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function suggest()
	{
		ES::requireLogin();
		ES::checkToken();

		$search = $this->input->get('search', '', 'default');

		$model = ES::model('articles');
		$result = $model->getItems(array('search' => $search, 'simplifiedResult' => true));

		return $this->view->call(__FUNCTION__, $result);
	}
}
