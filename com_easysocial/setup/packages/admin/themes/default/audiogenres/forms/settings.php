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
<div class="row">
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_SETTINGS_AUDIO_GENRE_GENERAL_SETTINGS'); ?>

			<div class="panel-body">
				<div class="form-group">
					<label for="title" class="col-md-4">
						<?php echo JText::_('COM_EASYSOCIAL_SETTINGS_TITLE');?>
						<i class="fa fa-question-circle pull-right"
							<?php echo $this->html('bootstrap.popover', JText::_('COM_EASYSOCIAL_SETTINGS_TITLE'), JText::_('COM_ES_AUDIO_GENRE_FORM_TITLE_DESC'), 'bottom'); ?>
						></i>
					</label>
					<div class="col-md-8">
						<?php echo $this->html('form.text', 'title', 'title', $genre->title, array('placeholder' => 'COM_ES_AUDIO_GENRE_FORM_TITLE_PLACEHOLDER', 'attr' => 'data-genre-title')); ?>
					</div>
				</div>

				<div class="form-group">
					<label for="title" class="col-md-4">
						<?php echo JText::_('COM_ES_AUDIO_GENRE_FORM_ALIAS' );?>
						<i class="fa fa-question-circle pull-right"
							<?php echo $this->html('bootstrap.popover', JText::_('COM_ES_AUDIO_GENRE_FORM_ALIAS' ), JText::_('COM_ES_AUDIO_GENRE_FORM_ALIAS_DESC' ), 'bottom' ); ?>
						></i>
					</label>
					<div class="col-md-8">
						<?php echo $this->html('form.text', 'alias', 'alias', $genre->alias, array('placeholder' => 'COM_ES_AUDIO_GENRE_FORM_ALIAS_PLACEHOLDER', 'attr' => 'data-genre-alias')); ?>
					</div>
				</div>

				<div class="form-group">
					<label for="description" class="col-md-4">
						<?php echo JText::_('COM_ES_AUDIO_GENRE_FORM_DESCRIPTION' );?>
						<i class="fa fa-question-circle pull-right"
							<?php echo $this->html('bootstrap.popover', JText::_('COM_ES_AUDIO_GENRE_FORM_DESCRIPTION' ), JText::_('COM_ES_AUDIO_GENRE_FORM_DESCRIPTION_DESC' ), 'bottom' ); ?>
						></i>
					</label>
					<div class="col-md-8">
						<?php echo $this->html('form.textarea', 'description', 'description', $genre->description, array('placeholder' => 'COM_ES_AUDIO_GENRE_FORM_DESCRIPTION_PLACEHOLDER')); ?>
					</div>
				</div>

				<div class="form-group">
					<label class="col-md-4">
						<?php echo JText::_('COM_ES_AUDIO_GENRE_FORM_PUBLISHING_STATUS' );?>
						<i class="fa fa-question-circle pull-right"
							<?php echo $this->html('bootstrap.popover', JText::_('COM_ES_AUDIO_GENRE_FORM_PUBLISHING_STATUS' ), JText::_('COM_ES_AUDIO_GENRE_FORM_PUBLISHING_STATUS_DESC' ), 'bottom' ); ?>
						></i>
					</label>
					<div class="col-md-8">
						<?php echo $this->html('form.toggler', 'state', $genre->state, 'state'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_AUDIO_GENRE_FORM_USER_ACCESS'); ?>

			<div class="panel-body">
				<div class="form-group">
					<label class="col-md-4">
						<?php echo JText::_('COM_EASYSOCIAL_FORM_ALLOWED_PROFILE_TYPES');?>
						<i class="fa fa-question-circle pull-right"
							<?php echo $this->html('bootstrap.popover', JText::_('COM_EASYSOCIAL_FORM_ALLOWED_PROFILE_TYPES'), JText::_('COM_EASYSOCIAL_FORM_ALLOWED_PROFILE_TYPES_DESC'), 'bottom'); ?>
						></i>
					</label>
					<div class="col-md-8">
						<?php echo $this->html('form.profiles', 'create_access[]', 'create_access', $createAccess, array('multiple' => true, 'style="height:150px;"')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
