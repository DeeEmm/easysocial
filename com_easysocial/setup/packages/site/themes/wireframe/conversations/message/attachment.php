<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($attachments && $this->config->get('conversations.attachments.enabled')) { ?>
<div class="conversation-attachments">
	<div class="es-convo__attached-list" data-es-attachment-wrapper>
		<?php foreach ($attachments as $attachment) { ?>
			<div class="es-convo__attached-item" data-es-attachment>
				<?php if ($attachment->hasPreview()) { ?>
				<a href="<?php echo $attachment->getURI();?>" target="_blank" data-title="<?php echo $this->html('string.escape', $attachment->name);?>" data-lightbox="attachment-<?php echo $attachment->id;?>">
					<img src="<?php echo $attachment->getPreviewURI();?>" />
					<i class="es-convo__attached-magnify-icon fa fa-search"></i>
				</a>
				<?php } ?>
				<a href="<?php echo $attachment->getPreviewURI(); ?>" class="es-convo__attached-link" target="_blank">
					<span class="es-convo__attached-name"><?php echo $attachment->name; ?></span>
					<span class="es-convo__attached-size">(<?php echo $attachment->getSize('kb'); ?> <?php echo JText::_('COM_EASYSOCIAL_UNIT_KILOBYTES'); ?>)</span>
				</a>
				<div class="o-btn-group">
					<a href="<?php echo $attachment->getPermalink(); ?>" class="btn btn-es-default-o btn-sm">
						<i class="fa fa-download"></i>
					</a>
					<?php if ($attachment->isOwner($this->my->id)) { ?>
					<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm"
					   data-es-attachment-delete data-id="<?php echo $attachment->id; ?>">
						<i class="fa fa-times"></i>
					</a>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
<?php } ?>
