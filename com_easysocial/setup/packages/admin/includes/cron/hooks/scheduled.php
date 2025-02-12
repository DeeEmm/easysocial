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

class SocialCronHooksScheduled
{
	public function execute(&$states)
	{
		// Initiate the process to get scheduled post that are pending to be processed.
		$states[] = $this->processScheduled();
	}

	/**
	 * Retrieves the list of scheduled streams to be published.
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function processScheduled()
	{
		$scheduled = ES::model('Scheduler');

		$items = $scheduled->getCronScheduledStream();

		if (empty($items)) {
			return JText::_('COM_ES_CRONJOB_NO_SCHEDULED_STREAMS');
		}

		$group = SOCIAL_TYPE_USER;
		$total = count($items);

		// Load up the dispatcher so that we can trigger this.
		$dispatcher = ES::dispatcher();

		foreach ($items as $item) {
			$stream = $item['stream'];
			$streamItem = $item['streamItem'];
			$scheduled = $item['scheduled'];

			if ($stream->isCluster()) {
				$cluster = $stream->getCluster();
				$group = $cluster->getType();
			}

			// Build the arguments for the trigger
			$args = array(&$stream, &$streamItem, &$scheduled);

			// @trigger onScheduledAppStoryPublish
			$dispatcher->trigger($group, 'onPublishScheduledAppStory', $args, $streamItem->context_type);

		}

		return JText::sprintf('COM_ES_CRONJOB_SCHEDULED_STREAMS_PUBLISHED', $total);
	}
}
