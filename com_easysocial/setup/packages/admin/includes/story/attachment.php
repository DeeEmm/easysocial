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

class SocialStoryAttachment extends SocialStoryPlugin
{
	public $button;

	/**
	 * Class constructor.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function __construct($name, $story)
	{
		parent::__construct($name, $story);

		$this->type = 'attachment';

		$this->button = new stdClass();
		$this->button->classname = 'for-' . $name;
		$this->button->html = '';

		$this->icon = new stdClass();
		$this->icon->classname = 'for-' . $name;
		$this->icon->html = '';

		$this->content = new stdClass();
		$this->content->classname = 'for-' . $name;
		$this->content->html = '';
	}
}
