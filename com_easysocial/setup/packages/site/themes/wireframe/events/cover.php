<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div data-event-cover
	 <?php echo $cover->photo_id ? 'data-es-photo="' . $cover->photo_id . '"' : '';?>
	 class="es-profile-header-cover es-flyout <?php echo !empty($newCover) ? "editing" : ""; ?> <?php if (!$event->hasCover()) { ?>no-cover<?php } ?>"
	 style="
		background-image: url(<?php echo $cover->getSource();?>);
		background-position: <?php echo $cover->getPosition();?>;
	 ">

	<div class="es-cover-container">
		<div class="es-cover-viewport">

			<div
				data-cover-image
				class="es-cover-image"
				<?php if (!empty($newCover)) { ?>
				data-photo-id="<?php echo $newCover->id; ?>"
				style="background-image: url(<?php echo $newCover->getSource('large'); ?>);"
				<?php } ?>
				<?php if ($cover->id) { ?>
				data-photo-id="<?php echo $cover->getPhoto()->id; ?>"
				style="background-image: url(<?php echo $cover->getSource(); ?>);"
				<?php } ?>
			>
			</div>

			<div class="es-cover-hint">
				<span>
					<span class="o-loader o-loader--sm o-loader--inline with-text"> <?php echo JText::_('COM_EASYSOCIAL_PHOTOS_COVER_LOADING'); ?></span>
					<span class="es-cover-hint-text"><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_COVER_DRAG_HINT'); ?></span>
				</span>
			</div>

			<div class="es-cover-loading-overlay"></div>

			<?php if ($event->isAdmin() || $this->my->isSiteAdmin()){ ?>
			<div class="es-flyout-content">

				<div class="dropdown_ es-cover-menu" data-cover-menu>
					<a href="javascript:void(0);" data-es-toggle="dropdown" class="dropdown-toggle_ es-flyout-button">
						<i class="fa fa-cog"></i><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_EDIT_COVER');?></span>
					</a>
					<ul class="dropdown-menu es-cover-dropdown-menu">
						<li data-cover-upload-button>
							<a href="javascript:void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_UPLOAD_COVER"); ?></a>
						</li>
						<li data-cover-select-button>
							<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_SELECT_COVER'); ?></a>
						</li>
						<li data-cover-edit-button>
							<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_PHOTOS_REPOSITION_COVER'); ?></a>
						</li>
						<li class="divider for-cover-remove-button"></li>
						<li data-cover-remove-button>
							<a href="javascript:void(0);"><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_REMOVE_COVER"); ?></a>
						</li>
					</ul>
				</div>

				<a href="javascript:void(0);"
				   class="es-cover-done-button es-flyout-button"
				   data-cover-done-button><i class="fa fa-check"></i><?php echo JText::_("COM_EASYSOCIAL_PHOTOS_COVER_DONE"); ?></a>

				<a href="javascript:void(0);"
				   class="es-cover-cancel-button es-flyout-button"
				   data-cover-cancel-button><i class="fa fa-times"></i><?php echo JText::_("COM_ES_CANCEL"); ?></a>
			</div>
			<?php } ?>

		</div>
	</div>
</div>
