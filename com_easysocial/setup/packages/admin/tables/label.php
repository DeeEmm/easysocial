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

// Load parent table.
ES::import( 'admin:/tables/table' );

/**
 * Object mapping for `#__social_labels`.
 *
 * @author	Mark Lee <mark@stackideas.com>
 * @since	1.0
 */
class SocialTableLabel extends SocialTable
{
	/**
	 * The unique id which is auto incremented.
	 * @var int
	 */
	public $id					= null;

	/**
	 * The command string.
	 * @var string
	 */
	public $created_by 			= null;

	/**
	 * The title of the points
	 * @var string
	 */
	public $title				= null;

	/**
	 * The extension / app name.
	 * @var string
	 */
	public $alias 			= null;

	/**
	 * The extension / app name.
	 * @var string
	 */
	public $state 			= null;

	/**
	 * Creation date of the list.
	 * @var datetime
	 */
	public $created				= null;

	/**
	 * The frequency for this badge before user achieves this.
	 * @var int
	 */
	public $params			= null;


	/**
	 * Class construct
	 *
	 * @since	1.0
	 * @param	JDatabase
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__social_labels' , 'id' , $db );
	}

	/**
	 * Retrieve the badge permalink
	 *
	 * @since	1.0
	 * @access	public
	 * @return	int		The total number of users who achieved this badge.
	 */
	public function getPermalink( $xhtml = false )
	{
		$url 	= FRoute::_( 'index.php?option=com_easysocial&view=labels&layout=item&id=' . $this->id , $xhtml );

		return $url;
	}

	/**
	 * Retrieves the author of the label
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getAuthor()
	{
		$user 	= ES::user( $this->created_by );

		return $user;
	}


	/**
	 * Gets the total number of times this label is used.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getUsage()
	{
		$model 		= ES::model( 'Labels' );
		$usage 		= $model->getUsage( $this->id );

		return $usage;
	}

	/**
	 * Perform validation checks for this record.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function check()
	{
		if( empty( $this->title ) )
		{
			$this->setError( JText::_( 'COM_EASYSOCIAL_LABELS_VALIDATION_LABEL_TITLE_CANNOT_EMPTY' ) );
			return false;
		}

		if( empty( $this->created_by ) )
		{
			$this->setError( JText::_( 'COM_EASYSOCIAL_LABELS_VALIDATION_LABEL_AUTHOR_CANNOT_EMPTY' ) );
			return false;
		}
	}
}
