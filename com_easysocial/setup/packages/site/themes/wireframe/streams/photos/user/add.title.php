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
?>
<?php echo JText::sprintf(ES::string()->computeNoun('APP_USER_PHOTOS_STREAM_UPLOADED_PHOTO_IN_ALBUM', $count), $this->html('html.user', $actor->id)
							, '<a href="' . $album->getPermalink() . '">' . $album->get( 'title' ) . '</a>'
							, $count );
