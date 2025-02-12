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

class SocialTableCluster extends SocialTable
{
	/**
	 * The unique id of the cluster
	 * @var int
	 */
	public $id = null;

	/**
	 * The category id of the cluster.
	 * @var string
	 */
	public $category_id = null;

	/**
	 * Determines the cluster type
	 * @var string
	 */
	public $cluster_type = null;

	/**
	 * The owner type of this cluster
	 * @var string
	 */
	public $creator_type = null;

	/**
	 * The owner unique id for this cluster
	 * @var int
	 */
	public $creator_uid = null;

	/**
	 * The title of this cluster
	 * @var string
	 */
	public $title = null;

	/**
	 * The description of this cluster
	 * @var string
	 */
	public $description = null;

	/**
	 * The alias for this cluster. Used for SEF
	 * @var string
	 */
	public $alias = '';

	/**
	 * The state of the cluster
	 * @var int
	 */
	public $state = null;

	/**
	 * The 'is new' state of the cluster
	 * @var int
	 */
	public $isnew = null;

	public $featured = null;

	/**
	 * The creation date of this cluster
	 * @var datetime
	 */
	public $created = null;

	/**
	 * JSON string that is used as params
	 * @var string
	 */
	public $params = null;

	/**
	 * Total number of hits this cluster obtained
	 * @var int
	 */
	public $hits = null;

	/**
	 * The type of this cluster. Whether it is a private / public / invite only
	 * @var string
	 */
	public $type = null;

	/**
	 * The notification type of this cluster. Whether it is a internal / email / both / off
	 * @var string
	 */
	public $notification = null;

	/**
	 * The secret key for this group for admin actions.
	 * @var string
	 */
	public $key = null;

	/**
	 * Parent id of this cluster.
	 * @var integer
	 */
	public $parent_id = null;

	/**
	 * Parent type of this cluster.
	 * @var string
	 */
	public $parent_type = null;

	/**
	 * Longitude value of this cluster.
	 * @var float
	 */
	public $longitude = null;

	/**
	 * Latitude value of this cluster.
	 * @var float
	 */
	public $latitude = null;

	/**
	 * Address of this cluster.
	 * @var string
	 */
	public $address = null;

	public $verified = 0;

	public function __construct(&$db)
	{
		parent::__construct('#__social_clusters', 'id', $db);
	}

	/**
	 * Override parent's bind implementation
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function bind($data, $ignore = array())
	{
		$state = parent::bind($data, $ignore);

		if (!$this->id) {
			$this->isnew = 1;
		}

		return $state;
	}

	public function load($keys = null, $reset = true)
	{
		if (! is_array($keys)) {

			// attempt to get from cache
			$catKey = 'cluster.'. $keys;

			if (ES::cache()->exists($catKey)) {
				$state = parent::bind(ES::cache()->get($catKey));
				return $state;
			}
		}

		$state = parent::load($keys, $reset);
		return $state;
	}

	/**
	 * Get the alias of this cluster.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function getAlias()
	{
		$alias = $this->id;

		// Ensure that the name is a safe url.
		if ($this->alias) {
			$alias .= ':' . JFilterOutput::stringURLSafe($this->alias);
		}

		return $alias;
	}

	/**
	 * Retrieves the cluster type (plural)
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getClusterTypePlural()
	{
		return $this->cluster_var;
	}

	/**
	 * Determines if the cluster is verified
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function isVerified()
	{
		$enabled = $this->config->get($this->getClusterTypePlural() . '.verification');

		if (!$enabled) {
			return false;
		}

		return $this->verified ? true : false;
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
		$old = ES::table('Cluster');
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
}
