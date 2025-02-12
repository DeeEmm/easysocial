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

class SocialStoryPlugin
{
	public $id = null;
	public $story = null;
	public $name = '';
	public $type = 'plugin';
	public $script = '';
	public $title = '';

	public function __construct($name, $story)
	{
		$this->name = $name;
		$this->story = $story;
		$this->id = uniqid();
		$this->title = JText::_('COM_EASYSOCIAL_STORY_' . strtoupper($this->name));
	}
}
