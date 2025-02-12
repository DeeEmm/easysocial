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
<div class="panels panel-default es-polls" data-polls-form data-id="<?php echo $poll->id;?>" data-uid="<?php echo $uid;?>" data-element="<?php echo $element;?>" data-cluster="<?php echo $cluster_id;?>">
	<form class="o-form-horizontal" name="pollsForm" id="pollsForm">

		<div class="o-alert o-alert--warning t-hidden" data-story-polls-alert>
			<?php echo JText::_('COM_EASYSOCIAL_POLLS_EMPTY_MESSAGE'); ?>
		</div>

		<div class="o-form-group">
			<input type="text" placeholder="<?php echo JText::_('COM_EASYSOCIAL_POLLS_SET_A_TITLE');?>" class="o-form-control" name="title" value="<?php echo ES::string()->escape($poll->title); ?>" autocomplete="off" data-polls-title />
		</div>

		<div class="panel-body pl-0 pr-0">
			<div class="data-field-multitextbox" data-field-multitextbox data-max="0">
				<ul class="g-list-unstyled" data-polls-options>

					<?php if ($items) { ?>
						<?php foreach ($items as $item) { ?>
							<?php echo $this->output('site/polls/form/default.option', array('item' => $item)); ?>
						<?php } ?>
					<?php } else { ?>
						<?php echo $this->output('site/polls/form/default.option', array('item' => null)); ?>
						<?php echo $this->output('site/polls/form/default.option', array('item' => null)); ?>
					<?php } ?>

					<li class="data-field-multitextbox-item" style="display:none;" data-polls-option-template>
						<div class="o-input-group">
							<input type="text" class="o-form-control" name="copied" value="" placeholder="<?php echo JText::_('COM_EASYSOCIAL_POLLS_ENTER_POLL_ITEM');?>">
							<span class="o-input-group__btn" data-polls-delete-btn>
								<button class="btn btn-es-default-o" type="button" data-polls-item-delete>×</button>
							</span>
						</div>
					</li>
				</ul>

				<div class="t-lg-mt--xl t-lg-mb--xl">
					<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-polls-add>
						<?php echo JText::_('COM_EASYSOCIAL_POLLS_ADD_ITEM');?>
					</a>
				</div>
			</div>

			<div class="o-checkbox t-lg-mb--md">
				<input type="checkbox" id="allow-multiple-selection-<?php echo $poll->id;?>" name="multiple" data-polls-multiple  <?php echo ($poll->multiple) ? ' checked="checked"' : ''; ?>>
				<label for="allow-multiple-selection-<?php echo $poll->id;?>">
					<?php echo JText::_('COM_EASYSOCIAL_POLLS_ALLOW_MULTIPLE_ITEM'); ?>
				</label>
			</div>

			<div id="datetimepicker4" class="o-input-group" data-polls-expiration data-value="<?php echo ($poll->expiry_date != '' && $poll->expiry_date != '0000-00-00 00:00:00') ? ES::date($poll->expiry_date)->toFormat('Y-m-d H:i:s') : '';?>">
				<input type="text" class="o-form-control" placeholder="<?php echo JText::_('COM_EASYSOCIAL_POLLS_EXPIRED_DATE'); ?>" data-picker />
				<input type="hidden" data-datetime />
				<span class="o-input-group__addon" data-picker-toggle>
					<i class="far fa-calendar-alt"></i>
				</span>
			</div>
		</div>

		<input type="hidden" name="element" value="<?php echo $element; ?>" />
		<input type="hidden" name="uid" value="<?php echo $uid; ?>" />
		<input type="hidden" name="cluster_id" value="<?php echo $cluster_id;?>" />
		<input type="hidden" name="itemsremoved" data-remove value="" />
	</form>
</div>

<?php if ($poll->id) { ?>
<div class="es-stream-editor-actions">
	<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm pull-left" data-stream-polls-edit-cancel>
		<?php echo JText::_('COM_ES_CANCEL');?>
	</a>

	<a href="javascript:void(0);" class="btn btn-es-primary-o btn-sm pull-right" data-stream-polls-edit-update>
		<?php echo JText::_('COM_EASYSOCIAL_STREAM_POLLS_UPDATE_BUTTON');?>
	</a>
</div>
<?php } ?>
