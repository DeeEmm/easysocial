<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

require_once(SOCIAL_LIB . '/migrators/helpers/info.php');

/**
 * Main class for migrator helper
 *
 * @since	1.0
 */
class SocialMigratorHelper
{
	public $info = null;

	/**
	 * The total number of items to process each time.
	 * @var int
	 */
	public $limit = 10;

	/**
	 * The user's chosen custom mapping
	 * @var Array
	 */
	public $userMapping = null;

	public function __construct()
	{
		$this->info = new SocialMigratorHelperInfo();
	}

	/**
	 * Sets the mapping for the custom field types the user has chosen
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function setUserMapping($maps)
	{
		if (!$maps) {
			return false;
		}

		$userMap 	= array();

		foreach ($maps as $map) {
			if (strpos($map['name'], 'field_') !== false) {
				$fid = str_replace('field_', '', $map['name']);
				if ($fid) {
					$userMap[ $fid ] = $map['value'];
				}
			}

			$this->userMapping = $userMap;
		}
	}

	public function getCountryCode($country)
	{
		static $countries = null;

		$country = strtolower($country);

		if (!$countries) {
			$file = JPATH_ADMINISTRATOR . '/components/com_easysocial/defaults/countries.json';
			$contents = file_get_contents($file);

			$json = ES::json();
			$data = $json->decode($contents);

			foreach ($data as $key => $value) {
				$countries[ strtolower($value) ] = $key;
			}
		}


		if (isset($countries[ $country ])) {
			// return country code.
			return $countries[ $country ];
		} else {
			// just return the coountry value
			return $country;
		}

	}


	/**
	 * Retrieves the field mapping from CB -> EasySocial
	 *
	 * @since	1.2
	 * @access	public
	 * @return	Array
	 */
	public function getFieldsMap()
	{
		return $this->mapping;
	}

	/**
	 * Adds a new user -> profile mapping
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	protected function addProfileMapping($userId , $profileId)
	{
		$db = ES::db();
		$sql = $db->sql();

		// delete from existing profile map
		$query = 'delete from `#__social_profiles_maps` where `user_id` = ' . $db->Quote($userId);
		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$db->query();

		$mapping = ES::table('ProfileMap');
		$mapping->profile_id = $profileId;
		$mapping->user_id = $userId;
		$mapping->state = SOCIAL_STATE_PUBLISHED;

		$mapping->store();

		// Update the log
		$this->info->setInfo(JText::sprintf('User ID #%1$s is associated to the Profile ID #%2$s' , $userId , $profileId));

		return $mapping;
	}


	/**
	 * Retrieves the default profile
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getDefaultProfile()
	{
		$db = ES::db();
		$sql = $db->sql();

		$sql->select('#__social_profiles');
		$sql->where('default' , 1);
		$sql->limit(1);

		$db->setQuery($sql);

		$row = $db->loadObject();

		if (!$row) {
			// If there is no default profile, just select 1 item from the profiles list.
			$sql->clear();
			$sql->select('#__social_profiles');
			$sql->limit(1);

			$db->setQuery($sql);
			$row = $db->loadObject();

			if (!$row) {
				return false;
			}

		}

		$profile = ES::table('Profile');
		$profile->bind($row);

		return $profile;
	}


	public function removeAdminSegment($url = '')
	{
		if ($url) {
			$url = '/' . ltrim($url , '/');
			$url = str_replace('/administrator/', '/', $url);
		}

		return $url;
	}

	/**
	 * Method to create a default workflow used in user profile
	 *
	 * @since	2.1
	 * @access	private
	 */
	public function createWorkflow($profileId, $title = '', $type = SOCIAL_TYPE_USER)
	{
		if (! $title) {
			$title = 'Default Migrated Workflow';
		}

		$workflow = ES::table('Workflow');
		$workflow->title = $title;
		$workflow->description = $title;
		$workflow->type = $type;
		$workflow->store();

		$workflowId = $workflow->id;

		// now we need to associate this profile with this newly created worflow.
		$workflowMap = ES::table('WorkflowMap');
		$workflowMap->uid = $profileId;
		$workflowMap->workflow_id = $workflowId;
		$workflowMap->type = $type;
		$workflowMap->store();

		return $workflowId;
	}

