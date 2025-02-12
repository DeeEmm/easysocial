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

class SocialBlocks extends EasySocial
{
	public static function factory()
	{
		return new self();
	}

	/**
	 * Blocks a specific target item
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function block($targetId, $reason = '')
	{
		$table = ES::table('BlockUser');
		$table->user_id = $this->my->id;
		$table->target_id = $targetId;
		$table->reason = $reason;
		$table->created = ES::date()->toMySQL();

		$table->store();

		return $table;
	}

	/**
	 * Unblocks a specific target item
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function unblock($targetId)
	{
		$table = ES::table('BlockUser');
		$table->load(array('user_id' => $this->my->id, 'target_id' => $targetId));

		$state = $table->delete();

		return $state;
	}

	/**
	 * Use @form instead
	 *
	 * @deprecated 2.0
	 */
	public function getForm($targetId)
	{
		return $this->form($targetId);
	}

	/**
	 * Retrieve the form to block the user
	 *
	 * @since	1.3
	 * @access	public
	 */
	public function form($targetId, $button = false)
	{
		if ($this->my->guest) {
			return;
		}
		
		if (!$this->config->get('users.blocking.enabled')) {
			return;
		}

		// Get the target object
		$user = ES::user($targetId);

		$namespace = 'site/blocks/link';

		if ($button) {
			$namespace = 'site/blocks/button';
		}

		// Default is to block
		$file = 'block';

		// We need to know if the target was already blocked by the user
		if ($user->isBlockedBy($this->my->id)) {
			$file = 'unblock';
		}

		$namespace = $namespace . '/' . $file;

		$theme = ES::themes();
		$theme->set('user', $user);
		$output = $theme->output($namespace);

		return $output;
	}
}
