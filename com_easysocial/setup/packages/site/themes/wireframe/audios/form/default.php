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
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="wrapper-for-full-height">
<?php echo $audio->getMiniHeader();?>

	<div class="es-container">
		<div class="es-content">
			<form action="<?php echo JRoute::_('index.php');?>" method="post" enctype="multipart/form-data" class="es-forms" data-audios-form>

				<div class="es-forms__group">
					<div class="es-forms__title">
						<?php echo $this->html('form.title', 'COM_ES_AUDIO_ADD_NEW_AUDIO_TITLE'); ?>
					</div>

					<div class="es-forms__content">
						<div class="o-form-horizontal">

							<div class="o-form-group">

								<?php echo $this->html('form.label', 'COM_ES_AUDIO_FORM_GENRE', 3, false); ?>

								<div class="o-control-input">
									<select id="audio-genre" name="genre_id" class="o-form-control">
										<option value=""><?php echo JText::_('COM_ES_AUDIO_FORM_SELECT_GENRE_FOR_AUDIO');?></option>

										<?php foreach ($genres as $genre) { ?>
										<option value="<?php echo $genre->id;?>"<?php echo $selectedGenre == $genre->id ? ' selected="selected"' : '';?>><?php echo Jtext::_($genre->title);?></option>
										<?php } ?>
									</select>

									<div class="help-block"><?php echo JText::_('COM_ES_AUDIO_FORM_GENRE_TIPS');?></div>
								</div>
							</div>

							<?php if ($audio->canUpload() && $audio->canEmbed()) { ?>
								<div class="o-form-group">
									<?php echo $this->html('form.label', 'COM_ES_AUDIO_FORM_TYPE', 3, false); ?>

									<div class="o-control-input">
										<label for="audio-link" class="radio-inline">
											<input id="audio-link" type="radio" name="source" value="link" data-audio-source <?php echo $audio->isLink() ? ' checked="checked"' : '';?>/>
											<span><?php echo JText::_('COM_ES_AUDIO_FORM_TYPE_EXTERNAL');?></span>
										</label>

										<label for="audio-uploads" class="radio-inline">
											<input id="audio-uploads" type="radio" name="source" value="upload" data-audio-source <?php echo $audio->isUpload() ? ' checked="checked"' : '';?>/>
											<span><?php echo JText::_('COM_ES_AUDIO_FORM_TYPE_UPLOAD');?></span>
										</label>
									</div>
								</div>
							<?php } else { ?>
								<?php if ($audio->canEmbed()) { ?>
									<input type="hidden" name="source" value="link" />
								<?php } ?>

								<?php if ($audio->canUpload()) { ?>
									<input type="hidden" name="source" value="upload" />
								<?php } ?>
							<?php } ?>

							<?php if ($audio->canEmbed()) { ?>
								<div class="o-form-group <?php echo $audio->isUpload() ? 't-hidden' : '';?>" data-form-link data-form-source>
									<?php echo $this->html('form.label', 'COM_ES_AUDIO_FORM_LINK_TO_AUDIO', 3, false); ?>

									<div class="o-control-input">
										<div class="o-input-group">
											<?php echo $this->html('grid.inputbox', 'link', $audio->isLink() ? $audio->path : '', 'audio-link-source', array('placeholder="' . JText::_('COM_ES_AUDIO_FORM_LINK_PLACEHOLDER') . '"', 'data-audio-link'));?>
												<div class="o-loader o-loader--sm" data-loader></div>
										</div>

										<div class="help-block" data-link-notice><?php echo JText::sprintf('COM_ES_AUDIO_FORM_SUPPORTED_PROVIDERS', $supportedProviders);?></div>
									</div>
								</div>
							<?php } ?>

							<?php if ($audio->canUpload()) { ?>
								<div class="o-form-group <?php echo $audio->isLink() && $audio->canEmbed() ? ' t-hidden' : '';?>" data-form-upload data-form-source>
									<?php echo $this->html('form.label', 'COM_ES_AUDIO_FORM_FILE', 3, false); ?>

									<div class="o-control-input">
										<div class="o-input-group">
											<input class="o-form-control" type="text" readonly data-audio-filename value="<?php echo !$audio->isNew() ? $audio->file_title : ''; ?>"/>
											<span class="o-input-group__btn">
												<span class="btn btn-es-default btn-file" data-browse-button>
													<?php echo JText::_('FIELDS_USER_COVER_BROWSE_FILE'); ?>&hellip; <input type="file" id="audio" name="audio" accept="audio/*" data-audio-file />
												</span>
											</span>
										</div>
										<p class="help-block" data-link-notice>
											<?php echo JText::sprintf('COM_ES_AUDIO_FORM_FILE_TIPS', $uploadLimit);?><br>
										</p>
									</div>
								</div>
							<?php } ?>

							<div class="o-form-group">

								<?php if (!$isCluster && $this->config->get('privacy.enabled')) { ?>
									<div class="es-privacy-cf">
										<?php echo $privacy->form($audio->id, SOCIAL_TYPE_AUDIOS, $audio->getAuthor()->id, 'audios.view', true, null, array(), array('linkStyle' => 'button', 'iconOnly' => false)); ?>
									</div>
								<?php } ?>

								<?php echo $this->html('form.label', 'COM_ES_AUDIO_FORM_TITLE', 3, false); ?>

								<div class="o-control-input">
									<?php echo $this->html('grid.inputbox', 'title', $audio->title, 'audio-title', array('placeholder="' . JText::_('COM_ES_AUDIO_FORM_TITLE_PLACEHOLDER') . '" data-audio-title')); ?>

									<div class="es-fields-error-note" data-title-error><?php echo JText::_('COM_ES_AUDIO_ENTER_A_TITLE'); ?></div>
								</div>
							</div>

							<div class="o-form-group">
								<?php echo $this->html('form.label', 'COM_ES_AUDIO_FORM_DESCRIPTION', 3, false); ?>

								<div class="o-control-input">
									<?php echo $this->html('grid.textarea', 'description', $this->html('string.escape', $audio->description), 'audio-desc', array('placeholder="' . JText::_('COM_ES_AUDIO_FORM_DESCRIPTION_PLACEHOLDER') . '" data-audio-desc')); ?>
								</div>
							</div>

							<?php if ($this->config->get('audio.dates')) { ?>
							<div class="o-form-group">
								<?php echo $this->html('form.label', 'COM_ES_AUDIOS_FIELD_DATE', 3, false); ?>

								<div class="o-control-input">
									<div class="o-grid o-grid--1of3">
										<div class="o-grid__cell">
											<?php echo $this->html('form.datepicker', 'assigned_date', $audio->assigned_date, [
												'placeholder' => JText::_('COM_ES_AUDIOS_FIELD_DATE_PLACEHOLDER')
											]); ?>
										</div>
									</div>
								</div>
							</div>
							<?php } ?>

							<div class="o-form-group">
								<?php echo $this->html('form.label', 'COM_ES_AUDIO_FORM_ARTIST', 3, false); ?>

								<div class="o-control-input">
									<?php echo $this->html('grid.inputbox', 'artist', $audio->artist, 'audio-artist', array('placeholder="' . JText::_('COM_ES_AUDIO_FORM_ARTIST_PLACEHOLDER') . '" data-audio-artist')); ?>
								</div>
							</div>

							<div class="o-form-group">
								<?php echo $this->html('form.label', 'COM_ES_AUDIO_FORM_ALBUM', 3, false); ?>

								<div class="o-control-input">
									<?php echo $this->html('grid.inputbox', 'album', $audio->album, 'audio-album', array('placeholder="' . JText::_('COM_ES_AUDIO_FORM_ALBUM_PLACEHOLDER') . '" data-audio-album')); ?>
								</div>
							</div>

							<div class="o-form-group" data-form-albumart>
								<?php echo $this->html('form.label', 'COM_ES_AUDIO_FORM_ALBUM_ART', 3, false); ?>

								<div class="o-control-input">
									<div class="albumart-frame" data-albumart-frame style="background-image: url(<?php echo $audio->getAlbumArt(); ?>)" >
										<?php echo $this->html('html.loading'); ?>
									</div>
									<div class="albumart-remove">
										<a href="javascript:void(0);" data-albumart-remove-button <?php if (!$audio->hasAlbumArt()) { ?>style="display: none;"<?php } ?>>×</a>
									</div>

									<div class="o-input-group">
										<input class="o-form-control" type="text" readonly data-audio-albumart-filename/>
										<span class="o-input-group__btn">
											<span class="btn btn-es-default btn-file" data-albumart-input>
												<?php echo JText::_('FIELDS_USER_COVER_BROWSE_FILE'); ?>&hellip; <input type="file" name="album_art" accept="image/*" data-audio-albumart />
											</span>
										</span>
									</div>

									<?php if ($this->config->get('audio.allowencode')) { ?>
									<p class="help-block">
										<label for="album_art-source" class="">
											<input id="album_art-source" type="checkbox" name="albumart_sourceCheckbox" <?php echo $audio->albumart_source == 'audio' ? 'checked' : ''; ?> data-albumart-source />
											<span><?php echo JText::_('COM_ES_AUDIO_FORM_ALBUM_ART_SOURCE_NOTICE');?></span>
										</label>
									</p>
									<?php } ?>

								</div>
							</div>

							<?php if ($this->config->get('audio.layout.item.usertags')) { ?>
							<div class="o-form-group">
								<?php echo $this->html('form.label', 'COM_ES_AUDIO_FORM_PEOPLE_IN_THIS_AUDIO', 3, false); ?>

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

							<?php if ($this->config->get('audio.layout.item.tags')) { ?>
							<div class="o-form-group">
								<?php echo $this->html('form.label', 'COM_ES_AUDIO_FORM_AUDIO_TAGS', 3, false); ?>

								<div class="o-control-input">
									<div class="es-form-hashtags-wrap">
										<div class="" data-hashtags-header>
											<div class="mentions-textfield es-story-textbox" data-hashtags>
												<div class="mentions">
													<div data-mentions-overlay data-default></div>
													<textarea class="o-form-control" name="hashtags" data-default="" data-initial="0" autocomplete="off"  placeholder="<?php echo JText::_('COM_ES_AUDIO_FORM_HASHTAGS_PLACEHOLDER', true);?>"
														data-story-textField data-mentions-textarea><?php echo $hashtags; ?></textarea>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>

				<div class="es-forms__actions">
					<div class="o-form-actions">
						<a href="<?php echo $returnLink;?>" class="btn btn-es-default-o t-pull-left"><?php echo JText::_('COM_ES_CANCEL');?></a>
						<button class="btn btn-es-primary-o t-pull-right" data-save-button><?php echo JText::_('COM_EASYSOCIAL_SAVE_BUTTON');?></button>
					</div>
				</div>

				<?php echo $this->html('suggest.hashtags'); ?>

				<?php if ($audio->uid && $audio->type) { ?>
				<input type="hidden" name="uid" value="<?php echo $audio->uid;?>" />
				<input type="hidden" name="type" value="<?php echo $audio->type;?>" />
				<?php } ?>

				<input type="hidden" id="albumart_source" name="albumart_source" data-audio-albumart-source value="<?php echo $audio->isNew() || !$this->config->get('audio.allowencode') ? 'upload' : $audio->albumart_source; ?>" />
				<input type="hidden" id="albumartData" name="albumartData" data-audio-albumart-data />
				<input type="hidden" name="id" value="<?php echo $audio->id;?>" />
				<?php echo $this->html('form.action', 'audios', 'save'); ?>
			</form>
		</div>
	</div>
</div>
