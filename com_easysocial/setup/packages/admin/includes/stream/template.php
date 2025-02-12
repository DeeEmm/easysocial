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

class SocialStreamTemplate extends JObject
{
	public $actor_id = null;
	public $actor_type = '';
	public $post_as = null;
	public $context_id = null;
	public $context_type = null;
	public $stream_type	= null;
	public $verb = null;
	public $target_id = 0;
	public $title = null;
	public $content = null;
	public $uid = 0;
	public $created = null;
	public $sitewide = null;
	public $location_id = null;
	public $with = null;
	public $isAggregate = null;
	public $aggregateWithTarget = null;
	public $isPublic = null;
	public $privacy_id = null;
	public $access = null;
	public $custom_access = null;
	public $field_access = null;
	public $params = null;
	public $item_params = null;
	public $childs = null;
	public $_public_rule = null;
	public $mentions = null;
	public $cluster_id = null;
	public $cluster_type = null;
	public $cluster_access = null;
	public $state = SOCIAL_STREAM_STATE_PUBLISHED;
	public $alias = null;
	public $anywhere_id = null;
	public $background_id = null;
	public $giphy = null;
	public $scheduled = null;

	public function __construct()
	{
		// Set the creation date to the current time.
		$date = ES::date();
		$this->created = $date->toMySQL();
		$this->sitewide = '0';
		$this->isAggregate = false;
		$this->aggregateWithTarget	= false;
		$this->isPublic = 0;
		$this->childs = array();
		$this->cluster_access = 0;
		$this->scheduled = false;

		//reset the _public_rule holder;
		$this->_public_rule = null;
	}

	/**
	 * Sets the background id
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function setBackground($id)
	{
		$this->background_id = $id;
	}

	public function setCluster($id, $type, $access = 1)
	{
		$this->cluster_id = $id;
		$this->cluster_type = $type;
		$this->cluster_access = $access;
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function setContent($content)
	{
		$this->content = $content;
	}

	/**
	 * Sets the actor object.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setActor($id, $type)
	{
		// Set actors id
		$this->actor_id 	= $id;

		// Set actors type
		$this->actor_type	= $type;
	}

	/**
	 * Sets the post as.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setPostAs($type)
	{
		$this->post_as = $type;
	}

	/**
	 * Sets the current hash url.
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function setCurrentUrl($hash)
	{
		$this->anywhere_id = $hash;
	}

	/**
	 * Sets the mood in the stream
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function setMood(SocialTableMood &$mood)
	{
		if (!$mood->id) {
			return;
		}

		$this->mood_id	= $mood->id;
	}

	/**
	 * Sets the context of this stream
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setContext($id, $type, $params = null)
	{
		// Set the context id.
		$this->context_id = $id;

		// Set the context type.
		$this->context_type = $type;

		if ($params) {


			if (is_string($params)) {
				$this->item_params = $params;
			}

			if ($params instanceof SocialRegistry) {
				$this->item_params = $params->toString();
			}

			// If the params is still empty, we just treat it as an object or string
			if (!$this->item_params && (is_array($params) || is_object($params))) {
				$this->item_params = json_encode($params);
			}
		}
	}

	/**
	 * Sets the verb of the stream item.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The verb
	 */
	public function setVerb( $verb )
	{
		// Set the verb property.
		$this->verb = $verb;
	}

	/**
	 * Sets the target id.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The target id
	 */
	public function setTarget( $id )
	{
		$this->target_id 	= $id;
	}

	/**
	 * Sets the stream location
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function setLocation( $location = null )
	{
		if( !is_null( $location ) && is_object( $location ) )
		{
			$this->location_id 	= $location->id;
		}
	}


	/**
	 * Sets the users in the stream
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function setWith( $ids = '' )
	{
		$this->with 	= $ids;
	}

	/**
	 * Sets mentions
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function setMentions( $mentions )
	{
		$this->mentions 	= $mentions;
	}

	/**
	 * Sets the state of the stream
	 *
	 * @since	1.3
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function setState($state)
	{
		$this->state = $state;
	}

	/**
	 * Sets the stream type.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	int		The target id
	 */
	public function setType( $type = 'full' )
	{
		$this->stream_type 	= $type;
	}

	public function setSiteWide( $isSideWide = true )
	{
		$this->sitewide = $isSideWide;
	}

	public function setAggregate( $aggregate = true, $aggregateWithTarget = false )
	{
		// when this is true, it will aggregate based on current context and verb.
		$this->isAggregate = $aggregate;

		// when this is true, it will aggregate based on the target_id as well.
		$this->aggregateWithTarget = $aggregateWithTarget;
	}

	public function setDate( $mySQLdate )
	{
		$this->created = $mySQLdate;
	}

	/*
	 * deprecated since 1.2.16
	 */
	public function setPublicStream( $keys, $privacy = null )
	{
		$holder = array( 'key' 		=> $keys,
						 'value' 	=> $privacy );

		$this->_public_rule = $holder;
	}

