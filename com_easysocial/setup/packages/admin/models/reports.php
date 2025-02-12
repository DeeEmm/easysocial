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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

// Include main model file.
ES::import( 'admin:/includes/model' );

class EasySocialModelReports extends EasySocialModel
{
	private $data			= null;

	public function __construct( $config = array() )
	{
		parent::__construct( 'reports' , $config );
	}

	public function deleteSimilarReports( $extension , $uid , $type )
	{
		$db 	= ES::db();
		$sql	= $db->sql();

		$sql->delete( '#__social_reports' );
		$sql->where( 'extension' , $extension );
		$sql->where( 'uid' , $uid );
		$sql->where( 'type' , $type );

		echo $sql->debug();exit;
	}

	/**
	 * Retrieves a list of reporters for a specific report.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getReporters( $extension , $uid , $type )
	{
		$db		= ES::db();
		$sql	= $db->sql();

		$sql->select( '#__social_reports' );
		$sql->where( 'extension' , $extension );
		$sql->where( 'uid' , $uid );
		$sql->where( 'type' , $type );

		$db->setQuery( $sql );
		$rows 		= $db->loadObjectList();

		if( !$rows )
		{
			return false;
		}

		$reports 	= array();

		foreach( $rows as $item )
		{
			$report 	= ES::table( 'Report' );
			$report->bind( $item );

			$reports[]	= $report;
		}

		return $reports;
	}

	/**
	 * Returns the total number of reports made.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getCount( $options = array()  )
	{
		static $_cache = array();

		$db 	= ES::db();
		$sql 	= $db->sql();

		$userId = isset( $options[ 'created_by' ] ) ? $options[ 'created_by' ] : '';

		if (!isset($_cache[$userId])) {
			$sql->select( '#__social_reports' );
			$sql->column( 'COUNT(1)' , 'total' );

			if( $userId )
			{
				$sql->where( 'created_by' , $userId );
			}

			$db->setQuery( $sql );

			$total	= $db->loadResult();
			$_cache[$userId] = $total;
		}

		return $_cache[$userId];
	}

	/**
	 * Retrieves the list of reports
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getReports()
	{
		$db = ES::db();

		$sql = $db->sql();
		$sql->select('#__social_reports');
		$sql->column('*');
		$sql->column('COUNT(1)', 'total');

		// We need to group up the items to ensure the unique-ness
		$sql->group('extension' , 'uid' , 'type');

		// Determines if we need to search for something
		$search = $this->getState('search');

		if ($search) {
			$sql->where('title' , '%' . $search . '%' , 'LIKE' , 'OR');
			$sql->where('message' , '%' . $search . '%' , 'LIKE' , 'OR');
		}

		// Set the total objects
		$this->setTotal($sql->getTotalSql(), true);

		// Set the ordering
		$ordering = $this->getState('ordering');

		if ($ordering) {
			$direction 	= $this->getState('direction');

			$sql->order($ordering , $direction);
		}

		// Get the real data now
		$result = parent::getData($sql->getSql());

		if (!$result) {
			return false;
		}

		$reports = array();

		foreach($result as $item) {
			$report = ES::table('Report');
			$report->bind($item);

			// Inject the total reports
			$report->total = $item->total;

			$reports[] = $report;
		}

		return $reports;
	}

	/**
	 * Purges all reports from the site
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function purge()
	{
		$db 	= ES::db();

		$sql 	= $db->sql();
		$sql->delete( '#__social_reports' );

		$db->setQuery( $sql );

		$db->Query();

		return true;
	}

	/**
	 * Retrieves the total number of reported stream from the site
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getReportCount()
	{
		$db = ES::db();
		$sql = $db->sql();

		$query = 'select count(1)';
		$query .= ' from `#__social_reports`';

		$sql->raw($query);
		$db->setQuery($sql);

		$total = (int) $db->loadResult();

		return $total;
	}	
}

