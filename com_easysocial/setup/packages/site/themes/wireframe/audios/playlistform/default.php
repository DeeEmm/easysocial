<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php echo $this->html('cover.user', $this->my); ?>

<form id="playlistForm" method="post" action="<?php echo JRoute::_('index.php');?>" class="es-forms">
	<div class="es-forms__group">
		<div class="es-forms__title">
			<?php echo $this->html('form.title', $list->id ? 'COM_ES_AUDIO_HEADING_EDIT_PLAYLIST' : 'COM_ES_AUDIO_HEADING_NEW_PLAYLIST', 'h1'); ?>
		</div>

		<div class="es-forms__content">
			<div class="o-form-horizontal">
				<div class="o-form-group">
					<?php echo $this->html('form.label', 'COM_ES_AUDIO_PLAYLIST_FORM_TITLE', 3, false); ?>

					<div class="o-control-input">
						<?php echo $this->html('grid.inputbox', 'title', $list->title, 'title', array('placeholder="' . JText::_('COM_ES_AUDIO_PLAYLIST_FORM_TITLE_PLACEHOLDER') . '"')); ?>

						<div class="help-block">
							<b><?php echo JText::_('COM_EASYSOCIAL_NOTE');?>:</b> <?php echo JText::_( 'COM_ES_AUDIO_PLAYLIST_FORM_TITLE_NOTE' );?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="es-forms__actions">
		<div class="o-form-actions">
			<a href="<?php echo ESR::audios();?>" class="btn btn-es-default pull-left"><?php echo JText::_('COM_ES_CANCEL'); ?></a>

			<button class="btn btn-es-primary pull-right"><?php echo JText::_('COM_EASYSOCIAL_SUBMIT_BUTTON');?></button>
		</div>
	</div>
		
	<input type="hidden" name="id" value="<?php echo $id; ?>" />
	<?php echo $this->html('form.action', 'audios', 'storePlaylist'); ?>
</form>
