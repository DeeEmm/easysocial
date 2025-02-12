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
ES::import('admin:/includes/indexer/indexer');

class SocialTableAudio extends SocialTable implements ISocialIndexerTable
{
	public $id = null;
	public $artist = null;
	public $album = null;
	public $cover = null;
	public $title = null;
	public $description = null;
	public $duration = null;
	public $user_id = null;
	public $uid = null;
	public $type = null;
	public $created = null;
	public $assigned_date = null;
	public $state = null;
	public $isnew = null;
	public $scheduled = null;
	public $featured = null;
	public $genre_id = null;
	public $hits = null;
	public $size = null;
	public $params = null;
	public $storage = 'joomla';
	public $path = null;
	public $original = null;
	public $file_title = null;
	public $source = 'link';
	public $post_as = null;
	public $playlist_id = null;
	public $albumart_source = 'upload';

	/**
	 * Use for photo privacy access
	 */
	public $access = null;
	public $custom_access = null;
	public $field_access = null;
	public $chk_access = null;

	public function __construct($db)
	{
		parent::__construct('#__social_audios', 'id', $db);
	}

	public function syncIndex()
	{
	}

	public function deleteIndex()
	{
	}

	/**
	 * Override store method
	 *
	 * @since   3.1
	 * @access  public
	 */
	public function store($updateNulls = false)
	{
		// always set the chk_access to 1
		// so that new photos created after 3.1 
		// will not need to re-run the
		// privacy access migration.
		$this->chk_access = 1;

		// Backward compatibility for fresh install on 4.0.10 #5336
		if (!$this->created) {
			$this->created = ES::date()->toMySQL();
		}

		// give default date to assigned_date if empty
		if (!$this->assigned_date || $this->assigned_date == '0000-00-00 00:00:00') {
			$this->assigned_date = $this->created;
		}

		return parent::store();
	}

	/**
	 * Determines if this audio is an upload source
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isUpload()
	{
		return $this->source == SOCIAL_AUDIO_UPLOAD;
	}

	/**
	 * Determines if this audio is a link source
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isLink()
	{
		return $this->source == SOCIAL_AUDIO_LINK;
	}

	/**
	 * Constructs the alias for this audio
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function getAlias($withId = true)
	{
		$title = $this->title;
		$alias = JFilterOutput::stringURLSafe($title);
		if (!$alias) {
			$alias = JFilterOutput::stringURLUnicodeSlug($title);
		}

		if ($withId) {
			$alias = $this->id . ':' . $alias;
		}

		return $alias;
	}

	/**
	 * Method to update the cached sef alias when there
	 * is changes on the alias column
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function updateAliasSEFCache()
	{
		$old = ES::table('Audio');
		$old->load($this->id);

		$oldAlias = $old->getAlias();
		$newAlias = $this->getAlias();

		if ($oldAlias != $newAlias) {
			ESR::updateSEFCache($this, $oldAlias, $newAlias);
		}
	}

	/**
	 * Method to delete the cached sef alias when item being removed.
	 *
	 * @since	3.1
	 * @access	public
	 */
	public function deleteSEFCache()
	{
		$alias = $this->getAlias();
		$state = ESR::deleteSEFCache($this, $alias);

		return $state;
	}


	/**
	 * Retrieves the permalink of an audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getPermalink($xhtml = true, $uid = null, $utype = null, $from = false, $external = false, $sef = true, $adminSef = false)
	{
		$options = array('id' => $this->getAlias(), 'layout' => 'item', 'external' => $external, 'sef' => $sef, 'adminSef' => $adminSef);

		if ($this->uid && $this->type && $this->type != SOCIAL_TYPE_USER) {
			$cluster = ES::cluster($this->type, $this->uid);

			$options['uid'] = $cluster->getAlias();
			$options['type'] = $this->type;

		} else if ($uid && $utype) {

			if (is_numeric($uid) && $utype == SOCIAL_TYPE_USER) {
				$user = ES::user($this->uid);
				$uid = $user->getAlias();
			}

			$options['uid'] = $uid;
			$options['type'] = $utype;

		} else if ($this->type == SOCIAL_TYPE_USER) {
			$user = ES::user($this->uid);
			$options['uid'] = $user->getAlias();
			$options['type'] = $this->type;
		}

		if ($from !== false && $from) {
			$options['from'] = $from;
		}

		$url = FRoute::audios($options, $xhtml);

		return $url;
	}

	/**
	 * Retrieves the external permalink of an audio
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getExternalPermalink()
	{
		$url = FRoute::audios(array('id' => $this->id, 'layout' => 'item', 'external' => true), false);

		return $url;
	}

	/**
	 * Allows caller to set the duration
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function setDuration(SocialAudioDuration $duration)
	{
		$this->duration = $duration->raw();

		// Save the audio object
		return $this->store();
	}

	/**
	 * Allows the caller to set the state to processing
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function processing()
	{
		$this->state = SOCIAL_AUDIO_PROCESSING;

		return $this->store();
	}

	/**
	 * Determines if the audio item is unfeatured
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isUnfeatured()
	{
		return !$this->isFeatured();
	}

	/**
	 * Determines if the audio item is featured
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function isFeatured()
	{
		return $this->featured == SOCIAL_AUDIO_PUBLISHED;
	}

	/**
	 * Determines if the audio item is unpublished
	 *
	 * @since	3.2.16
	 * @access	public
	 */
	public function isUnpublished()
	{
		return $this->state == SOCIAL_AUDIO_UNPUBLISHED;
	}

	/**
	 * Determines if the audio item is scheduled
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function isScheduled()
	{
		return $this->scheduled == SOCIAL_AUDIO_SCHEDULED;
	}
}
