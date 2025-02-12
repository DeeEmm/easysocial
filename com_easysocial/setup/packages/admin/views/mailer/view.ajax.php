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

ES::import('admin:/views/views');

class EasySocialViewMailer extends EasySocialAdminView
{
	/**
	 * Previews an email
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function preview()
	{
		$id = $this->input->get('id', 0, 'int');

		$table = ES::table('Mailer');
		$table->load($id);

		// Load the language
		$table->loadLanguage(true);

		// Load the mailer library
		$mailer = ES::mailer();

		$table->title = $mailer->translate($table->title, $table->params);

		$theme = ES::themes();
		$theme->set('mailer', $table);
		$contents = $theme->output('admin/mailer/dialogs/preview');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Renders the confirmation dialog to reset email theme files
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function confirmReset()
	{
		$files = $this->input->get('files', array(), 'default');

		$theme = ES::themes();
		$theme->set('files', $files);
		$contents = $theme->output('admin/mailer/dialogs/reset.default');

		return $this->ajax->resolve($contents);
	}

	/**
	 * Confirmation before purging everything
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmPurgeAll()
	{
		$theme 	= ES::themes();
		$contents = $theme->output('admin/mailer/dialogs/purge.all');

		$this->ajax->resolve($contents);
	}

	/**
	 * Confirmation before purging pending e-mails
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmPurgeSent()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/mailer/dialogs/purge.sent');

		$this->ajax->resolve($contents);
	}

	/**
	 * Confirmation before purging pending e-mails
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function confirmPurgePending()
	{
		$theme = ES::themes();
		$contents = $theme->output('admin/mailer/dialogs/purge.pending');

		$this->ajax->resolve($contents);
	}
}
