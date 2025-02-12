<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>

<?php if ($page->isOpen()) { ?>
<span data-original-title="<?php echo JText::_('COM_ES_PAGES_PUBLIC_PAGE_TOOLTIP');?>" data-es-provide="tooltip" data-placement="<?php echo $placement;?>">
	<?php if ($showIcon) { ?><i class="fa fa-globe-americas"></i>&nbsp;&nbsp;<?php } ?>
	<?php echo JText::_('COM_ES_CLUSTER_TYPE_PUBLIC'); ?>
</span>
<?php } ?>

<?php if ($page->isClosed()) { ?>
<span data-original-title="<?php echo JText::_('COM_EASYSOCIAL_PAGES_PRIVATE_PAGE_TOOLTIP');?>" data-es-provide="tooltip" data-placement="<?php echo $placement;?>">
	<?php if ($showIcon) { ?><i class="fa fa-lock"></i>&nbsp;&nbsp;<?php } ?>
	<?php echo JText::_('COM_ES_CLUSTER_TYPE_PRIVATE'); ?>
</span>
<?php } ?>

<?php if ($page->isInviteOnly()) { ?>
<span data-original-title="<?php echo JText::_('COM_EASYSOCIAL_PAGES_INVITE_PAGE_TOOLTIP');?>" data-es-provide="tooltip" data-placement="<?php echo $placement;?>">
	<?php if ($showIcon) {?><i class="fa fa-envelope"></i>&nbsp;&nbsp;<?php } ?>
	<?php echo JText::_('COM_ES_CLUSTER_TYPE_INVITE_ONLY'); ?>
</span>
<?php } ?>
