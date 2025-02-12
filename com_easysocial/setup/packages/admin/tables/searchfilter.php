<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/tables/table');

class SocialTableSearchFilter extends SocialTable
{
	/**
	 * The unique id.
	 * @var	int
	 */
	public $id = null;

	/**
	 * Element
	 * @var	string - user / group
	 */
	public $element = null;

	/**
	 * Uid - user id / group id
	 * @var	int
	 */
	public $uid = null;

	/**
	 * Title
	 * @var	int
	 */
	public $title = null;

	/**
	 * The alias of the search filter
	 * @var	string
	 */
	public $alias	 		= null;

	/**
	 * The filter data
	 * @var	json string
	 */
	public $filter	 		= null;

	/**
	 * user id who created the filter
	 * @var	int
	 */
	public $created_by	 		= null;

	/**
	 * creation date
	 * @var	datetime
	 */
	public $created	 		= null;

	/**
	 * indicate if this is a sitewide filter
	 * @var	int
	 */
	public $sitewide	 		= null;


	/**
	 * Class Constructor.
	 *
	 * @since	1.1
	 * @access	public
	 */
	public function __construct( $db )
	{
		parent::__construct( '#__social_search_filter' , 'id' , $db);
	}

	/**
	 * Override parent's store function
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function store( $updateNulls = false )
	{
		// Generate an alias for this filter if it is empty.
		if( empty( $this->alias ) )
		{
			$alias 	= $this->title;
			$alias 	= JFilterOutput::stringURLSafe( $alias );
			$tmp	= $alias;

			$i 		= 1;

			while( $this->aliasExists( $alias ) )
			{
				$alias 	= $tmp . '-' . $i;
				$i++;
			}

			$this->alias 	= $alias;
		}

		$state 	= parent::store( $updateNulls );
	}

	/**
	 * Checks the database to see if there are any same alias
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function aliasExists( $alias )
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select( '#__social_search_filter' );
		$sql->column( 'COUNT(1)' , 'total' );
		$sql->where( 'alias' , $alias );

		$db->setQuery( $sql );

		$exists 	= $db->loadResult() > 0 ? true : false;

		return $exists;
	}

	/**
	 * Determines if the user can delete this record
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canDelete($userId = null)
	{
		$my = ES::user($userId);

		if ($this->created_by != $my->id && !$my->isSiteAdmin()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user can delete this record
	 *
	 * @since	3.1.0
	 * @access	public
	 */
	public function canEdit($userId = null)
	{
		$my = ES::user($userId);

		if ($this->created_by != $my->id && !$my->isSiteAdmin()) {
			return false;
		}

		return true;
	}

	/**
	 * Retrieves the alias of this filter
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getAlias()
	{
		$alias 	= $this->id . '-' . $this->alias;

		return $alias;
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
	 * Generates the permalink for the search filter
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getPermalink($advanced = false, $xhtml = true)
	{
		$options = array('fid' => $this->getAlias(), 'type' => $this->element);

		if ($advanced) {
			$options['layout'] = 'advanced';
		}

		$permalink = ESR::search($options, $xhtml);

		return $permalink;
	}

	/**
	 * Normalize and retrieves the values for a search filter
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getSearchConfig()
	{
		$data = json_decode($this->filter);

		$config = array();
		$config['criterias'] = isset($data->{'criterias[]'} ) ? $data->{'criterias[]'} : '';
		$config['datakeys'] = isset($data->{'datakeys[]'} ) ? $data->{'datakeys[]'} : '';
		$config['operators'] = isset($data->{'operators[]'} ) ? $data->{'operators[]'} : '';
		$config['conditions'] = isset($data->{'conditions[]'} ) ? $data->{'conditions[]'} : '';

		if (!is_array($config['criterias'])) {
			$config['criterias'] = array($config['criterias']);
		}

		if (!is_array($config['datakeys'])) {
			$config['datakeys'] = array($config['datakeys']);
		}

		if (!is_array($config['operators'])) {
			$config['operators'] = array($config['operators']);
		}

		if (!is_array($config['conditions'])) {
			$config['conditions'] = array($config['conditions']);
		}

		$config['match'] = isset($data->matchType) ? $data->matchType : 'all';
		$config['avatarOnly'] = isset($data->avatarOnly) ? true : false;
		$config['sort'] = isset($data->sort) ? $data->sort : ES::config()->get('users.advancedsearch.sorting', 'default');

		return $config;
	}
}
