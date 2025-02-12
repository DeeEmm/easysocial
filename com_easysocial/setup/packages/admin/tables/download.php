<?php
/**
* @package  EasyBlog
* @copyright Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license  GNU/GPL, see LICENSE.php
* EasyBlog is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

class SocialTableDownload extends SocialTable
{
	public $id = null;
	public $userid = null;
	public $state = null;
	public $params = null;
	public $created = null;

	public function __construct(&$db)
	{
		parent::__construct('#__social_download', 'id', $db);

		$this->config = ES::config();
	}

	/**
	 * Determine whether user has requested.
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function isExists()
	{
		if (is_null($this->id)) {
			return false;
		}

		return true;
	}

	/**
	 * Determine when the state is ready
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function isNew()
	{
		return $this->state == ES_DOWNLOAD_REQ_NEW;
	}

	/**
	 * Determine when the state is ready
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function isProcessing()
	{
		return $this->state == ES_DOWNLOAD_REQ_PROCESS;
	}

	/**
	 * Determine when the state is ready
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function isReady()
	{
		return $this->state == ES_DOWNLOAD_REQ_READY;
	}

	/**
	 * Determine when the state is locked
	 *
	 * @since	4.0.10
	 * @access	public
	 */
	public function isLocked()
	{
		return $this->state == ES_DOWNLOAD_REQ_LOCKED;
	}

	/**
	 * Method used to update the request state.
	 *
	 * @since 2.1.11
	 * @access public
	 */
	public function updateState($state)
	{
		$this->state = $state;

		// debug. need to uncomment.
		return $this->store();
	}

	/**
	 * Method used to set filepath.
	 *
	 * @since 2.1.11
	 * @access public
	 */
	public function setFilePath($filepath)
	{
		$params = new JRegistry($this->params);
		$params->set('path', $filepath);
		$this->params = $params->toString();
	}

	/**
	 * Request state of the download. Return false if not exist.
	 *
	 * @since 2.2
	 * @access public
	 */
	public function getState()
	{
		if (!$this->isExists()) {
			return false;
		}

		return $this->state;
	}

	/**
	 * Retrieves the label for the state (used for display purposes)
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getStateLabel()
	{
		if ($this->getState() == ES_DOWNLOAD_REQ_READY) {
			return JText::_('COM_ES_DOWNLOAD_STATE_READY');
		}

		return JText::_('COM_ES_DOWNLOAD_STATE_PROCESSING');
	}

	/**
	 * Retrieves the requester
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getRequester()
	{
		$user = ES::user($this->userid);

		return $user;
	}

	/**
	 * Method used to send email notification to user who requested to download GDPR details.
	 * @since  2.2
	 * @access public
	 */
	public function sendNotification()
	{

		$jConfig = ES::jconfig();
		$user = ES::user($this->userid);

		$downloadLink = $this->getDownloadLink();

		$emailData = array();
		$emailData['sitename'] = ES::jconfig()->getValue('sitename');
		$emailData['downloadLink'] = $downloadLink;
		$emailData['actorName'] = $user->name;

		$subject = JText::_('COM_ES_EMAILS_GDPR_DOWNLOAD_SUBJECT');

		$mailerData = ES::mailer()->getTemplate();

		$mailerData->setTitle($subject);
		$mailerData->setRecipient($user->name, $user->email);
		$mailerData->setTemplate('site/gdpr/download.ready');
		$mailerData->setParams($emailData);
		$mailerData->setFormat(1);
		$mailerData->setLanguage($user->getLanguage());

		// add into mail queue
		ES::mailer()->create($mailerData);

		return true;
	}

	/**
	 * Method to ouput the zip file to browser for download.
	 * @since  2.2
	 * @access public
	 */
	public function showArchiveDownload()
	{
		// or however you get the path
		$param = $this->getParams();
		$file = $param->get('path', '');

		if (! $file) {
			return false;
		}

		$user = ES::user($this->userid);

		$fileName =  JFilterOutput::stringURLSafe($user->getName());
		$fileName .= '.zip';

		header("Content-Type: application/zip");
		header("Content-Disposition: attachment; filename=$fileName");
		header("Content-Length: " . filesize($file));

		echo file_get_contents($file);
		exit;
	}

	/**
	 * Method generate the download link of this request
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getDownloadLink()
	{
		$downloadLink = ESR::_('index.php?option=com_easysocial&view=download', false, array(), null, false, true);

		return $downloadLink;
	}

	/**
	 * Retrieves the expiration in days
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getExpireDays()
	{
		$days = $this->config->get('users.download.expiry');

		return $days;
	}

	/**
	 * Override parent delete method to manually delete archive file as well.
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function delete($pk = null)
	{
		// Delete temporary folder if it exists
		$user = ES::user($this->userid);
		$tmpFolder = SOCIAL_GDPR_DOWNLOADS . '/' . md5($user->id . $user->password . $user->email);

		$exists = JFolder::exists($tmpFolder);

		if ($exists) {
			JFolder::delete($tmpFolder);
		}

		$param = $this->getParams();
		$file = $param->get('path', '');

		if ($file) {
			if (JFile::exists($file)) {
				JFile::delete($file);
			}
		}

		return parent::delete($pk);
	}
}