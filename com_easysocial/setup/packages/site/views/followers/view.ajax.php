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

class EasySocialViewFollowers extends EasySocialSiteView
{
	/**
	 * Responsible to return html codes to the ajax calls.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function filter($filter, SocialUser $user, $users = array(), $pagination = null)
	{
		$theme = ES::themes();
		$theme->set('users', $users);
		$theme->set('filter', $filter);
		$theme->set('pagination', $pagination);

		$output = $theme->output('site/followers/default/items');

		return $this->ajax->resolve($output);
	}
}
