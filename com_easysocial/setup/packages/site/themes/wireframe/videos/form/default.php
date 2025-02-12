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
<div class="wrapper-for-full-height">
	<?php echo $video->getMiniHeader();?>

	<div class="es-container">
		<div class="es-content">
			<form action="<?php echo JRoute::_('index.php');?>" method="post" enctype="multipart/form-data" class="es-forms" data-videos-form>

				<div class="es-forms__group">
					<div class="es-forms__title">
						<?php echo $this->html('form.title', 'COM_EASYSOCIAL_VIDEOS_ADD_NEW_VIDEO_TITLE'); ?>
					</div>

					<div class="es-forms__content">
						<div class="o-form-horizontal">
							<div class="o-form-group">
								<?php echo $this->html('form.label', 'COM_EASYSOCIAL_VIDEOS_VIDEO_CATEGORY', 3, false); ?>

								<div class="o-control-input">
									<select id="video-category" name="category_id" class="o-form-control">
										<option value=""><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_VIDEO_SELECT_CATEGORY_FOR_VIDEO');?></option>

										<?php foreach ($categories as $category) { ?>
										<option value="<?php echo $category->id;?>"<?php echo $selectedCategory == $category->id ? ' selected="selected"' : '';?>><?php echo Jtext::_($category->title);?></option>
										<?php } ?>
									</select>

									<div class="help-block"><?php echo JText::_('COM_EASYSOCIAL_VIDEOS_VIDEO_CATEGORY_TIPS');?></div>
								</div>
							</div>

							<?php if ($video->canUpload() && $video->canEmbed()) { ?>
							<div class="o-form-group">
								<?php echo $this->html('form.label', 'COM_EASYSOCIAL_VIDEOS_VIDEO_TYPE', 3, false); ?>

								<div class="o-control-input">
									<?php if ($video->canEmbed()) { ?>
									<div class="o-radio o-radio--inline">
										<input id="video-link" type="radio" name="source" value="link" data-video-source <?php echo $video->isLink() ? ' checked="checked"' : '';?>/>
										<label for="video-link">
											<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_VIDEO_TYPE_EXTERNAL');?>
										</label>
									</div>
									<?php } ?>

									<?php if ($video->canUpload()) { ?>
									<div class="o-radio o-radio--inline">
										<input id="video-uploads" type="radio" name="source" value="upload" data-video-source <?php echo $video->isUpload() ? ' checked="checked"' : '';?>/>
										<label for="video-uploads">
										<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_VIDEO_TYPE_UPLOAD');?>
										</label>
									</div>

									<?php } ?>
								</div>
							</div>
							<?php } else { ?>
								<?php if ($video->canEmbed()) { ?>
									<input type="hidden" name="source" value="link" />
								<?php } ?>

								<?php if ($video->canUpload()) { ?>
									<input type="hidden" name="source" value="upload" />
								<?php } ?>

							<?php } ?>

							<?php if ($video->canUpload()) { ?>
							<div class="o-form-group <?php echo $video->isLink() && $video->canEmbed() ? ' t-hidden' : '';?>" data-form-upload data-form-source>
								<?php echo $this->html('form.label', 'COM_EASYSOCIAL_VIDEOS_VIDEO_FILE', 3, false); ?>

								<div class="o-control-input">
									<div class="o-input-group">
										<input class="o-form-control" type="text" readonly data-video-filename value="<?php echo !$video->isNew() ? $video->file_title : ''; ?>"/>
										<span class="o-input-group__btn">
											<span class="btn btn-es-default btn-file" data-browse-button>
												<?php echo JText::_('FIELDS_USER_COVER_BROWSE_FILE'); ?>&hellip;
												<input type="file" name="video" data-video-file/>
											</span>
										</span>
									</div>
									<p class="help-block" data-link-notice>
										<?php echo JText::sprintf('COM_EASYSOCIAL_VIDEOS_VIDEO_FILE_TIPS', $uploadLimit);?>.
										<?php if ($this->access->get('videos.truncate')) { ?>
											<?php echo JText::sprintf('COM_ES_VIDEO_AUTO_TRUNCATED_MESSAGE', $this->access->get('videos.truncatelength')); ?>
										<?php } ?>
									</p>
								</div>

							</div>
							<?php } ?>

							<?php if ($video->canEmbed()) { ?>
							<div class="o-form-group <?php echo $video->isUpload() ? 't-hidden' : '';?>" data-form-link data-form-source>
								<?php echo $this->html('form.label', 'COM_EASYSOCIAL_VIDEOS_VIDEO_LINK', 3, false); ?>

								<div class="o-control-input">
									<?php echo $this->html('grid.inputbox', 'link', $video->isLink() ? $video->path : '', 'video-link-source', array('placeholder="' . JText::_('COM_EASYSOCIAL_VIDEOS_VIDEO_LINK_PLACEHOLDER') . '"', 'data-video-link'));?>
									<div class="o-loader o-loader--sm" data-loader></div>
								</div>
							</div>
							<?php } ?>

							<div class="o-form-group">
								<?php if (!$isCluster && $this->config->get('privacy.enabled')) { ?>
									<div class="es-privacy-cf">
										<?php echo $privacy->form($video->id, SOCIAL_TYPE_VIDEOS, $video->getAuthor()->id, 'videos.view', true, null, array(), array('linkStyle' => 'button', 'iconOnly' => false)); ?>
									</div>
								<?php } ?>
								<?php echo $this->html('form.label', 'COM_EASYSOCIAL_VIDEOS_VIDEO_TITLE', 3, false); ?>

								<div class="o-control-input">
									<?php echo $this->html('grid.inputbox', 'title', $video->title, 'video-title', array('data-video-title placeholder="' . JText::_('COM_EASYSOCIAL_VIDEOS_VIDEO_TITLE_PLACEHOLDER') . '"')); ?>

									<div class="es-fields-error-note" data-title-error><?php echo JText::_('COM_ES_VIDEO_ENTER_A_TITLE'); ?></div>
								</div>

							</div>

							<div class="o-form-group">
								<?php echo $this->html('form.label', 'COM_EASYSOCIAL_VIDEOS_VIDEO_DESCRIPTION', 3, false); ?>

								<div class="o-control-input" data-video-desc-field data-video-editor-type="<?php echo $defaultEditor; ?>">
									<?php if ($defaultEditor != 'noeditor') { ?>
										<?php echo $editor->display('description', $video->description, '100%', '350', '10', '10', false, null, 'com_easysocial'); ?>
									<?php } else { ?>
										<?php echo $this->html('grid.textarea', 'description', $video->description, 'video-desc', array('placeholder="' . JText::_('COM_EASYSOCIAL_VIDEOS_FORM_DESCRIPTION_PLACEHOLDER') . '" data-video-desc')); ?>
									<?php } ?>
								</div>
							</div>

							<?php if ($this->config->get('video.dates')) { ?>
							<div class="o-form-group">
								<?php echo $this->html('form.label', 'COM_ES_VIDEOS_FIELD_DATE', 3, false); ?>

								<div class="o-control-input">
									<div class="o-grid o-grid--1of3">
										<div class="o-grid__cell">
											<?php echo $this->html('form.datepicker', 'assigned_date', $video->assigned_date, [
												'placeholder' => JText::_('COM_ES_VIDEOS_FIELD_DATE_PLACEHOLDER')
											]); ?>
										</div>
									</div>
								</div>
							</div>
							<?php } ?>

							<?php if ($this->config->get('video.layout.item.usertags')) { ?>
							<div class="o-form-group">
								<?php echo $this->html('form.label', 'COM_EASYSOCIAL_VIDEOS_FORM_PEOPLE_IN_THIS_VIDEO', 3, false); ?>

								<div class="o-control-input">
									<div class="textboxlist disabled" data-mentions>

										<?php if ($userTags) { ?>
											<?php foreach ($userTags as $userTag) { ?>
												<div class="textboxlist-item" data-id="<?php echo $userTag->getEntity()->id; ?>" data-title="<?php echo $userTag->getEntity()->getName(); ?>" data-textboxlist-item>
													<span class="textboxlist-itemContent" data-textboxlist-itemContent>
														<img width="16" height="16" src="<?php echo $userTag->getEntity()->getAvatar(SOCIAL_AVATAR_SMALL);?>" />
														<?php echo $userTag->getEntity()->getName(); ?>
														<input type="hidden" name="items" value="<?php echo $userTag->getEntity()->id; ?>" />
													</span>
													<div class="textboxlist-itemRemoveButton" data-textboxlist-itemRemoveButton>
														<i class="fa fa-times"></i>
													</div>
												</div>
											<?php } ?>
										<?php } ?>

										<input type="text" autocomplete="off" disabled class="textboxlist-textField" data-textboxlist-textField placeholder="<?php echo JText::_('COM_EASYSOCIAL_CONVERSATIONS_START_TYPING');?>"
										/>
									</div>
								</div>
							</div>
							<?php } ?>

							<?php if ($this->config->get('video.layout.item.tags')) { ?>
							<div class="o-form-group">
								<?php echo $this->html('form.label', 'COM_EASYSOCIAL_VIDEOS_FORM_VIDEO_TAGS', 3, false); ?>

								<div class="o-control-input">
									<div class="es-form-hashtags-wrap">
										<div class="" data-hashtags-header>
											<div class="mentions-textfield es-story-textbox" data-hashtags>
												<div class="mentions">
													<div data-mentions-overlay data-default></div>
													<textarea class="o-form-control" name="hashtags" data-default="" data-initial="0" autocomplete="off"  placeholder="<?php echo JText::_('COM_EASYSOCIAL_VIDEOS_HASHTAGS_PLACEHOLDER', true);?>"
														data-story-textField data-mentions-textarea><?php echo $hashtags; ?></textarea>
												</div>
											</div>
										</div>
									</div>

								</div>
							</div>
							<?php } ?>

							<div class="o-form-group">
								<?php echo $this->html('form.label', 'COM_EASYSOCIAL_VIDEOS_VIDEO_LOCATION', 3, false); ?>

								<div class="o-control-input">
									<?php echo $this->html('form.location', $video->getLocation()->table, '', 'video'); ?>
								</div>
							</div>

						</div>
					</div>
				</div>

				<div class="es-forms__actions">
					<div class="o-form-actions">
						<a href="<?php echo $returnLink;?>" class="btn btn-es-default-o t-pull-left" data-cancel-button><?php echo JText::_('COM_ES_CANCEL');?></a>
						<button type="button" class="btn btn-es-primary t-pull-right" data-save-button>
							<?php echo JText::_('COM_EASYSOCIAL_SAVE_BUTTON');?>
						</button>
					</div>
				</div>

				<?php echo $this->html('suggest.hashtags'); ?>

				<?php if ($video->uid && $video->type) { ?>
				<input type="hidden" name="uid" value="<?php echo $video->uid;?>" />
				<input type="hidden" name="type" value="<?php echo $video->type;?>" />
				<?php } ?>

				<input type="hidden" name="id" value="<?php echo $video->id;?>" data-video-id/>
				<input type="hidden" name="fileUploaded" value="" data-file-uploaded/>

				<?php echo $this->html('form.action', 'videos', 'save'); ?>
			</form>
		</div>
	</div>
</div>
