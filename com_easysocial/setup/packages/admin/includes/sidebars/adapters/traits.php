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

trait SocialSidebarTrait
{
	/**
	 * Retrieves the total counts of videos based on the type
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function render($items)
	{
		$data = [];

		if ($items) {
			foreach ($items as $item) {

				$obj = ES::makeObject($item);

				$type = $obj->type;

				$args = [];
				$method = 'render' . ucfirst($type);

				$args['filter'] = $obj->active;

				$obj->html = '';

				if (method_exists($this, $method)) {
					$obj->html = call_user_func_array([$this, $method], array_values($args));
				}

				$data[] = $obj;
			}
		}

		return $data;
	}
}
