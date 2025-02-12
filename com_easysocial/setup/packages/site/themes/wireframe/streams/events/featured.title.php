<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($event->isGroupEvent()) { ?>
	<?php echo JText::sprintf('APP_USER_EVENTS_STREAM_USER_FEATURED_EVENT_IN_GROUP', $this->html('html.user', $actor), $this->html('html.cluster', $event), $this->html('html.cluster', $group)); ?>
<?php } else if ($event->isPageEvent()) { ?>
	<?php echo JText::sprintf('APP_USER_EVENTS_STREAM_USER_FEATURED_EVENT_IN_PAGE', $this->html('html.page', $page)); ?>
<?php } else { ?>
	<?php echo JText::sprintf('APP_USER_EVENTS_STREAM_USER_FEATURED_EVENT', $this->html('html.user', $actor), $this->html('html.cluster', $event)); ?>
<?php } ?>
