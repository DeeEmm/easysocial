<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php echo $this->html('cover.user', $this->my, 'friends'); ?>

<div class="es-container">
	<div class="es-content">
		<form method="post" action="<?php echo JRoute::_('index.php');?>" class="es-forms">
			<div class="es-forms__group">
				<div class="es-forms__title">
					<?php echo $this->html('form.title', $list->id ? 'COM_EASYSOCIAL_HEADING_EDIT_FRIEND_LIST' : 'COM_EASYSOCIAL_HEADING_NEW_FRIEND_LIST', 'h1'); ?>
				</div>

				<div class="es-forms__content">
					<div class="o-form-horizontal">
						<div class="o-form-group">
							<?php echo $this->html('form.label', 'COM_EASYSOCIAL_FRIENDS_LIST_FORM_TITLE', 3, false); ?>

							<div class="o-control-input">
								<?php echo $this->html('grid.inputbox', 'title', $this->html('string.escape', $list->title), 'title', array('placeholder="' . JText::_('COM_EASYSOCIAL_FRIENDS_LIST_FORM_TITLE_PLACEHOLDER') . '"')); ?>

								<div class="help-block">
									<strong><?php echo JText::_( 'COM_EASYSOCIAL_NOTE' );?>:</strong> <?php echo JText::_( 'COM_EASYSOCIAL_FRIENDS_LIST_FORM_TITLE_NOTE' );?>
								</div>
							</div>
						</div>

						<div class="o-form-group">
							<?php echo $this->html('form.label', 'COM_EASYSOCIAL_FRIENDS_LIST_FORM_USERS', 3, false); ?>

							<div class="o-control-input">
								<div class="textboxlist controls disabled" data-friends-suggest>
									<?php if ($members) { ?>
										<?php foreach ($members as $user) { ?>
											<div class="textboxlist-item" data-id="<?php echo $user->id;?>" data-title="<?php echo $this->html('string.escape', $user->getName() );?>" data-textboxlist-item>
												<span class="textboxlist-itemContent" data-textboxlist-itemContent><?php echo $this->html( 'string.escape' , $user->getName() );?>
													<input type="hidden" name="items[]" value="<?php echo $user->id;?>" />
												</span>
												<a class="textboxlist-itemRemoveButton" href="javascript: void(0);" data-textboxlist-itemRemoveButton><i class="fa fa-times"></i></a>
											</div>
										<?php } ?>
									<?php } ?>
									<input type="text" autocomplete="off" disabled class="participants textboxlist-textField" data-textboxlist-textField placeholder="<?php echo JText::_('COM_EASYSOCIAL_FRIENDS_LIST_FORM_USERS_PLACEHOLDER');?>" />
								</div>

								<div class="help-block">
									<b><?php echo JText::_('COM_EASYSOCIAL_NOTE');?>:</b> <?php echo JText::_('COM_EASYSOCIAL_FRIENDS_LIST_FORM_USERS_NOTE');?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="es-forms__actions">
				<div class="o-form-actions">
					<a href="<?php echo ESR::friends();?>" class="btn btn-es-default-o pull-left"><?php echo JText::_('COM_ES_CANCEL'); ?></a>

					<button class="btn btn-es-primary pull-right">
						<?php echo JText::_('COM_EASYSOCIAL_SUBMIT_BUTTON');?>
					</button>
				</div>
			</div>

			<?php echo $this->html('form.action', 'friends', 'storeList'); ?>
			<input type="hidden" name="id" value="<?php echo $id; ?>" />
		</form>
	</div>
</div>
