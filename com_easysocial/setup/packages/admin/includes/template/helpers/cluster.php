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

class ThemesHelperCluster extends ThemesHelperAbstract
{
	/**
	 * Renders the page stream object
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function approvalHistory($data)
	{
		$theme = ES::themes();
		$theme->set('data', $data);

		$content = $theme->output('site/clusters/history/default');

		return $content;
	}

	/**
	 * Renders the members text of a cluster
	 *
	 * @since   3.0.0
	 * @access  public
	 */
	public function members($cluster)
	{
		// Default to group type
		$text = 'COM_EASYSOCIAL_GROUPS_MEMBERS_MINI';

		if ($cluster->getType() == SOCIAL_TYPE_EVENT) {
			$text = 'COM_EASYSOCIAL_EVENTS_GUESTS';
		}

		if ($cluster->getType() == SOCIAL_TYPE_PAGE) {
			$text = 'COM_EASYSOCIAL_FOLLOWERS';
		}

		$text = ES::string()->computeNoun($text, $cluster->getTotalMembers());
		$text = JText::sprintf($text, $cluster->getTotalMembers());

		return $text;
	}
}
