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
defined('_JEXEC') or die ('Unauthorized Access');
?>
<?php if ($this->isMobile()) { ?>
	<div class="es-new-convo-wrapper">
		<a class="btn btn-es-primary btn-block btn-mobile-new-convo" href="<?php echo ESR::conversations(array('layout' => 'compose'));?>">
			<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_NEW_CONVERSATION'); ?>
		</a>
	</div>
<?php } ?>

	<div class="es-conversations <?php echo ($this->isMobile() && $isListing) ? ' sidebar-open' : ''; ?>" data-es-conversations data-es-container>

		<div class="es-convo">
			<div class="es-convo__sidebar">
				<div class="es-convo__sidebar-hd">
					<ul class="o-nav o-nav--fit es-convo-sidebar-tab">
						<li class="o-nav__item<?php echo $active == 'inbox' || !$active ? ' active' : '';?>" data-es-tab="inbox">
							<a href="<?php echo ESR::conversations();?>" class="o-nav__link" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_CONVERSATIONS_INBOX'); ?>">
								<?php echo JText::sprintf('COM_EASYSOCIAL_CONVERSATION_TAB_CONVERSATIONS', $totalInbox);?>
							</a>
						</li>
						<li class="o-nav__item<?php echo $active == 'archives' ? ' active' : '';?>" data-es-tab="archives">
							<a href="<?php echo ESR::conversations(array('type' => 'archives'));?>" title="<?php echo JText::_('COM_EASYSOCIAL_PAGE_TITLE_CONVERSATIONS_ARCHIVES'); ?>" class="o-nav__link">
								<?php echo JText::sprintf('COM_EASYSOCIAL_CONVERSATION_TAB_ARCHIVES', $totalArchive);?>
							</a>
						</li>
					</ul>
				</div>

				<?php if (!$this->isMobile() && $access->allowed('conversations.create')) { ?>
				<div class="es-convo__sidebar-btn-new">
					<a class="btn btn-es-primary-o btn-lg btn-block" href="<?php echo ESR::conversations(array('layout' => 'compose'));?>">
						<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_NEW_CONVERSATION'); ?>
					</a>
				</div>
				<?php } ?>

				<div class="es-convo__search-input-wrap">
					<input type="text" class="o-form-control" autocomplete="off"
						   placeholder="<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_FILTER'); ?>" data-es-search />
				</div>
				<div class="es-convo__sidebar-scroll-area">

					<div class="es-convo-list<?php echo !$conversations ? ' is-empty' : '';?>" data-es-list>
						<div class="es-convo-list-items" data-es-list-items>
							<?php echo $this->output('site/conversations/default/lists', array('lists' => $conversations, 'activeConversation' => $activeConversation)); ?>
						</div>

						<div class="<?php echo ($nextlimit < 0) ? ' t-hidden': ''; ?> es-convo__sidebar-pagination" data-es-conversation-pagination-wrapper>
							<a href="javascript:void(0);" class="btn btn-es-default-o btn-block" data-es-conversation-pagination data-type="<?php echo $active; ?>" data-limitstart="<?php echo $nextlimit;?>">
								<i class="fa fa-refresh"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_CONVERSATIONS_LOAD_MORE_CONVERSATIONS'); ?>
							</a>
						</div>

						<div class="o-empty o-empty--height-no" data-es-list-empty>
							<div class="o-empty__content">
								<div class="o-empty__text">
									<?php echo (isset($isArchive) && $isArchive) ? JText::_('COM_EASYSOCIAL_CONVERSATION_ARCHIVE_EMPTY_LIST') : JText::_('COM_EASYSOCIAL_CONVERSATION_EMPTY_LIST');?>
								</div>
							</div>
						</div>
						<div class="o-loader o-loader--top"></div>
					</div>
				</div>
			</div>

			<div class="es-convo__content<?php echo $activeConversation ? ' has-active' : '';?>" data-contents>

				<?php echo $this->render('module', 'es-conversations-before-contents'); ?>

				<div class="es-convo__content-hd" data-es-conversation-header>
					<div class="es-convo__content-hd-title">
						<span data-es-title>
							<?php if ($activeConversation) { ?>
								<?php echo $activeConversation->getTitle(); ?>
							<?php } else { ?>
								&nbsp;
							<?php } ?>
						</span>
						<!-- TODO: isWritable -->
						<?php if ($activeConversation) { ?>
							<a href="javascript:void(0);" class="es-convo__content-hd-edit <?php echo (!$activeConversation || $activeConversation->canEditTitle()) ? '': ' t-hidden'; ?>" data-es-rename>
								<i class="fa fa-pencil-alt"></i>
							</a>
						<?php } ?>
					</div>

					<div class="es-convo__content-action btn-toolbar" data-es-actions>
						<?php if ($this->isMobile()) { ?>
							<a class="btn btn-es-default-o btn-convo-back" href="javascript: void(0);" data-back-button>
								<i class="i-chevron i-chevron--left"></i>
							</a>
						<?php } ?>

						<div class="o-btn-group<?php echo (!$activeConversation) ? ' t-hidden': ''; ?>" data-item-tools>
							<?php echo $this->output('site/conversations/default/actions', array('conversation' => $activeConversation)); ?>
						</div>
					</div>

					<div class="es-convo__content-hd-title-input" data-es-title-container>
						<div class="o-input-group o-input-group--sm">
							<input class="o-form-control" type="text" data-es-title-textbox />
							<span class="o-input-group__btn">
								<button class="btn btn-es-default-o" type="button" data-title-cancel><?php echo JText::_('COM_ES_CANCEL'); ?></button>
								<button class="btn btn-es-primary-o" type="button" data-title-save><?php echo JText::_('COM_EASYSOCIAL_SAVE_BUTTON'); ?></button>
							</span>
						</div>
					</div>
				</div>

				<div class="es-convo__content-scroll-area" data-es-scroller>
					<div class="es-convo-messages">
						<div class="<?php echo !$activeConversation ? ' is-empty' : '';?>" data-es-contents-wrapper>
							<div class="message-list">
								<div class="t-lg-mt--xl" data-es-first></div>

								<div data-es-messages>
									<?php if ($activeConversation) { ?>
										<?php echo $activeConversation->getMessagesHtml(array('pagination' => true, 'limit' => ES::getLimit('messages_limit'), 'checkAccess' => true)); ?>
									<?php } ?>

								</div>

								<div style="margin-bottom: 15px;display: block;" data-es-latest></div>
							</div>
							<div class="o-empty" data-es-content-empty>
								<div class="o-empty__content">
									<div class="o-empty__text">
										<?php if (isset($isArchive) && $isArchive) { ?>
											<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_ARCHIVE_EMPTY'); ?>
										<?php } elseif (!ES::conversation()->canCreate()) {  ?>
											<?php echo JText::_('COM_ES_CONVERSATION_NOT_ALLOWED_EMPTY'); ?>
										<?php } else {  ?>
											<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_EMPTY'); ?>
										<?php } ?>
									</div>

									<?php if (ES::conversation()->canCreate()) { ?>
									<div class="o-empty__action t-lg-mt--xl" data-es-action-btn-empty>
										<a class="btn btn-es-primary" href="<?php echo FRoute::conversations(array('layout' => 'compose'));?>">
											<i class="fa fa-comments"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_NEW_CONVERSATION'); ?>
										</a>
									</div>
									<?php } ?>
								</div>
							</div>
							<div class="o-loader o-loader--top"></div>
						</div>
					</div>
				</div>


				<div class="es-convo-composer-wrapper <?php echo ($activeConversation && $activeConversation->canReply()) ? '' : 't-hidden'; ?>"
					 data-es-composer>

					 <div class="t-hidden es-convo-typing" data-typing>
					</div>

					<form method="post"
						  name="es-convo-reply"
						  class="es-convo-reply <?php echo !$activeConversation ? 't-hidden' : '';?>"
						  data-es-reply-form
						  enctype="multipart/form-data"
						  data-id="<?php echo $activeConversation ? $activeConversation->id : '';?>"
					>
						<div class="es-convo-composer">
							<div class="es-convo-composer__bd">
								<div class="es-convo-composer__editor">
									<div data-composer-editor-header>
										<div class="es-convo-composer__textarea mentions" data-composer-editor-area>
											<div data-mentions-overlay data-default></div>
											<textarea class="o-form-control es-convo-composer__textarea"
												name="message"
												autocomplete="off"
												data-mentions-textarea
												data-default=""
												data-initial="0"
												data-composer-editor
												data-es-conversation-reply-textarea
												placeholder="<?php echo JText::_($this->config->get('conversations.entersubmit') ? 'COM_EASYSOCIAL_CONVERSATIONS_WRITE_YOUR_MESSAGE_HERE_ENTER' : 'COM_EASYSOCIAL_CONVERSATIONS_WRITE_YOUR_MESSAGE_HERE');?>"></textarea>
											<?php if ($this->config->get('honeypot.conversations')) { ?>
												<?php echo $this->html('form.honeypot'); ?>
											<?php } ?>
										</div>
									</div>
								</div>

								<?php echo $this->output('site/conversations/message/attachment.form'); ?>
								<?php echo $this->output('site/conversations/message/location.form'); ?>
							</div>
							<div class="es-convo-composer__ft">

								<div class="es-convo-composer__action">
									<div class="es-convo-composer__action-tab">
										<?php if (ES::giphy()->isEnabledForConversations()) { ?>
											<a href="javascript:void(0);" class="es-convo-composer__action-tab-link"
												data-giphy-button
												data-popbox="module://easysocial/giphy/popbox"
												data-popbox-toggle="click"
												data-popbox-position="bottom-right"
											>
												GIF
											</a>
										<?php } ?>
										<?php if ($this->config->get('conversations.attachments.enabled')) { ?>
											<a href="javascript:void(0);" class="es-convo-composer__action-tab-link" data-es-attachment-toggle>
												<i class="fa fa-paperclip"></i>
											</a>
										<?php } ?>
										<?php if ($this->config->get( 'conversations.location')) { ?>
											<a href="javascript:void(0);" class="es-convo-composer__action-tab-link" data-es-location-toggle>
												<i class="fa fa-map-marker-alt"></i>
											</a>
										<?php } ?>
										<div class="es-convo-composer__action-tab-link " data-es-emoticons-toggle>
											<?php echo ES::smileys()->html();?>
										</div>
									</div>
									<div class="es-convo-composer__action-reply">
										<button class="btn btn-es-primary reply-button" data-es-reply-button>
											<?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_REPLY'); ?>
										</button>
									</div>
								</div>

							</div>
						</div>

						<input type="hidden" name="option" value="com_easysocial" />
						<input type="hidden" name="controller" value="conversations" />
						<input type="hidden" name="task" value="create" />
						<?php echo JHTML::_('form.token'); ?>
					</form>
				</div>

				<?php echo $this->html('suggest.hashtags'); ?>
				<?php echo $this->html('suggest.friends'); ?>
				<?php echo $this->html('suggest.emoticons'); ?>

				<div class="t-hidden">
					<div data-id="COM_EASYSOCIAL_CONVERSATION_REPLY_FORM_EMPTY"><?php echo JText::_('COM_EASYSOCIAL_CONVERSATION_REPLY_FORM_EMPTY'); ?></div>
				</div>

				<?php echo $this->render('module', 'es-conversations-after-contents'); ?>
			</div>
		</div>
	</div>
