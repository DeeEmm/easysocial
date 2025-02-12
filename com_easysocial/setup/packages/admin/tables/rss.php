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

// Import main table to assis this table.
ES::import('admin:/tables/table');

class SocialTableRss extends SocialTable
{
	/**
	 * The unique task id.
	 * @var	int
	 */
	public $id 		= null;

	/**
	 * The owner of the task.
	 * @var	int
	 */
	public $user_id	= null;

	/**
	 * The owner of the task.
	 * @var	int
	 */
	public $uid	= null;

	/**
	 * The owner of the task.
	 * @var	int
	 */
	public $type	= null;

	/**
	 * The feed title.
	 * @var	string
	 */
	public $title 	= null;

	/**
	 * The feed description.
	 * @var	string
	 */
	public $description 	= null;

	/**
	 * The feed url.
	 * @var	string
	 */
	public $url 	= null;

	/**
	 * The state of the task
	 * @var	string
	 */
	public $state 	= null;

	/**
	 * The date time this task has been created.
	 * @var	datetime
	 */
	public $created 	= null;

	/**
	 * Class Constructor.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __construct(& $db )
	{
		parent::__construct( '#__social_rss' , 'id' , $db );
	}

	/**
	 * Overrides the delete function
	 *
	 * @since	1.0
	 * @access	public
	 * @return	bool
	 */
	public function delete( $pk = null )
	{
		$state 		= parent::delete();

		// Delete any items that are related to this stream
		$stream 	= ES::stream();
		$stream->delete( $this->id , 'feeds' );

		return $state;
	}

	/**
	 * Initializes the parser
	 *
	 * @since	2.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getParser()
	{
		static $parsers	= array();

		if (!isset($parsers[$this->id]))
		{
			$model = ES::model('RSS');
			$parser = $model->getParser($this->url);

		    $parsers[$this->id] = $parser;
		}

		return $parsers[$this->id];
	}

	/**
	 * Creates a new stream for the feed
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function createStream($verb)
	{
		// Add activity logging when a friend connection has been made.
		// Activity logging.
		$stream				= ES::stream();
		$streamTemplate		= $stream->getTemplate();

		// Set the cluster
		if ($this->type == SOCIAL_TYPE_GROUP || $this->type == SOCIAL_TYPE_PAGE) {
			$streamTemplate->setCluster($this->uid, $this->type, 1);
		}

		// Set the actor.
		$streamTemplate->setActor($this->user_id, SOCIAL_TYPE_USER);

		// Set the context.
		$streamTemplate->setContext($this->id, 'feeds');

		// Set the verb.
		$streamTemplate->setVerb($verb);

		// Set the public stream
		$streamTemplate->setPublicStream('core.view');

		// Set the params to offload the loading
		$streamTemplate->setParams($this);

		// Create the stream data.
		$stream->add($streamTemplate);
	}

	/**
	 * Shorthand to get the permalink of this note.
	 *
	 * @since  2.1.0
	 * @access public
	 */
	public function getPermalink($external = false, $xhtml = true, $sef = true, $adminSef = false)
	{
		static $apps = array();

		$cluster = ES::cluster($this->type, $this->uid);

		if (!isset($apps[$this->type])) {
			$apps[$this->type] = $cluster->getApp('feeds');
		}

		$options = array();
		$options['layout'] = 'canvas';
		$options['customView'] = 'item';
		$options['uid'] = $cluster->getAlias();
		$options['type'] = $cluster->getType();
		$options['id'] = $apps[$this->type]->getAlias();
		$options['rssid'] = $this->id;
		$options['sef'] = $sef;
		$options['adminSef'] = $adminSef;

		$permalink = ESR::apps($options, $xhtml);

		return $permalink;
	}
}