	/**
	 * Set stream access.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string		privacy rule. e.g. core.view
	 *          int			privacy value in integer
	 *          array 		user ids
	 */
	public function setAccess($keys, $privacy = null, $custom = null, $field = null)
	{
		$holder = array( 'key' 		=> $keys,
						 'value' 	=> $privacy,
						 'custom' => $custom,
						 'field' => $field );

		$this->_public_rule = $holder;
	}

	public function bindStreamAccess()
	{
		if (! $this->actor_id) { // this is a guest.
			// let get the privacy id for the keys that passed in.
			$keys = $this->_public_rule['key'];

			$rules 	= explode( '.', $keys );
			$key  	= array_shift( $rules );
			$rule 	= implode( '.', $rules );

			$currentRule = ES::table('Privacy');
			$currentRule->load(array('type'=>$key, 'rule' => $rule));

			if (! $currentRule->id) {
				// lets load the core.view privacy.
				$currentRule->load(array('type'=> 'core', 'rule' => 'view'));
			}

			$this->privacy_id = $currentRule->id;
			$this->access = 0; // always default to public
			$this->custom_access = '';
			$this->field_access = '';

		} else {

			$privacyLib = ES::privacy( $this->actor_id );

			$privacyData = $privacyLib->getData();

			$core = $privacyData['core']['view'];

			if ($this->_public_rule) {
				$keys = $this->_public_rule['key'];
				$access = $this->_public_rule['value'];
				$custom = isset($this->_public_rule['custom']) ? $this->_public_rule['custom'] : '';
				$field = isset($this->_public_rule['field']) ? $this->_public_rule['field'] : '';

				if ($this->actor_type == SOCIAL_STREAM_ACTOR_TYPE_USER) {
					// we need to test the user privacy for this rule.
					$rules 	= explode( '.', $keys );
					$key  	= array_shift( $rules );
					$rule 	= implode( '.', $rules );

					// if current passed in rule not found, we will use the core.view instead.
					$currentRule = $core;
					if( isset($privacyData[$key]) && isset($privacyData[$key][$rule])) {
						$currentRule = $privacyData[$key][$rule];
					}

					$this->privacy_id = $currentRule->id;
					$this->access = (! is_null($access)) ? $access : $currentRule->default;
					$this->custom_access = '';
					$this->field_access = '';


					if ($this->access == SOCIAL_PRIVACY_CUSTOM) {
						$tmp = array();

						if ($custom) {
							$tmp = $custom;
						} else if($currentRule->custom) {
							foreach( $currentRule->custom as $cc) {
								$tmp[] = $cc->user_id;
							}
						}

						if ($tmp) {
							$this->custom_access = ',' . implode(',', $tmp) . ',';
						}
					}

					if ($this->access == SOCIAL_PRIVACY_FIELD) {
						if ($field) {
							$this->field_access = $field;
						}
					}

				}

			} else {

				$this->privacy_id = $core->id;
				$this->access = !empty($access) ? $access : $core->default;
				$this->custom_access = '';
				$this->field_access = '';

				if ($this->access == SOCIAL_PRIVACY_CUSTOM) {
					$tmp = array();

					if ($custom) {
						$tmp = $custom;
					} else if($core->custom) {
						foreach( $core->custom as $cc) {
							$tmp[] = $cc->user_id;
						}
					}

					if ($tmp) {
						$this->custom_access = ',' . implode(',', $tmp) . ',';
					}
				}

				if ($this->access == SOCIAL_PRIVACY_FIELD) {
					if ($field) {
						$this->field_access = $field;
					}
				}
			}
		}
	}

	/**
	 * Sets the stream params
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function setParams($params)
	{
		if (!$params) {
			return;
		}

		if (!is_string($params)) {
			if ($params instanceof SocialRegistry) {
				$this->params = $params->toString();
			} else {
				$this->params = json_encode($params);
			}
		} else {
			$this->params = $params;
		}
	}

	/*
	 * This functin allow user to aggreate items of same type ONLY in within one stream.
	 * when there are child items, the isAggreate will be off by default when processing streams aggregation.
	 * E.g. of when this function take action:
	 *		Imagine if you wanna agreate photos activity logs for one single stream but DO NOT wish to aggregate with other photos stream.
	 *      If that is the case, then you will need to use this function so that stream lib will only aggreate the photos items in this single stream.
	 */
	public function setChild( $contextId )
	{
		if ($contextId) {
			$this->childs[] = $contextId;
		}
	}

	/**
	 * Experimental usage of alias as poster
	 *
	 * @since   2.0
	 * @access  public
	 */
	public function setAlias($posterId)
	{
		$this->alias = $posterId;
	}

	/**
	 * Set scheduled
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public function setScheduled($scheduled)
	{
		$date = JFactory::getDate($scheduled, ES::date()->getOffSetObject());
		$date->setTimezone(new DateTimeZone('GMT'));

		// We'll need to format the scheduled datetime to GMT.
		$this->scheduled = $date->toSql();
	}

	/**
	 * Check scheduled.
	 *
	 * @since   3.3.0
	 * @access  public
	 */
	public function isScheduled()
	{
		return $this->scheduled ? true : false;
	}
}
