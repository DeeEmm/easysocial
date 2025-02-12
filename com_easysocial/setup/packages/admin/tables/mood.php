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
 * Object mapping for `#__social_moods`.
 *
 * @author	Mark Lee <mark@stackideas.com>
 * @since	1.2
 */
class SocialTableMood extends SocialTable
{
	/**
	 * The unique id which is auto incremented.
	 * @var int
	 */
	public $id					= null;

	/**
	 * This determines the namespace of the owning item. For instance, if this mood is associated with a stream, stream.user.create
	 * @var string
	 */
	public $namespace 			= null;

	/**
	 * This determines the namespace of the owning item. For instance, if this mood is associated with a stream, it should use the stream id
	 * @var int
	 */
	public $namespace_uid 		= null;

	/**
	 * This should contain the class name for the emoticon
	 * @var string
	 */
	public $icon 			= null;

	/**
	 * This should contain the verb of the mood. Example: Feeling, Listening, Playing
	 * @var string
	 */
	public $verb 			= null;

	/**
	 * This should contain the subject of the mood. Example: Happy, Sad, Angry
	 * @var string
	 */
	public $subject 		= null;

	/**
	 * This should determine if this mood is a custom mood
	 * @var bool
	 */
	public $custom 			= null;

	/**
	 * If the mood is a custom mood, it should use the text
	 * @var string
	 */
	public $text 			= null;

	/**
	 * The owner of the mood
	 * @var int
	 */
	public $user_id			= null;

	/**
	 * The creation date of the mood
	 * @var datetime
	 */
	public $created			= null;

	/**
	 * Class construct
	 *
	 * @since	1.0
	 * @param	JDatabase
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__social_moods' , 'id' , $db );
	}

	/**
	 * Override parent's implementation
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return	bool		True on success false otherwise
	 */
	public function bindPost($post = array())
	{
		$state		= parent::bind($post);

		$icon		= isset($post['mood_icon']) ? $post['mood_icon'] : $this->icon;
		$verb		= isset($post['mood_verb']) ? $post['mood_verb'] : $this->verb;
		$subject	= isset($post['mood_subject']) ? $post['mood_subject'] : $this->subject;
		$custom		= isset($post['mood_custom']) ? $post['mood_custom'] : $this->custom;
		$text		= isset($post['mood_text']) ? $post['mood_text'] : $this->text;

		// The "custom" string is being passed as "true" or "false" string and not a boolean value.
		$custom 	= $custom == "true" ? true : false;

		// If there's always no icon, and not a custom mood skip this
		if (empty($icon) && !$custom) {
			return false;
		}

		// The text must never be empty
		if ($custom && empty($text)) {
			return false;
		}

		// If this is a predefined one, verb has to exist
		if (!$custom && empty($subject)) {
			return false;
		}

		$this->icon 	= $icon;
		$this->verb 	= $verb;
		$this->subject	= $subject;
		$this->custom 	= $custom ? true : false;
		$this->text 	= $text;

		return true;
	}

	/**
	 * Returns the icon class
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function getIconClass()
	{
		return 'es-emoji ' . $this->icon;
	}

	public function getText()
	{
		if ($this->custom && $this->text) {
			return $this->text;
		}

		$user		= ES::user($this->user_id);

		// Get the term to be displayed
		$value		= $user->getFieldData('GENDER');

		$term		= 'NOGENDER';

		if ($value == 1) {
			$term = 'MALE';
		}

		if ($value == 2) {
			$term = 'FEMALE';
		}

		// If this is not a custom value we need to merge the verb and the subject
		$verb 		= strtoupper($this->verb);
		$subject	= strtoupper($this->subject);

		$text 	= JText::_('COM_EASYSOCIAL_MOOD_' . $verb . '_' . $subject . '_' . $term);
		// dump($this->verb, $this->subject);

		// $text 	= $this->verb . ' ' . $this->subject;

		return $text;
	}

	public function toExportData($viewer)
	{
		$mood = new stdClass();
		$mood->subject = $this->subject;
		$mood->text = $this->getText();

		return $mood;
	}
}
