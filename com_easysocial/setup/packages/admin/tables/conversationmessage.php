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

ES::import('admin:/tables/table');

class SocialTableConversationMessage extends SocialTable
{
	public $id = null;
	public $conversation_id = null;
	public $type = null;
	public $message = null;
	public $created = null;
	public $created_by = null;

	// These columns are not real columns in the database table.
	protected $target = null;

	public function __construct(&$db)
	{
		parent::__construct('#__social_conversations_message', 'id' , $db);
	}

	/**
	 * Retrieves the author of the message.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getCreator()
	{
		static $nodes = array();

		if (!isset($nodes[$this->created_by])) {
			$nodes[$this->created_by] = ES::user($this->created_by);
		}

		return $nodes[$this->created_by];
	}

	/**
	 * Retrieves the content of the message.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getContents($fromRest = false)
	{
		$message = '';
		$my = ES::user();

		if ($this->type == 'join') {

			if ($this->getTarget()->id == $my->id) {
				return JText::sprintf('COM_EASYSOCIAL_CONVERSATIONS_INVITED_YOU_INTO_CONVERSATION_MESSAGE', $this->getCreator()->getName());
			}

			return JText::sprintf('COM_EASYSOCIAL_CONVERSATIONS_INVITED_INTO_CONVERSATION_MESSAGE', $this->getCreator()->getName(), $this->getTarget()->getName());
		}

		if ($this->type == 'leave') {

			if ($this->getCreator()->id == $my->id) {
				return JText::_('COM_EASYSOCIAL_CONVERSATIONS_YOU_LEFT_CONVERSATION_MESSAGE');
			}

			return JText::sprintf('COM_EASYSOCIAL_CONVERSATIONS_LEFT_CONVERSATION_MESSAGE', $this->getCreator()->getName());
		}

		if ($this->type == 'delete') {
			if ($this->getTarget()->id == $my->id) {
				return JText::_('COM_ES_CONVERSATIONS_DELETED_PARTICIPANT_YOU_MESSAGE');
			}

			if ($this->getCreator()->id == $my->id) {
				return JText::sprintf('COM_ES_CONVERSATIONS_YOU_DELETED_PARTICIPANT_MESSAGE', $this->getTarget()->getName());
			}

			return JText::sprintf('COM_ES_CONVERSATIONS_DELETED_PARTICIPANT_MESSAGE', $this->getCreator()->getName(), $this->getTarget()->getName());
		}

		if ($this->type == 'create') {
			return JText::sprintf('COM_ES_CONVERSATIONS_CREATED_THIS_CONVERSATION', $this->getCreator()->getName());
		}

		if (!$fromRest) {
			if ($this->type == 'giphy') {
				$output = $this->message;

				if (ES::giphy()->isEnabledForConversations()) {
					$theme = ES::themes();
					$theme->set('giphy', $this->message);

					$output = $theme->output('site/giphy/preview/display');
				}

				return $output;
			}

			if ($this->type == 'message') {

				// $stringLib = ES::string();
				$stringLib = ES::get('string');

				$message = $this->message;

				$tags = $this->getTags();

				// Apply mentions and hashtags
				if ($tags) {
					$message = $stringLib->processTags($tags, $message, true, true);
				}

				$message = ES::string()->escape($message);

				// Convert the tags
				if ($tags) {
					$message = $stringLib->afterProcessTags($tags, $message, true, false);
					$message = $stringLib->processSimpleTags($message);
				}

				// Apply e-mail replacements
				$message = ES::string()->replaceEmails($message);

				// comment out this is because parseBBCode function already do the great job parse to permalink.
				// Apply hyperlinks
				// $message = ES::string()->replaceHyperlinks($message);

				// Apply bbcode
				$message = ES::string()->parseBBCode($message, array('escape' => false, 'links' => true, 'code' => true));
			}
		}

		return $message;
	}

	public function setType($type = SOCIAL_CONVERSATION_TYPE_MESSAGE)
	{
		$allowed = array(SOCIAL_CONVERSATION_TYPE_MESSAGE, SOCIAL_CONVERSATION_TYPE_LEAVE, SOCIAL_CONVERSATION_TYPE_JOIN, SOCIAL_CONVERSATION_TYPE_DELETE, SOCIAL_CONVERSATION_TYPE_GIPHY, SOCIAL_CONVERSATION_TYPE_CREATE);

		// If there is disallowed type, always leave it to default type
		if (!in_array($type, $allowed)) {
			return;
		}

		$this->type = $type;

		if ($type == SOCIAL_CONVERSATION_TYPE_GIPHY) {
			$giphy = ES::giphy();

			// If giphy is disabled for conversation or is not a valid url, set it back to message
			if (!$giphy->isEnabledForConversations() || !$giphy->isValidUrl($this->message)) {
				$this->type = SOCIAL_CONVERSATION_TYPE_MESSAGE;
			}
		}
	}


	/**
	 * Retrieves a list of tags for this conversation
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getTags()
	{
		$model = ES::model('Tags');

		$tags = $model->getTags($this->id, 'conversations');

		return $tags;
	}

	/**
	 * Retrieves the intro text portion of a message.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getIntro($overrideLength = null)
	{
		$config = ES::config();

		// Get the maximum length.
		$maxLength = is_null($overrideLength) ? $config->get('conversations.layout.intro') : $overrideLength;

		$message = $this->message;

		if ($this->type == 'join') {
			$message = JText::sprintf('COM_EASYSOCIAL_CONVERSATIONS_INVITED_INTO_CONVERSATION_MESSAGE', $this->getCreator()->getName(), $this->getTarget()->getName());
		}

		if ($this->type == 'leave') {
			$message = JText::sprintf('COM_EASYSOCIAL_CONVERSATIONS_LEFT_CONVERSATION_MESSAGE', $this->getCreator()->getName());
		}

		$message = strip_tags($message);
		$message = ES::string()->processEmoWithTruncate($message, $maxLength);

		return $message;
	}

	/**
	 * Retrieves a list of attachment for this conversation message.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getAttachments()
	{
		$model = ES::model('Files');

		$files = $model->getFiles($this->id, SOCIAL_TYPE_CONVERSATIONS);

		return $files;
	}


	/**
	 * Binds any temporary files to the message.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function bindTemporaryFiles($ids, $deleteSource = true)
	{
		// This should only be executed with a valid conversation.
		if (!$this->id) {
			$this->setError(JText::_('COM_EASYSOCIAL_CONVERSATIONS_ERROR_STORE_CONVERSATION_FIRST'));
			return false;
		}

		// Ensure that they are in an array form.
		$ids = ES::makeArray($ids);

		foreach ($ids as $id) {
			$file = ES::table('File');

			$file->sub = ES_FILE_SUB_PREFIX_CONVERSATION . $this->conversation_id;
			$file->uid = $this->id;
			$file->type = SOCIAL_TYPE_CONVERSATIONS;

			// Copy some of the data from the temporary table.
			$hashFileName = true;
			$file->copyFromTemporary($id, $deleteSource, $hashFileName);
		}

		return true;
	}

	public function getType()
	{
		return strtolower($this->type);
	}

	/**
	 * This is only used when the conversation type is a "join" or "leave" type.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getTarget()
	{
		$target = $this->message;

		$user = ES::user($target);

		return $user;
	}

	/**
	 * Override parent's store method so that we can
	 * run our own maintenance here.
	 */
	public function store($updateNulls = false)
	{
		$state = parent::store($updateNulls);

		if ($state) {
			$input = ES::request();

			// Add a new location item if address, latitude , longitude is provided.
			$latitude = $input->get('latitude', '', 'default');
			$longitude = $input->get('longitude');
			$address = $input->get('address');

			// Let's add the location now.
			if (!empty($latitude ) && !empty($longitude) && !empty($address)) {
				$location = ES::table('Location');
				$location->latitude	= $latitude;
				$location->longitude = $longitude;
				$location->address = $address;
				$location->uid = $this->id;
				$location->type = SOCIAL_TYPE_CONVERSATIONS;
				$location->user_id = $this->created_by;

				$location->store();
			}
		}

		return $state;
	}