	public function createDefaultItems($profileId, $title = '', $type = SOCIAL_TYPE_USER, $extension = '')
	{
		// we need to create workflow data as well.
		$workflowMap = ES::table('WorkflowMap');
		$workflowMap->load(array('uid' => $profileId, 'type' => $type));

		$workflowId = 0;

		if ($workflowMap->id) {
			$workflowId = $workflowMap->workflow_id;
		} else {
			// there is no existing workflow for this profile.
			// lets create one.
			$workflowId = $this->createWorkflow($profileId, $title, $type);
		}

		// Read the default profile json file first.
		$path = SOCIAL_ADMIN_DEFAULTS . '/fields/profile_migrator.json';

		if ($extension == 'com_profiler') {
			$path = SOCIAL_ADMIN_DEFAULTS . '/fields/cb_profile_migrator.json';
		}

		$contents = file_get_contents($path);

		$json = ES::json();
		$defaults = $json->decode($contents);

		$newStepIds = array();

		// Let's go through each of the default items.
		foreach ($defaults as $step) {
			// Create default step for this profile.
			$stepTable = ES::table('FieldStep');
			$stepTable->bind($step);

			// always set this to yes.
			// $stepTable->visible_display = 1;

			// Map the correct uid and type.
			$stepTable->uid = $profileId;
			$stepTable->type = ($type == SOCIAL_TYPE_USER) ? SOCIAL_TYPE_PROFILES : SOCIAL_TYPE_CLUSTERS;
			$stepTable->workflow_id = $workflowId;

			// Try to store the default steps.
			$state = $stepTable->store();

			$newStepIds[] = $stepTable->id;

			// Now we need to create all the fields that are in the current step
			if ($step->fields && $state) {

				foreach ($step->fields as $field) {
					$appTable = ES::table('App');
					$appTable->loadByElement($field->element , SOCIAL_TYPE_USER , SOCIAL_APPS_TYPE_FIELDS);

					$fieldTable = ES::table('Field');


					$fieldTable->bind($field);

					// Ensure that the main items are being JText correctly.
					$fieldTable->title = $field->title;
					$fieldTable->description = $field->description;
					$fieldTable->default = isset($field->default) ? $field->default : '';

					// Set the app id.
					$fieldTable->app_id = $appTable->id;

					// Set the step.
					$fieldTable->step_id = $stepTable->id;

					// Set this to be published by default.
					$fieldTable->state = isset($field->state) ? $field->state : SOCIAL_STATE_PUBLISHED;

					// Set this to be searchable by default.
					$fieldTable->searchable = isset($field->searchable) ? $field->searchable : SOCIAL_STATE_PUBLISHED;

					// Set this to be searchable by default.
					$fieldTable->required = isset($field->required) ? $field->required : SOCIAL_STATE_PUBLISHED;

					// // Set this to be searchable by default.
					// $fieldTable->required = isset($field->required) ? $field->required : SOCIAL_STATE_PUBLISHED;

					// Check if the default items has a params.
					if (isset($field->params)) {
						$fieldTable->params = ES::json()->encode($field->params);
					}

					// Store the field item.
					$fieldTable->store();

					// set the unique key
					$fieldTable->checkUniqueKey();
					$fieldTable->store();

				}
			}
		}

		return $newStepIds;
	}

	public function addPrivacyMap($uid, $utype)
	{
		$db = ES::db();
		$sql = $db->sql();

		// lets add privacy for this newly create profile :)
		$query = 'insert into `#__social_privacy_map` (`privacy_id`, `uid`, `utype`, `value`)';
		$query .= ' select id, ' . $db->Quote($uid) . ', ' . $db->Quote($utype) . ', value';
		$query .= ' from `#__social_privacy`';

		$sql->clear();
		$sql->raw($query);
		$db->setQuery($sql);
		$state = $db->query();

		return $state;

	}


	public function profileLastOrdering()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select max(`ordering`) from `#__social_profiles`';

		$sql->raw($query);
		$db->setQuery($sql);

		$result = $db->loadResult();

