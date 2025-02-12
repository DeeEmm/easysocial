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
?>
<?php if (!empty($stream->with)) {

	$str = JText::_('COM_EASYSOCIAL_STREAM_STORY_WITH') . ' ';
	$last = count($stream->with) - 1;

	foreach ($stream->with as $i => $user) {

		$user = $stream->with[$i];

		if (!$user->isBlock()) {
			$username = $this->html( 'html.user' , $user->id , true );
		} else {
			$username = $user->getName();
		}

		$str .= $username;

		if ($i !== $last) {
			$str .= ($i < $last - 1) ? ', ' : ' ' . JText::_('COM_EASYSOCIAL_AND') . ' ';
		}
	}

	echo $str;
} ?>
