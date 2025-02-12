<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class EasySocialControllerSharer extends EasySocialController
{
	/**
	 * Stores the link that is being shared
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function save()
	{
		if (!$this->config->get('sharer.enabled')) {
			ES::raiseError(404);
		}

		ES::requireLogin();
		ES::checkToken();

		// Load our story library
		$story = ES::story(SOCIAL_TYPE_USER);

		// Get posted data.
		$post = $this->input->getArray('post');

		// Determine the post types.
		$type = SOCIAL_TYPE_LINKS;

		// Check if the content is empty only for story based items.
		if ((!isset($post['content']) || empty($post['content'])) && $type == SOCIAL_TYPE_STORY) {
			return $this->view->exception('COM_EASYSOCIAL_STORY_PLEASE_POST_MESSAGE');
		}

		// We need to allow raw because we want to allow <,> in the text but it should be escaped during display
		$content = $this->input->get('content', '', 'raw');

		// We need to remove if there is any /n before the first word
		$content = preg_replace('~^[\r\n]+~', '', $content);
		$contextIds = 0;

		// Set the privacy for the album
		$privacy = $this->input->get('privacy', '', 'default');
		$customPrivacy = $this->input->get('privacyCustom', '', 'string');
		$fieldPrivacy = $this->input->get('privacyField', '', 'string');

		$privacyRule = 'story.view';

		// Check for posting permission
		if (!$this->my->canPostStory()) {
			return $this->view->exception('COM_EASYSOCIAL_STORY_NOT_ALLOW_TO_POST_HERE');
		}

		// Options that should be sent to the stream lib
		$args = array(
						'content' => $content,
						'contextIds' => $contextIds,
						'contextType' => $type,
						'actorId' => $this->my->id,
						'privacyRule' => 'story.view',
						'privacyValue' => '',
						'privacyCustom' => '',
						'privacyField' => ''
					);

		// The form may contain params
		if (isset($post['params'])) {
			$args['params'] = $post['params'];
		}

		// Create the stream item
		$stream = $story->create($args);

		// Add badge for the author when a story is created.
		ES::badges()->log('com_easysocial', 'story.create', $this->my->id, JText::_('COM_EASYSOCIAL_STORY_BADGE_CREATED_STORY'));

		// Add points for the author when a story is created.
		ES::points()->assign('story.create', 'com_easysocial', $this->my->id);

		// Assign points to the person that added the share button
		if (isset($post['aff']) && $post['aff']) {
			$table = ES::table('Users');
			$exists = $table->load(array('affiliation_id' => $post['aff']));

			if ($exists && $table->user_id) {
				ES::points()->assign('sharer.shared', 'com_easysocial', $table->user_id);
			}
		}

		$this->view->call(__FUNCTION__);
	}
}
