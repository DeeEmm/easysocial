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

class EasySocialModNewsfeedHelper extends EasySocial
{
	public function getFilterLists()
	{
		$model = ES::model('Stream');
		$lists = $model->getFilters($this->my->id);

		return $lists;
	}

	public function getFriendLists()
	{
		$listLimit = $this->config->get('lists.display.limit');

		// Get the friend's list.
		$model = ES::model('Lists');
		$lists = $model->setLimit($listLimit)->getLists(array('user_id' => $this->my->id));
		
		return $lists;
	}
}
