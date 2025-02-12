<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($listing->type === SOCIAL_TYPE_GROUP) { ?>
	<span data-original-title="<?php echo JText::_('COM_ES_MARKETPLACES_GROUP_MARKETPLACE', true);?>" data-es-provide="tooltip" data-placement="<?php echo $placement;?>">
		<?php if ($showIcon) {?><i class="fa fa-users"></i>&nbsp;&nbsp;<?php } ?>
		<?php echo JText::_('COM_ES_MARKETPLACES_GROUP_MARKETPLACE'); ?>
	</span>

<?php } else if ($listing === SOCIAL_TYPE_PAGE) { ?>
	<span data-original-title="<?php echo JText::_('COM_ES_MARKETPLACES_PAGE_MARKETPLACE', true);?>" data-es-provide="tooltip" data-placement="<?php echo $placement;?>">
		<?php if ($showIcon) {?><i class="fa fa-users"></i>&nbsp;&nbsp;<?php } ?>
		<?php echo JText::_('COM_ES_MARKETPLACES_PAGE_MARKETPLACE'); ?>
	</span>
<?php } ?>
