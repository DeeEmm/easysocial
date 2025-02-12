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

class SocialCronHooksAudios
{
	public function execute(&$states)
	{
		// Initiate the process to get audio that are pending to be processed.
		$states[] = $this->processAudios();

		// Initiate the process to check audio that are being processed.
		$states[] = $this->checkProcessedAudios();
	}

	/**
	 * Retrieves a list of audios that are being processed so that we can update the state accordingly.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function checkProcessedAudios()
	{
		// Get a list of audios that are being processed
		$options = array('filter' => 'processing', 'limit' => 20, 'sort' => 'random');

		$model = ES::model('Audios');
		$audios = $model->getAudiosForCron($options);

		if (!$audios) {
			return JText::_('COM_ES_AUDIO_CRONJOB_PROCESSING_NO_AUDIO');
		}

		$total = 0;

		foreach ($audios as $audio) {

			// Get the status of the audio
			$status = $audio->status();

			// If the audio is processed successfully, publish the audio now.
			if ($status === true) {
				$publishingOptions = array('createStream' => true);

				// we need to check if this is new audio from stream edit or not.
				$streamId = $audio->getStreamId('create');
				if ($streamId) {
					$publishingOptions['createStream'] = false;
				}

				$audio->publish($publishingOptions);

				$this->sendNotification($audio);

				$total++;
			}
		}
		return JText::sprintf('COM_ES_AUDIO_CRONJOB_PROCESSING_AUDIO_PUBLISHED', $total);
	}

	/**
	 * Send notification when audio is published
	 *
	 * @since   2.2
	 * @access  public
	 */
	public function sendNotification($audio)
	{
		$jConfig = ES::jconfig();
		$user = $audio->getAuthor();
		$permalink = $audio->getPermalink();

		$emailData = array();
		$emailData['sitename'] = ES::jconfig()->getValue('sitename');
		$emailData['permalink'] = $permalink;
		$emailData['actorName'] = $user->name;

		$subject = JText::_('COM_ES_EMAILS_AUDIO_READY_SUBJECT');

		$mailerData = ES::mailer()->getTemplate();

		$mailerData->setTitle($subject);
		$mailerData->setRecipient($user->name, $user->email);
		$mailerData->setTemplate('site/audios/ready');
		$mailerData->setParams($emailData);
		$mailerData->setFormat(1);
		$mailerData->setLanguage($user->getLanguage());

		// add into mail queue
		ES::mailer()->create($mailerData);

		return true;
	}

	/**
	 * Retrieves a list of audio to be processed and fire them to be processed.
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function processAudios()
	{
		// Get a list of audio that are pending for processing
		$options = array('filter' => 'pending', 'limit' => 20, 'sort' => 'random');

		$model = ES::model('Audios');
		$audios = $model->getAudiosForCron($options);

		if (!$audios) {
			return JText::_('COM_ES_AUDIO_CRONJOB_PENDING_NO_AUDIO');
		}

		$total = 0;

		foreach ($audios as $audio) {

			// Launch the audio process
			$audio->process();

			$total++;
		}

		return JText::sprintf('COM_ES_AUDIO_CRONJOB_PENDING_AUDIO_PROCESSED', $total);
	}
}
