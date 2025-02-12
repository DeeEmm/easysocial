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

<?php if ($this->isMobile() && $cluster) { ?>
<a class="btn btn-es-default-o btn-sm t-lg-mb--lg" href="<?php echo $cluster->getPermalink();?>">
	&larr; <?php echo JText::sprintf('COM_EASYSOCIAL_BACK_TO_CLUSTER', ucfirst($cluster->getType()));?>
</a>
<?php } ?>


<?php if ($customFilter || $hashtags) { ?>
	<?php if ($customFilter) { ?>
	<div class="es-snackbar2">
		<div class="es-snackbar2__context">
			<div class="es-snackbar2__title">
				<?php echo JText::sprintf('COM_ES_AUDIO_CUSTOM_FILTER_HEADER_TITLE', $customFilter->title); ?>
			</div>
		</div>
		<div class="es-snackbar2__actions">
			<a href="<?php echo $customFilter->getEditLink();?>" class="btn btn-sm btn-es-default-o"
				data-audio-create-filter
				data-type="audios"
				data-id="<?php echo $customFilter->id; ?>"
				data-cluster-type="<?php echo $type; ?>"
				data-uid="<?php echo $uid; ?>"
				title="<?php echo JText::_('COM_ES_AUDIO_FILTERS_EDIT_FILTER');?>"
				><?php echo JText::_('COM_ES_EDIT'); ?></a>
		</div>
	</div>
	<?php } else { ?>
	<div class="es-snackbar2">
		<div class="es-snackbar2__context">
			<div class="es-snackbar2__title">
				<?php echo ES::tagFilter()->getLinks($hashtags, 'audios', $uid, $type); ?>
			</div>
		</div>
	</div>
	<?php } ?>

<?php } else { ?>
	<?php if (isset($filter) && $filter == 'pending') { ?>
	<div class="es-snackbar2">
		<div class="es-snackbar2__context">
			<div class="es-snackbar2__title">
				<?php echo JText::_('COM_ES_AUDIO_PENDING_TITLE');?>
			</div>
		</div>
	</div>
	<p class="pending-info"><?php echo JText::_('COM_ES_AUDIO_PENDING_INFO');?></p>
	<hr />
	<?php } ?>

	<?php if ((isset($isFeatured) && $isFeatured) || (isset($filter) && $filter == 'featured')) { ?>
		<div class="es-snackbar2">
			<div class="es-snackbar2__context">
				<div class="es-snackbar2__title">
					<?php echo JText::_("COM_ES_AUDIO_FEATURED_AUDIOS");?>
				</div>
			</div>
		</div>
	<?php } else { ?>
		<div class="es-snackbar2">
			<div class="es-snackbar2__context">
				<div class="es-snackbar2__title">
					<?php echo JText::_("COM_ES_AUDIOS");?>
				</div>
			</div>

			<?php if ($browseView) { ?>
			<div class="es-snackbar2__actions">
				<?php echo $this->html('form.popdown', 'sorting', $sort, array(
					$this->html('form.popdownOption', 'latest', 'COM_ES_SORT_BY_LATEST', '', false, $sortItems->latest->attributes, $sortItems->latest->url),
					$this->html('form.popdownOption', 'alphabetical', 'COM_ES_SORT_BY_ALPHABETICALLY', '', false, $sortItems->alphabetical->attributes, $sortItems->alphabetical->url),
					$this->html('form.popdownOption', 'popular', 'COM_ES_SORT_BY_POPULARITY', '', false, $sortItems->popular->attributes, $sortItems->popular->url),
					$this->html('form.popdownOption', 'commented', 'COM_ES_SORT_BY_MOST_COMMENTED', '', false, $sortItems->commented->attributes, $sortItems->commented->url),
					$this->html('form.popdownOption', 'likes', 'COM_ES_SORT_BY_MOST_LIKES', '', false, $sortItems->likes->attributes, $sortItems->likes->url)
				)); ?>
			</div>
			<?php } ?>
		</div>
	<?php } ?>
<?php } ?>
