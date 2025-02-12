<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($actor->getType() != SOCIAL_TYPE_PAGE) { ?>
	<?php echo JText::sprintf('APP_VIDEOS_STREAM_PAGES_CREATE_TITLE_' . $stream->getPerspective(), $this->html('html.user', $actor), $this->html('html.page', $page)); ?>
<?php } else { ?>
	<?php echo JText::sprintf('APP_VIDEOS_STREAM_PAGES_CREATE_TITLE_PAGES', $this->html('html.page', $page)); ?>
<?php } ?>

