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

class SocialTableAudioGenre extends SocialTable implements ISocialIndexerTable
{
	public $id = null;
	public $title = null;
	public $alias = null;
	public $description = null;
	public $state = null;
	public $default = null;
	public $user_id = null;
	public $created = null;
	public $ordering = null;

	public function __construct($db)
	{
		parent::__construct('#__social_audios_genres', 'id', $db);
	}

	public function syncIndex()
	{
	}

	public function deleteIndex()
	{
	}

	/**
	 * Sets the default audio genre
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function setDefault()
	{
		$db = ES::db();

		// Remove all default
		$query = array();
		$query[] = 'UPDATE ' . $db->qn('#__social_audios_genres') . ' SET ' . $db->qn('default') . '=' . $db->Quote(0);
		$db->setQuery($query);
		$db->query();

		$this->default = true;

		return $this->store();
	}

	/**
	 * Build's the genre alias
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getAlias()
	{
		$alias = JFilterOutput::stringURLSafe($this->alias);
		if (!$alias) {
			$alias = JFilterOutput::stringURLUnicodeSlug($this->alias);
		}

		$alias 	= $this->id . ':' . $alias;
		return $alias;
	}

	/**
	 * Retrieves the permalink for this audio genre
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getPermalink($xhtml = true, $uid = null, $type = null)
	{
		$options = [
			'genreId' => $this->getAlias()
		];

		if ($uid && $type) {
			$options['uid'] = $uid;
			$options['type'] = $type;

			if (is_numeric($uid) && $type == SOCIAL_TYPE_USER) {
				$user = ES::user($uid);
				$options['uid'] = $user->getAlias();
			}

			if (is_numeric($uid) && $type != SOCIAL_TYPE_USER) {
				$cluster = ES::cluster($type, $uid);

				if ($cluster && $cluster->id) {
					$options['uid'] = $cluster->getAlias();
				}
			}
		}

		$url = ESR::audios($options, $xhtml);

		return $url;
	}

	/**
	 * Override parent's delete behavior
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function delete($pk = null)
	{
		// Before deleting, we need to ensure that there are no audios associated with this genre
		$total = $this->getTotalAudios();

		if ($total > 0) {
			$this->setError('COM_ES_AUDIO_GENRES_UNABLE_TO_DELETE_CHILDS');
			return false;
		}

		return parent::delete();
	}

	/**
	 * Retrieves the total number of audios from this genre
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getTotalAudios($cluster = false, $uid = null, $type = null)
	{
		static $result = array();

		$index = $this->id . $uid . $type;

		if (!isset($result[$index])) {

			$model = ES::model('Audios');
			$result[$index] = $model->getTotalAudiosFromGenre($this->id, $cluster, $uid, $type);
		}

		return $result[$index];
	}


	/**
	 * Retrieves a list of profile id's associated with the genre
	 *
	 * @since	2.1
	 * @access	public
	 */
	public function getProfileAccess($type = 'create')
	{
		$model = ES::model('Audios');

		$profiles = $model->getGenreAccess($this->id, $type);

		return $profiles;
	}

	/**
	 * Bind the access for a genre node.
	 *
	 * @since   2.1
	 * @access  public
	 */
	public function bindGenreAccess($type = 'create', $profiles = [])
	{
		$model = ES::model('Audios');

		return $model->insertGenreAccess($this->id, $type, $profiles);
	}

	/**
	 * Override parent's store function
	 *
	 * @since  2.1
	 * @access public
	 */
	public function store($updateNulls = null)
	{
		if (empty($this->ordering)) {
			$this->ordering = $this->getNextOrder();
		}

		$state = parent::store($updateNulls);

		return $state;
	}

	/**
	 * Exports audio category data
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer, $options = array())
	{
		static $cache = array();

		$key = $this->id . $viewer->id;

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$extended = ES::normalize($options, 'extended', true);

		$result = array(
			'id' => $this->id,
			'title' => JText::_($this->title),
			'description' => JText::_($this->description),
			'alias' => $this->getAlias()
		);

		if ($extended) {
			$result['author'] = ES::user($this->user_id)->toExportData($viewer, false);
		}

		$result = (object) $result;

		$cache[$key] = $result;

		return $cache[$key];
	}

	/**
	 * Retrieve the description of this category
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Retrieve the permalink for the filter listing
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getFilterPermalink($xhtml = true, $uid = null, $type = null)
	{
		return $this->getPermalink($xhtml, $uid, $type);
	}

	/**
	 * Retrieve the title of the category
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getTitle()
	{
		return JText::_($this->title);
	}

	/**
	 * Retrieve the state of genre/category
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isPublished()
	{
		return ($this->getState() == SOCIAL_STATE_PUBLISHED);
	}
}
