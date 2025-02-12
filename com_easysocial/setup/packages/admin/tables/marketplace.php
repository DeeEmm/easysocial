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

class SocialTableMarketplace extends SocialTable implements ISocialIndexerTable
{
	public $id = null;
	public $title = null;
	public $description = '';
	public $price = 0.00;
	public $currency = null;
	public $stock = null;
	public $condition = null;
	public $user_id = null;
	public $uid = null;
	public $type = null;
	public $created = null;
	public $state = null;
	public $isnew = null;
	public $scheduled = null;
	public $featured = null;
	public $category_id = null;
	public $album_id = null;
	public $params = null;
	public $hits = null;
	public $longitude = null;
	public $latitude = null;
	public $address = null;
	public $post_as = null;

	public function __construct($db)
	{
		parent::__construct('#__social_marketplaces', 'id', $db);
	}

	public function syncIndex()
	{
	}

	public function deleteIndex()
	{
	}

	/**
	 * Override bind method
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function bind($data, $ignore = array())
	{
		$state = parent::bind($data, $ignore);

		if (!$this->user_id) {
			// set current logged user
			$this->user_id = ES::user()->id;
		}

		if (!$this->id) {
			$this->isnew = 1;
		}

		if (!$this->price) {
			$this->price = 0.00;
		}

		return $state;
	}

	/**
	 * Override store method
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function store($updateNulls = false)
	{
		return parent::store();
	}

	/**
	 * Constructs the alias for this item
	 *
	 * @since   4.0
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
	 * @since	4.0
	 * @access	public
	 */
	public function updateAliasSEFCache()
	{
		$old = ES::table('Marketplace');
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
	 * @since	4.0
	 * @access	public
	 */
	public function deleteSEFCache()
	{
		$alias = $this->getAlias();
		$state = ESR::deleteSEFCache($this, $alias);

		return $state;
	}

	/**
	 * Retrieves the permalink of a item
	 *
	 * @since	4.0
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

		$url = ESR::marketplaces($options, $xhtml);

		return $url;
	}

	/**
	 * Retrieves the external permalink of a item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getExternalPermalink($format = null)
	{
		$options = array('id' => $this->getAlias(), 'layout' => 'item', 'external' => true);

		if ($format) {
			$options['format'] = $format;
		}

		if ($this->uid && $this->type && $this->type != SOCIAL_TYPE_USER) {
			$cluster = ES::cluster($this->type, $this->uid);

			$options['uid'] = $cluster->getAlias();
			$options['type'] = $this->type;

		} else if ($this->type == SOCIAL_TYPE_USER) {
			$user = ES::user($this->uid);
			$options['uid'] = $user->getAlias();
			$options['type'] = $this->type;
		}

		$url = ESR::marketplaces($options, false);

		return $url;
	}

	/**
	 * Determines if the item is unfeatured
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isUnfeatured()
	{
		return !$this->isFeatured();
	}

	/**
	 * Determines if the item is featured
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isFeatured()
	{
		return $this->featured == SOCIAL_STATE_PUBLISHED;
	}

	/**
	 * Determines if the item is unpublished
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isUnpublished()
	{
		return $this->state == SOCIAL_STATE_UNPUBLISHED;
	}

	/**
	 * Determines if the listing is scheduled
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isScheduled()
	{
		return $this->scheduled == SOCIAL_MARKETPLACE_SCHEDULED;
	}
}
