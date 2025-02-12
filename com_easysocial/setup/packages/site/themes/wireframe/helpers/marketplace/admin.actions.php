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
<li>
	<a href="<?php echo $listing->getEditLink();?>">
		<?php echo ($listing->isDraft()) ? JText::_('COM_ES_MARKETPLACES_REVIEW_LISTING') : JText::_('COM_ES_MARKETPLACES_EDIT_LISTING'); ?>
	</a>
</li>

<?php if ($this->my->isSiteAdmin() && !$listing->isDraft()) { ?>
	<?php if ($listing->isFeatured()) { ?>
	<li>
		<a href="javascript:void(0);" data-es-marketplaces-unfeature data-id="<?php echo $listing->id;?>" data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_ES_MARKETPLACES_UNFEATURE_LISTING');?></a>
	</li>
	<?php } else { ?>
	<li>
		<a href="javascript:void(0);" data-es-marketplaces-feature data-id="<?php echo $listing->id;?>" data-return="<?php echo $returnUrl;?>"><?php echo JText::_('COM_ES_MARKETPLACES_FEATURE_LISTING');?></a>
	</li>
	<?php } ?>
<?php } ?>

<?php if ($showAdminAction) { ?>
	<li class="divider"></li>
	<?php echo $listingAdminStart; ?>
	<?php echo $listingAdminEnd; ?>
<?php } ?>

<li class="divider"></li>

<?php if ($listing->canMarkAsSold()) { ?>
	<li>
		<a href="javascript:void(0);" data-es-marketplaces-sold data-id="<?php echo $listing->id;?>"><?php echo JText::_('COM_ES_MARKETPLACES_MARK_SOLD_LISTING');?></a>
	</li>
<?php } ?>

<?php if ($listing->canMarkAvailable()) { ?>
	<li>
		<a href="javascript:void(0);" data-es-marketplaces-available data-id="<?php echo $listing->id;?>"><?php echo JText::_('COM_ES_MARKETPLACES_MARK_AVAILABLE_LISTING');?></a>
	</li>
<?php } ?>

<?php if ($listing->canDelete()) { ?>
	<li>
		<a href="javascript:void(0);" data-es-marketplaces-delete data-id="<?php echo $listing->id;?>"><?php echo JText::_('COM_ES_MARKETPLACES_DELETE_LISTING');?></a>
	</li>
<?php } ?>
