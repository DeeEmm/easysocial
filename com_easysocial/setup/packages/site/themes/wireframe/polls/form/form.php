<?php
/**
* @package		EasySocial
* @copyright	Copyright (C)18 Stack Ideas Sdn Bhd. All rights reserved.
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
	<div class="o-form-horizontal">
		<div class="o-form-group">
			<?php if (!$cluster_id && $this->config->get('privacy.enabled')) { ?>
				<div class="es-privacy-cf">
					<?php echo $privacy->form($poll->id, SOCIAL_TYPE_POLLS, $this->my->id, 'polls.view', true, null, array(), array('linkStyle' => 'button', 'iconOnly' => false)); ?>
				</div>
			<?php } ?>

			<?php echo $this->html('form.label', 'COM_ES_TITLE', 3, false); ?>

			<div class="o-control-input">
				<input type="text" placeholder="<?php echo JText::_('COM_EASYSOCIAL_POLLS_SET_A_TITLE');?>" class="o-form-control" name="title" value="<?php echo ES::string()->escape($poll->title); ?>" autocomplete="off" data-polls-title />

				<div class="help-block"><?php echo JText::_('COM_EASYSOCIAL_POLLS_TITLE_TIPS');?></div>
			</div>
		</div>

		<div class="o-form-group">
			<?php echo $this->html('form.label', 'COM_ES_OPTIONS', 3, false); ?>

			<div class="o-control-input">
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

					<div class="t-lg-mt--md">
						<a href="javascript:void(0);" class="btn btn-es-default-o btn-sm" data-polls-add>
							<?php echo JText::_('COM_EASYSOCIAL_POLLS_ADD_ITEM');?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<div class="o-form-group">
			<label class="o-control-label"></label>

			<div class="o-control-input">
				<div class="o-checkbox t-lg-mb--md">
					<input type="checkbox" id="allow-multiple-selection" name="multiple" data-polls-multiple  <?php echo ($poll->multiple) ? ' checked="checked"' : ''; ?>>
					<label for="allow-multiple-selection">
						<?php echo JText::_('COM_EASYSOCIAL_POLLS_ALLOW_MULTIPLE_ITEM'); ?>
					</label>
				</div>
			</div>
		</div>

		<div class="o-form-group">
			<?php echo $this->html('form.label', 'COM_EASYSOCIAL_POLLS_EXPIRATION_DATE', 3, false); ?>

			<div class="o-control-input">
				<div class="o-grid o-grid--1of3">
					<div class="o-grid__cell">
						<div id="datetimepicker4" class="o-input-group" data-polls-expiration data-value="<?php echo ($poll->expiry_date != '' && $poll->expiry_date != '0000-00-00 00:00:00') ? ES::date($poll->expiry_date)->toFormat('Y-m-d H:i:s') : '';?>">
							<input type="text" class="o-form-control" placeholder="<?php echo JText::_('COM_EASYSOCIAL_POLLS_EXPIRED_DATE'); ?>" data-picker />
							<input type="hidden" name="expiry" data-datetime />
							<span class="o-input-group__addon" data-picker-toggle>
								<i class="far fa-calendar-alt"></i>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
