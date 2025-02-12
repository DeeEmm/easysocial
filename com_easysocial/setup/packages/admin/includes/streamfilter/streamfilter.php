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

class SocialStreamFilter extends EasySocial
{
	private $type = null;

	private $hashtag = null;
	private $hashtagAlias = null;
	
	private $cluster = null;

	private $canCreateFilter = false;

	private $active = null;
	private $activeFilterId = null;

	private $appFilters = array();
	private $customFilters = array();

	public function __construct($type = SOCIAL_TYPE_USER, $canCreateFilter = false)
	{
		$this->type = $type;
		$this->canCreateFilter = $canCreateFilter;

		parent::__construct();
	}

	/**
	 * Retrieves the namespace to the stream filter template file
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function getNamespace()
	{
		$namespace = 'site/stream/filter/' . $this->type;

		return $namespace;
	}

	/**
	 * Generates the activity stream filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function html()
	{
		$theme = ES::themes();
		
		$theme->set('active', $this->active);
		$theme->set('hashtag', $this->hashtag);
		$theme->set('hashtagAlias', $this->hashtagAlias);
		$theme->set('customFilters', $this->customFilters);
		$theme->set('activeFilterId', $this->activeFilterId);
		$theme->set('canCreateFilter', $this->canCreateFilter);
		$theme->set('appFilters', $this->appFilters);
		$theme->set('cluster', $this->cluster);

		$namespace = $this->getNamespace();
		$output = $theme->output($namespace);

		return $output;
	}

	/**
	 * Sets an active hashtag if there is any
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function setActiveHashtag($hashtag = null, $hashtagAlias = null)
	{
		$this->hashtag = $hashtag;
		$this->hashtagAlias = $hashtagAlias ? $hashtagAlias : $hashtag;
	}

	/**
	 * Sets the current active filter
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function setActiveFilter($filter, $filterId = null)
	{
		$this->active = $filter;

		// Use only when custom filter is active
		$this->activeFilterId = $filterId;
	}

	/**
	 * Sets the list of app filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function setAppFilters($filters = array())
	{
		$this->appFilters = $filters;
	}

	/**
	 * Sets a list of custom filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function setCustomFilters($filters = array())
	{
		$this->customFilters = $filters;
	}

	/**
	 * Sets a list of custom filters
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function setCluster($cluster)
	{
		$this->cluster = $cluster;
	}
}