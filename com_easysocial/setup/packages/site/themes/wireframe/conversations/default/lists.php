<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($lists) { ?>
	<?php foreach ($lists as $list) { ?>
		<div class="es-convo__sidebar-item
			<?php echo $list->isNew() ? ' is-unread' : '';?>
			<?php echo $list->isArchived() ? ' is-archived' : '';?>
			<?php echo $list->id == ($activeConversation && $activeConversation->id) ? ' is-active' : '';?>"
			data-es-item
			data-item
			data-id="<?php echo $list->id;?>"
			data-lastupdate="<?php echo ES::date()->toSql(); ?>"
			data-title="<?php echo $this->html('string.escape', $list->getTitle(false, true));?>"
		>
			<div class="o-flag" data-es-conversation>
				<a href="<?php echo ESR::conversations(array('id' => $list->id)); ?>" title="<?php echo $list->getTitle(); ?>" data-link></a>
				<div class="o-flag__image o-flag--top">
					<a href="<?php echo ESR::conversations(array('id' => $list->id)); ?>" data-link></a>
					<?php echo $list->getAvatar(); ?>
				</div>
				<div class="o-flag__body">
					<div class="es-convo__sidebar-item-title xes-user-name t-lg-mb--sm">
						<span data-item-title>
							<?php echo $list->getTitle(); ?>
						</span>
					</div>

					<div class="t-lg-mb--sm t-hidden" data-item-title-textbox>
						<div class="o-input-group o-input-group--sm">
							<input class="o-form-control" placeholder="<?php echo JText::_('COM_ES_SHOW_TITLE_TYPE_CONVERSATION'); ?>" type="text" data-es-title-textbox-list value="">
							<span class="o-input-group__btn">
								<button class="btn btn-es-primary" type="button" data-title-save-list><?php echo JText::_('COM_EASYSOCIAL_SAVE_BUTTON'); ?></button>
							</span>
						</div>
					</div>

					<div class="es-convo-meta">
						<?php if ($list->isReadable($this->my->id)) { ?>
							<?php echo $list->getLastRepliedDate(true); ?>
						<?php } else { ?>
							<?php echo $list->getLastMessage()->getRepliedDate(true); ?>
						<?php } ?>
					</div>
				</div>
			</div>

			<div class="es-convo__sidebar-action" data-menu-dropdown>
				<div class="pull-right">
					<a href="javascript:void(0);" class="btn btn-default btn-xs dropdown-toggle_" data-es-toggle="dropdown">
						<i class="fa fa-angle-down"></i>
					</a>
					<ul class="dropdown-menu">

						<?php if ($list->isReadable($this->my->id)) { ?>
							<li data-item-menu="unread">
								<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_MARK_UNREAD'); ?></a>
							</li>
							<?php if ($list->isArchived()) { ?>
								<li data-item-menu="unarchive">
									<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_UNARCHIVE'); ?></a>
								</li>
							<?php } else {  ?>
								<li data-item-menu="archive">
									<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_ARCHIVE'); ?></a>
								</li>
							<?php } ?>

							<li>
							<a href="javascript:void(0);" data-es-rename-list>
							<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_EDIT_TITLE');?>
							</a>
							</li>
							<li class="divider"></li>
						<?php } ?>

						<li data-item-menu="delete">
							<a href="javascript:void(0);"><?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_DELETE'); ?></a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	<?php } ?>
<?php } ?>
