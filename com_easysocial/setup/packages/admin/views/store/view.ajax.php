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

class EasySocialViewStore extends EasySocialAdminView
{
	/**
	 * Renders a confirmation dialog
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmation()
	{
		$id = $this->input->get('id', 0, 'int');
		$app = ES::store()->getApp($id);

		if (!$id || !$app->id) {
			return $this->exception('Invalid application id provided');
		}

		$return = $this->input->get('return', '', 'default');

		$theme = ES::themes();
		$theme->set('app', $app);
		$theme->set('return', $return);

		$contents = $theme->output('admin/apps/store/dialogs/confirmation');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Post processing after generating apps
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function refresh()
	{
		if ($this->hasErrors()) {
			$message = $this->getMessage();
			return $this->ajax->reject($message->message);
		}

		return $this->ajax->resolve();
	}
}