		return (empty($result)) ? 0 : $result;
	}


	/**
	* Process mentions.
	*
	* @since	2.0
	* @access	public
	*/
	public static function processJSMentions(&$content)
	{
		$pattern = '/@\[\[([\d]+):contact:([^\]]+)\]\]/i';
		preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

		$mentions = array();

		if ($matches) {
			foreach ($matches as $match) {

				$tag = $match[0];
				$userId = $match[1];
				$userName = $match[2];

				$content = str_replace($tag, $userName, $content);

				// now we need to get the offset of this occurance.
				$offset = ESJString::strpos($content, $userName);

				if ($offset !== false) {
					$data = new stdClass();

					$data->start = $offset;
					$data->length = ESJString::strlen($userName);
					$data->type = 'entity';
					$data->value = 'user:' . $userId;

					$mentions[] = $data;
				}
			}
		}

		return $mentions;
	}


	/**
	* Process mentions.
	*
	* @since	2.2
	* @access	public
	*/
	public static function processCommentMentions(&$commentObj)
	{
		$content = $commentObj->comment;
		$pattern = '/@\[\[([\d]+):contact:([^\]]+)\]\]/i';
		preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

		if ($matches) {
			foreach ($matches as $match) {

				$tag = $match[0];
				$userId = $match[1];
				$userName = $match[2];

				$content = str_replace($tag, $userName, $content);

				// now we need to get the offset of this occurance.
				$offset = ESJString::strpos($content, $userName);

				if ($offset !== false) {
					$tag = ES::table('Tag');
					$tag->offset = $offset;
					$tag->length = ESJString::strlen($userName);
					$tag->type = 'entity';

					$tag->item_id = $userId;
					$tag->item_type = 'user';
					$tag->creator_id = ES::user()->id;
					$tag->creator_type = SOCIAL_TYPE_USER;

					$tag->target_id = $commentObj->id;
					$tag->target_type = 'comments';

					$tag->store();
				}
			}

			$commentObj->comment = $content;
			$commentObj->store();
		}
	}


	/**
	 * Method to process image attachment from comment.
	 *
	 * @since  2.2
	 * @access private
	 */
	public static function processCommentAttachment($jsComment, $esComment)
	{
		$config = ES::config();
		$db = ES::db();

		$jsCommentParams = ES::registry($jsComment->params);
		$jsPhotoId = $jsCommentParams->get('attached_photo_id', 0);

		if ($jsPhotoId) {

			$query = 'select a.* from `#__community_photos` as a';
			$query .= ' where a.`id` = ' . $db->Quote($jsPhotoId);

			$db->setQuery($query);
			$jsPhoto = $db->loadObject();

			if ($jsPhoto->id) {
				// images/originalphotos/84/1/e03fbd75d6e8f5fe0e542665.jpg
				$imagePath = JPATH_ROOT . '/' . $jsPhoto->original;

				if (!JFile::exists($imagePath)) {
					// files from originalphotos not found. let try to get it from photos folder instead.
					// images/photos/84/1/e03fbd75d6e8f5fe0e542665.jpg
					$imagePath = JPATH_ROOT . '/' . $jsPhoto->image;
				}

				if (!JFile::exists($imagePath)) {
					// both image from originalphotos and photos folder not found. Lets give up.
					return false;
				}

				$path = JPATH_ROOT . '/' . ES::cleanPath($config->get('comments.storage'));

				// load the image file.
				$image = ES::image();
				$image->load($imagePath);

				$file = ES::table('File');
				$file->uid = $esComment->id;
				$file->type = SOCIAL_TYPE_COMMENTS;
				$file->name = $image->getName();
				$file->hash = JFile::makeSafe($file->name);
				$file->mime = $image->getMime();
				$file->size = filesize($imagePath);
				$file->created = $esComment->created;
				$file->user_id = $esComment->created_by;
				$file->storage = SOCIAL_STORAGE_JOOMLA;

				//  now let copy the file
				$dest = $path . '/' . $esComment->id;

				if (!JFolder::exists($dest)) {
					JFolder::create($dest);
				}

				// append the filename
				$dest .= '/' . $file->name;
				$state = JFile::copy($imagePath , $dest);

				if ($state) {
					$file->store();
				}
			}
		}

		return true;
	}

	public static function generateAlias($text)
	{
		$alias = JFilterOutput::stringURLSafe($text);

		if (!$alias) {
			$date = ES::date();
			$alias = JFilterOutput::stringURLSafe($date->toSql());
		}

		return $alias;
	}

	/**
	 * Converts html codes to bbcode
	 *
	 * @since	2.0.7
	 * @access	public
	 */
	public function html2bbcode($text)
	{
		$bbcodeSearch = array(
			'/<strong>(.*?)<\/strong>/ims',
			'/<b>(.*?)<\/b>/ims',
			'/<big>(.*?)<\/big>/ims',
			'/<em>(.*?)<\/em>/ims',
			'/<i>(.*?)<\/i>/ims',
			'/<u>(.*?)<\/u>/ims',
			'/<img.*?src=["|\'](.*?)["|\'].*?\>/ims',
			'/<[pP]>/ims',
			'/<\/[pP]>/ims',
			'/<blockquote>(.*?)<\/blockquote>/ims',
			'/<ol.*?\>(.*?)<\/ol>/ims',
			// '/<ul.*?\>(.*?)<\/ul>/ims',
			// '/<li.*?\>(.*?)<\/li>/ims',
			'/<a.*?href=["|\']mailto:(.*?)["|\'].*?\>.*?<\/a>/ims',
			'/<a.*?href=["|\'](.*?)["|\'].*?\>(.*?)<\/a>/ims',
			'/<pre.*?\>(.*?)<\/pre>/ims',
		);

		$bbcodeReplace = array(
			'[b]\1[/b]',
			'[b]\1[/b]',
			'[b]\1[/b]',
			'[i]\1[/i]',
			'[i]\1[/i]',
			'[u]\1[/u]',
			'[img]\1[/img]',
			'',
			'<br />',
			'[quote]\1[/quote]',
			// '[list=1]\1[/list]',
			// '[list]\1[/list]',
			'[*] \1',
			'[email]\1[/email]',
			'[url="\1"]\2[/url]',
			'[code type="xml"]\1[/code]',
		);

		// Replace bbcodes
		// $text = strip_tags($text, '<br><strong><em><u><img><a><p><blockquote><ol><ul><li><b><big><i><pre>');
		$text = strip_tags($text, '<br><strong><em><u><img><a><p><blockquote><ol><b><big><i><pre>');
		$text = preg_replace($bbcodeSearch , $bbcodeReplace, $text);
		$text = str_ireplace('<br />', "\r\n", $text);
		$text = str_ireplace('<br>', "\r\n", $text);

		return $text;
	}

}
