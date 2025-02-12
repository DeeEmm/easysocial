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
<?php if ($listing->type == SOCIAL_TYPE_GROUP) { ?>
	<?php echo JText::sprintf('APP_USER_MARKETPLACES_STREAM_USER_' . strtoupper($verb) . '_LISTING_IN_GROUP', $this->html('html.user', $actor), $this->html('html.marketplace', $listing), $this->html('html.group', $group)); ?>
<?php } else if ($listing->type == SOCIAL_TYPE_PAGE) { ?>
	<?php if ($actor->getType() != SOCIAL_TYPE_PAGE) { ?>
		<?php echo JText::sprintf('APP_USER_MARKETPLACES_STREAM_USER_' . strtoupper($verb) . '_LISTING_IN_PAGE', $this->html('html.page', $page), $this->html('html.user', $actor)); ?>
	<?php } else { ?>
		<?php echo JText::sprintf('APP_USER_MARKETPLACES_STREAM_USER_' . strtoupper($verb) . '_LISTING', $this->html('html.page', $page)); ?>
	<?php } ?>
<?php } else { ?>
	<?php echo JText::sprintf('APP_USER_MARKETPLACES_STREAM_USER_' . strtoupper($verb) . '_LISTING', $this->html('html.user', $actor), $this->html('html.marketplace', $listing)); ?>
<?php } ?>
