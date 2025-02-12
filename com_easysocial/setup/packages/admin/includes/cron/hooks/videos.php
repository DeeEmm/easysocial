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

class SocialCronHooksVideos
{
	public function execute(&$states)
	{
		// Initiate the process to get videos that are pending to be processed.
		$states[] = $this->processVideos();

		// Initiate the process to check videos that are being processed.
		$states[] = $this->checkProcessedVideos();
	}

	/**
	 * Retrieves a list of videos that are being processed so that we can update the state accordingly.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function checkProcessedVideos()
	{
		// Get a list of videos that are being processed
		$options = array('filter' => 'processing', 'limit' => 20, 'sort' => 'random');

		$model = ES::model('Videos');
		$videos = $model->getVideosForCron($options);

		if (!$videos) {
			return JText::_('COM_EASYSOCIAL_CRONJOB_VIDEOS_PROCESSING_NO_VIDEOS');
		}

		$total = 0;

		foreach ($videos as $video) {

			// Get the status of the video
			$status = $video->status();

			// If the video is processed successfully, publish the video now.
			if ($status === true) {
				
				// If needed, we need to delete the original video from the site
				if ($video->isUpload() && ES::config()->get('video.delete')) {
					@JFile::delete($video->table->original);
				}

				$publishingOptions = array('createStream' => true);

				// we need to check if this is new video from stream edit or not. #558
				$streamId = $video->getStreamId('create');
				if ($streamId) {
					$publishingOptions['createStream'] = false;
				}

				$video->publish($publishingOptions);

				$this->sendNotification($video);
			}

			$total++;
		}
		return JText::sprintf('COM_EASYSOCIAL_CRONJOB_VIDEOS_PROCESSING_PUBLISHED', $total);
	}

	/**
	 * Send notification when video is published
	 *
	 * @since   2.2
	 * @access  public
	 */
	public function sendNotification($video)
	{
		$jConfig = ES::jconfig();
		$user = $video->getAuthor();
		$permalink = $video->getPermalink();

		$emailData = array();
		$emailData['sitename'] = ES::jconfig()->getValue('sitename');
		$emailData['permalink'] = $permalink;
		$emailData['actorName'] = $user->name;

		$subject = JText::_('COM_ES_EMAILS_VIDEO_READY_SUBJECT');

		$mailerData = ES::mailer()->getTemplate();

		$mailerData->setTitle($subject);
		$mailerData->setRecipient($user->name, $user->email);
		$mailerData->setTemplate('site/videos/ready');
		$mailerData->setParams($emailData);
		$mailerData->setFormat(1);
		$mailerData->setLanguage($user->getLanguage());

		// add into mail queue
		ES::mailer()->create($mailerData);

		return true;
	}

	/**
	 * Retrieves a list of videos to be processed and fire them to be processed.
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function processVideos()
	{
		// Get a list of videos that are pending for processing
		$options = array('filter' => 'pending', 'limit' => 20, 'sort' => 'random');

		$model = ES::model('Videos');
		$videos = $model->getVideosForCron($options);

		if (!$videos) {
			return JText::_('COM_EASYSOCIAL_CRONJOB_VIDEOS_PENDING_NO_VIDEOS');
		}

		$total = 0;

		foreach ($videos as $video) {

			// Launch the video process
			$video->process();

			$total++;
		}

		return JText::sprintf('COM_EASYSOCIAL_CRONJOB_VIDEOS_PENDING_VIDEO_PROCESSED', $total);
	}
}