	/**
	 * Returns a standard location object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getLocation()
	{
		$location = ES::table('Location');
		$state = $location->loadByType($this->id, SOCIAL_TYPE_CONVERSATIONS);

		if (!$state) {
			return false;
		}

		return $location;
	}

	/**
	 * Retrieves the last replied date
	 *
	 * @since	1.5
	 * @access	public
	 */
	public function getRepliedDate($lapsed = true)
	{
		$date = ES::date($this->created);

		if ($lapsed) {
			return $date->toLapsed();
		}

		return $date;
	}

	/**
	 * Method to export the data for Rest API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer)
	{
		static $cache = array();

		$key = $this->id . $viewer->id;

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$creator = $this->getCreator()->toExportData($viewer);

		$repliedDate = $this->getRepliedDate(false);
		$timestamp = $repliedDate->toFormat('H:i');

		$creatorObject = array(
			'id' => $creator->id,
			'displayName' => $creator->displayName,
			'isVerified' => $creator->isVerified,
			'labelFontColour' => $creator->labelFontColour,
			'labelBackgroundColour' => $creator->labelBackgroundColour
		);

		$result = array(
			'id' => $this->id,
			'conversation_id' => $this->conversation_id,
			'type' => $this->type,
			'message' => $this->getFormattedContent($viewer),
			'created_by' => $this->created_by,
			'replied' => $this->getRepliedDate(true),
			'avatar' => $creator->avatar['large'],
			'isViewer' => $creator->id == $viewer->id,
			'displayName' => $creator->displayName,
			'creatorId' => $creator->id,
			'creatorObject' => $creatorObject,
			'created' => $this->created,
			'formattedCreatedDate' => $this->getCreatedDate()->toFormat('DATE_FORMAT_LC6'),
			'timestamp' => $timestamp,
			'attachments' => false,
			'canDelete' => ES::conversation($this->conversation_id)->canDeleteMessage($viewer->id, $this)
		);

		$attachments = $this->getAttachments();

		if ($attachments) {
			$files = array();
			foreach ($attachments as $attachment) {
				$files[] = $attachment->toExportData($viewer);
			}

			$result['attachments'] = $files;
		}

		$result = (object) $result;

		$cache[$key] = $result;

		return $cache[$key];
	}

	/**
	 * Retrieves the created date
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getCreatedDate()
	{
		$date = ES::date($this->created);

		return $date;
	}

	/**
	 * Retrieve the content and format it according to REST API format
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function getFormattedContent($viewer)
	{
		// Set the content data on a variable
		$rawContent = $this->message;

		$messageContent = new stdClass();
		$messageContent->raw = $rawContent;
		$messageContent->formatted = '';
		$messageContent->object = '';

		$formattedContent = $rawContent;

		$tags = $this->getTags();

		if ($this->type == 'message') {
			$stringLib = ES::string();

			// Format the tags accordingly
			if ($tags) {
				$formattedContent = $stringLib->processTags($tags, $formattedContent, true, 'rest');
			}

			// bbcode content
			$config = ES::config();
			$bbCodeOptions = array('escape' => false, 'emoticons' => true, 'links' => true, 'restFormat' => true);
			$formattedContent = $stringLib->parseBBCode($formattedContent, $bbCodeOptions, $tags);

			// Remove <br>
			$formattedContent = str_ireplace(array('<br>', '</br>'), '', $formattedContent);
		} else {
			$formattedContent = $this->getContents(true);
		}

		if (!$formattedContent) {
			return $messageContent;
		}

		// Finalize the format
		$messageObject = $this->formatMessageObjects($viewer, $formattedContent, $tags);

		$messageContent->formatted = $formattedContent;
		$messageContent->object = $messageObject;

		return $messageContent;
	}

	/**
	 * Method to process stream tags to satisfy the REST API
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function formatMessageObjects($viewer, $formattedContent, $tags)
	{
		$objects = array();

		// process all the objects that are tagged within the stream content. (emoticon, hashtag, etc)
		foreach ($tags as $tag) {
			if (!isset($tag->identifier)) {
				continue;
			}

			$object = new stdClass();
			$object->identifier = $tag->identifier;
			$object->type = $tag->type;

			// Process user mention
			if (($tag->type == 'user' || $tag->type == 'entity') && isset($tag->user) && $tag->user instanceof SocialUser) {
				$user = $tag->user;
				$object->user = $user->toExportData($viewer);
			}

			if ($tag->type == 'emoticon') {

				if (!isset($tag->source)) {
					$table = ES::table('emoticon');
					$table->load(array('title' => $tag->title));

					if (!$table->id) {
						continue;
					}

					$tag->source = $table->icon;
				}

				if (stristr($tag->source , 'http://') === false && stristr($tag->source , 'https://') === false) {
					$subFolder = JURI::root(true);
					$tag->source = ltrim($tag->source, '/');

					if ($subFolder) {
						$subFolder = ltrim($subFolder, '/');
						$parts = explode('/', $tag->source);

						// Determine if the source already included the sub folder
						if ($parts[0] !== $subFolder) {
							$tag->source = rtrim(JURI::root(), '/') . '/' . $tag->source;
						} else {
							$uri = JURI::getInstance();
							$root = $uri->toString(array('scheme', 'host'));
							$root = rtrim($root, '/');

							if (isset($uri->port) && $uri->port) {
								$root = $root . '/' . ltrim($uri->port, '/');
							}

							$tag->source = $root . '/' . $tag->source;
						}

					} else {
						$tag->source = rtrim(JURI::root(), '/') . '/' . $tag->source;
					}
				}

				$object->source = $tag->source;
			}

			if ($tag->type == 'hashtag') {
				$object->title = $tag->title;
			}

			if ($tag->type == 'external_url') {
				$object->url = $tag->url;
			}

			if ($tag->type == 'email') {
				$object->email = $tag->value;
			}

			$objects[$object->identifier] = $object;
		}

		// Split the content so that the app can re-assemble the content part by part.
		$contentObject = explode('[[object]]', $formattedContent);
		$newContentObject = array();

		foreach ($contentObject as $string) {
			$obj = new stdClass();

			if (!isset($objects[$string])) {

				// Nothing to process
				if (strlen($string) === 0) {
					continue;
				}

				$obj->type = 'string';
				$obj->value = $string;

				$newContentObject[] = $obj;
				continue;
			}

			$obj->type = 'object';
			$obj->value = $objects[$string];
			$newContentObject[] = $obj;
		}

		return $newContentObject;
	}

}
