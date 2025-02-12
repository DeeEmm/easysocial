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
<form action="index.php" id="adminForm" method="post" name="adminForm" data-table-grid data-audios-form>
	<div class="wrapper accordion">
		<div class="tab-box tab-box-alt">
			<div class="tabbable">

				<ul id="audiosForm" class="nav nav-tabs">
					<li class="tabItem active">
						<a data-es-toggle="tab" href="#general" data-form-tabs data-item="general">
							<?php echo JText::_('COM_EASYSOCIAL_PROFILES_TAB_PROFILE_GENERAL');?>
						</a>
					</li>
				</ul>

				<div class="tab-content">
					<div id="general" class="tab-pane active in">
						<div class="row">
							<div class="col-md-6">
								<div class="panel">
									<?php echo $this->html('panel.heading', 'COM_ES_AUDIO_FORM_GENERAL'); ?>

									<div class="panel-body">
										<div class="form-group">
											<label for="title" class="col-md-4">
												<?php echo JText::_('COM_ES_AUDIO_AUTHOR');?>
											</label>
											<div class="col-md-8">
												<input type="text" class="o-form-control input-sm" value="<?php echo $audio->getAuthor()->getName();?>" disabled="true" />
											</div>
										</div>

										<div class="form-group">
											<label for="title" class="col-md-4">
												<?php echo JText::_('COM_ES_AUDIO_FORM_GENRE');?>
												<i class="fa fa-question-circle t-lg-pull-right"
													<?php echo $this->html('bootstrap.popover' , 'COM_ES_AUDIO_FORM_GENRE', '', 'bottom'); ?>
												></i>
											</label>
											<div class="col-md-8">
												<select name="genre_id" class="o-form-control input-sm">
													<option value=""><?php echo JText::_('COM_ES_AUDIO_FORM_SELECT_GENRE');?></option>
													<?php foreach ($genres as $genre) { ?>
													<option value="<?php echo $genre->id;?>" <?php echo $table->genre_id == $genre->id ? ' selected="selected"' : '';?>><?php echo JText::_($genre->title);?></option>
													<?php } ?>
												</select>
											</div>
										</div>

										<div class="form-group">
											<label for="title" class="col-md-4">
												<?php echo JText::_('COM_ES_AUDIO_FORM_TITLE');?>
												<i class="fa fa-question-circle t-lg-pull-right"
													<?php echo $this->html('bootstrap.popover' , 'COM_ES_AUDIO_FORM_TITLE', '', 'bottom'); ?>
												></i>
											</label>
											<div class="col-md-8">
												<input type="text" name="title" id="title" class="o-form-control input-sm" value="<?php echo $this->html('string.escape', $table->title);?>"/>
											</div>
										</div>

										<div class="form-group">
											<label for="title" class="col-md-4">
												<?php echo JText::_('COM_ES_AUDIO_FORM_DESCRIPTION');?>
												<i class="fa fa-question-circle t-lg-pull-right"
													<?php echo $this->html('bootstrap.popover' , 'COM_ES_AUDIO_FORM_DESCRIPTION', '', 'bottom'); ?>
												></i>
											</label>
											<div class="col-md-8">
												<textarea name="description" class="o-form-control input-sm" placeholder="<?php echo JText::_('COM_ES_AUDIO_FORM_DESCRIPTION_PLACEHOLDER');?>"><?php echo $table->description;?></textarea>
											</div>
										</div>

										<div class="form-group">
											<label for="es-fields-85" class="col-md-4 control-label">
												<?php echo JText::_('COM_ES_AUDIO_FORM_PEOPLE_IN_THIS_AUDIO');?>
												<i class="fa fa-question-circle t-lg-pull-right"
													<?php echo $this->html('bootstrap.popover' , 'COM_ES_AUDIO_FORM_PEOPLE_IN_THIS_AUDIO', '', 'bottom'); ?>
												></i>
											</label>
											<div class="col-md-8">
												<div class="textboxlist disabled" data-mentions>

													<?php if ($userTags) { ?>
														<?php foreach ($userTags as $userTags) { ?>
															<div class="textboxlist-item" data-id="<?php echo $userTags->getEntity()->id; ?>" data-title="<?php echo $userTags->getEntity()->getName(); ?>" data-textboxlist-item>
																<span class="textboxlist-itemContent" data-textboxlist-itemContent>
																	<img width="16" height="16" src="<?php echo $userTags->getEntity()->getAvatar(SOCIAL_AVATAR_SMALL);?>" />
																	<?php echo $userTags->getEntity()->getName(); ?>
																	<input type="hidden" name="items" value="<?php echo $userTags->getEntity()->id; ?>" />
																</span>
																<a class="textboxlist-itemRemoveButton" href="javascript: void(0);" data-textboxlist-itemRemoveButton>
																	<i class="fa fa-times"></i>
																</a>
															</div>
														<?php } ?>
													<?php } ?>

													<input type="text" autocomplete="off"
														disabled
														class="textboxlist-textField"
														data-textboxlist-textField
														placeholder="<?php echo JText::_('COM_EASYSOCIAL_CONVERSATIONS_START_TYPING');?>"
													/>
												</div>
											</div>
										</div>

										<div class="form-group">
											<label for="es-fields-85" class="col-md-4 control-label">
												<?php echo JText::_('COM_ES_AUDIO_FORM_HASHTAGS');?>
												<i class="fa fa-question-circle t-lg-pull-right"
													<?php echo $this->html('bootstrap.popover' , 'COM_ES_AUDIO_FORM_HASHTAGS', '', 'bottom'); ?>
												></i>
											</label>

											<div class="col-md-8">
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

										<div class="form-group">
											<label for="es-fields-85" class="col-md-4 control-label">
												<?php echo JText::_('COM_ES_AUDIO_AUDIO_PRIVACY');?>
											</label>
											<div class="col-md-8">
												<div class="pull-left">
													<?php echo $privacy->form($audio->id, SOCIAL_TYPE_AUDIOS, $audio->getAuthor()->id, 'audios.view', true, null, array()); ?>
												</div>
											</div>
										</div>
									</div>
								</div>

							</div>

							<div class="col-md-6">
								<div class="panel">
									<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_PROFILES_FORM_AUDIO_SOURCE'); ?>

									<div class="panel-body">
										<?php if ($audio->isPendingProcess() || $audio->isProcessing()) { ?>
										<div class="form-group">
										<?php echo JText::_('COM_ES_AUDIO_FORM_AUDIO_IS_PENDING_OR_PROCESSING'); ?>
										</div>

										<input type="hidden" name="source" value="upload" />
										<?php } else { ?>
										<div class="form-group">
											<label for="title" class="col-md-4">
												<?php echo JText::_('COM_ES_AUDIO_FORM_AUDIO_TYPE');?>
												<i class="fa fa-question-circle t-lg-pull-right"
													<?php echo $this->html('bootstrap.popover' , 'COM_ES_AUDIO_FORM_AUDIO_TYPE', '', 'bottom'); ?>
												></i>
											</label>
											<div class="col-md-8">
												<label for="audio-link" class="radio-inline">
													<input id="audio-link" type="radio" name="source" value="link" data-audio-source <?php echo $audio->isLink() ? ' checked="checked"' : '';?>/>
													<span><?php echo JText::_('COM_ES_AUDIO_FORM_AUDIO_TYPE_EXTERNAL');?></span>
												</label>

												<label for="audio-uploads" class="radio-inline">
													<input id="audio-uploads" type="radio" name="source" value="upload" data-audio-source <?php echo $audio->isUpload() ? ' checked="checked"' : '';?>/>
													<span><?php echo JText::_('COM_ES_AUDIO_FORM_AUDIO_TYPE_UPLOAD');?></span>
												</label>
											</div>
										</div>

										<div class="form-group<?php echo $audio->isUpload() ? ' t-hidden' : '';?>" data-form-link data-form-source>
											<label for="audio-link-source" class="col-md-4 control-label">
												<?php echo JText::_('COM_ES_AUDIO_AUDIO_LINK');?>
											</label>
											<div class="col-md-8">
												<input type="text" class="o-form-control input-sm" name="link"
													placeholder="<?php echo JText::_('COM_ES_AUDIO_AUDIO_LINK_PLACEHOLDER');?>"
													value="<?php echo $audio->isLink() ? $audio->path : '';?>"
													id="audio-link-source"
												/>
											</div>
										</div>

										<div class="form-group<?php echo $audio->isLink() ? ' t-hidden' : '';?>" data-form-upload data-form-source>
											<label class="col-md-4 control-label">
												<?php echo JText::_('COM_ES_AUDIO_AUDIO_FILE');?>
												<i class="fa fa-question-circle t-lg-pull-right"
													<?php echo $this->html('bootstrap.popover' , 'COM_ES_AUDIO_AUDIO_FILE', '', 'bottom'); ?>
												></i>
											</label>
											<div class="col-md-8">
												<input type="file" name="audio" />
											</div>
										</div>
										<?php } ?>

									</div>
								</div>
							</div>
						</div>

					</div>
				</div>

			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'audios'); ?>
	<input type="hidden" name="id" value="<?php echo $audio->id;?>" />
</form>
