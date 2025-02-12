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

class EasySocialViewMailer extends EasySocialAdminView
{
	/**
	 * Renders the mailer queue from the system
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function display($tpl = null)
	{
		// Set page heading
		$this->setHeading('COM_EASYSOCIAL_HEADING_EMAIL_ACTIVITIES', 'COM_EASYSOCIAL_MAILER_DESCRIPTION');

		// Add buttons
		JToolbarHelper::publishList('publish' , JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_MARK_SENT'));
		JToolbarHelper::unpublishList('unpublish' , JText::_('COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_MARK_PENDING'));
		JToolbarHelper::trash('purgeSent' , JText::_( 'COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_PURGE_SENT'), false);
		JToolbarHelper::trash('purgePending' , JText::_( 'COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_PURGE_PENDING'), false);
		JToolbarHelper::trash('purgeAll' , JText::_( 'COM_EASYSOCIAL_TOOLBAR_TITLE_BUTTON_PURGE_ALL'), false);

		$this->addHelpButton('/administrators/cronjobs/cronjobs');

		// Get the model
		$model = ES::model('Mailer', array('initState' => true, 'namespace' => 'mailer.listing'));

		// Load site's language file as some of the subjects are from the front end's language file
		ES::language()->loadSite();

		$emails = $model->getItemsWithState();
		$pagination = $model->getPagination();
		$state = $model->getState('published');
		$limit = $model->getState('limit');
		$search = $model->getState('search');
		$ordering = $model->getState('ordering');
		$direction = $model->getState('direction');

		if ($state != 'all') {
			$state = (int) $state;
		}

		$lib = ES::mailer();

		// Need to do some processing on the emails
		foreach ($emails as $mail) {
			$mail->loadLanguage();
			$mail->title = $lib->translate($mail->title, $mail->params);
		}

		$this->set('ordering', $ordering);
		$this->set('direction', $direction);
		$this->set('search', $search);
		$this->set('limit', $limit);
		$this->set('published', $state);
		$this->set('emails', $emails);
		$this->set('pagination', $pagination);

		echo parent::display('admin/mailer/default/default');
	}

	/**
	 * Renders the list of e-mail templates
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function editor()
	{
		$this->setHeading('COM_EASYSOCIAL_HEADING_EMAIL_TEMPLATES');

		JToolbarHelper::deleteList('', 'reset', JText::_('COM_EASYSOCIAL_EMAILS_RESET_DEFAULT'));

		$model = ES::model('Emails');
		$files = $model->getFiles();

		$this->set('files', $files);

		return parent::display('admin/mailer/editor/default');
	}

	/**
	 * Post processing after resetting email
	 *
	 * @since	2.0.0
	 * @access	public
	 */
	public function reset()
	{
		return $this->redirect('index.php?option=com_easysocial&view=mailer&layout=editor');
	}

	/**
	 * Renders the editor for email template
	 *
	 * @since	2.0.0
	 * @access	public
	 */
	public function editFile()
	{
		$this->showSidebar = false;

		JToolBarHelper::apply('saveFile');
		JToolBarHelper::cancel();

		$this->setHeading('COM_EASYSOCIAL_EMAILS_EDITING_TITLE', 'COM_EASYSOCIAL_EMAILS_EDITING_TITLE_DESC');

		$file = $this->input->get('file', '', 'default');
		$file = urldecode($file);

		$model = ES::model("Emails");
		$absolutePath = $model->getFolder() . $file;

		$file = $model->getTemplate($absolutePath, true);

		// Always use codemirror
		$editor = ES::getEditor('codemirror');

		$this->set('editor', $editor);
		$this->set('file', $file);

		return parent::display('admin/mailer/editfile/default');
	}

	/**
	 * Previews an email
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function preview()
	{
		$id = $this->input->get('id', 0, 'int');

		$mail = ES::table('Mailer');
		$mail->load($id);

		$mail->loadLanguage(true);

		echo $mail->preview();
		exit;
	}

	/**
	 * Post processing after saving an email template file
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function saveFile($file, $path)
	{
		return $this->app->redirect('index.php?option=com_easysocial&view=mailer&layout=editor');
	}


	/**
	 * Post processing after mails are purged
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function purge()
	{
		return $this->redirect('index.php?option=com_easysocial&view=mailer');
	}

	/**
	 * Method is invoked when the user publish / unpublish mail items
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function togglePublish()
	{
		$this->redirect('index.php?option=com_easysocial&view=mailer');
	}
}
