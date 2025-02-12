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

ES::import('admin:/includes/db/helpers/base');

class SocialDBHelperJoomla40 extends SocialDBHelper
{
	public function nameQuote($str)
	{
		return $this->db->quoteName($str);
	}

	public function Query()
	{
		return $this->db->execute();
	}

	/**
	 * Proxy for getErrorNum method for Joomla4 compatibility
	 *
	 * @since   3.3
	 * @access  public
	 */
	public function getErrorNum()
	{
		return $this->db->getConnection()->errno;
	}
}
