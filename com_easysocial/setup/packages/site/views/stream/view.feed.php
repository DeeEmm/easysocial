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

class EasySocialViewStream extends EasySocialSiteView
{
	public function display($tpl = null)
	{
		// Get the configuration objects
		$config = ES::config();
		$jConfig = ES::config('joomla');

		if (!$config->get('rss.enabled')) {
			$this->info->set(false, 'COM_EASYSOCIAL_NOT_ALLOWED_TO_VIEW_SECTION', SOCIAL_MSG_ERROR);
			$this->redirect(ESR::dashboard(array(), false));
			return;
		}

		// Get the stream library
		$stream = ES::stream();
		$stream->get();

		// Get the result in an array form
		$result = $stream->toArray();

		// Set the document properties
		$doc = JFactory::getDocument();
		$doc->link = ESR::dashboard();

		ES::document()->title(JText::_('COM_EASYSOCIAL_STREAM_FEED_TITLE'));
		$doc->setDescription(JText::sprintf('COM_EASYSOCIAL_STREAM_FEED_DESC', $jConfig->getValue('sitename')));

		if ($result) {

			foreach ($result as $row) {
				$item = new JFeedItem();
				$item->title = $row->title;
				$item->link = ESR::stream(array('id' => $row->uid));
				$item->description = $row->content;
				$item->date	= $row->created->toMySQL();

				$doc->addItem($item);
			}
		}
	}
}
