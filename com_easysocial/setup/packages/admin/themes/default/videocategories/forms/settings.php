<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2017 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_SETTINGS_VIDEOS_CATEGORY_GENERAL_SETTINGS'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_FORMS_TITLE'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.text', 'title', 'title', $category->title, array('placeholder' => 'COM_EASYSOCIAL_VIDEOS_CATEGORY_FORM_TITLE_PLACEHOLDER', 'attr' => 'data-category-title')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_FORMS_ALIAS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.text', 'alias', 'alias', $category->alias, array('placeholder' => 'COM_EASYSOCIAL_VIDEOS_CATEGORY_FORM_ALIAS_PLACEHOLDER', 'attr' => 'data-category-alias')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_VIDEO_CATEGORY_DESCRIPTION'); ?>
					
					<div class="col-md-7">
						<?php echo $this->html('form.textarea', 'description', 'description', $category->description, array('placeholder' => 'COM_EASYSOCIAL_VIDEOS_CATEGORY_FORM_DESCRIPTION_PLACEHOLDER')); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_FORMS_PUBLISHED'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'state', $category->state, 'state'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_VIDEO_CATEGORY_FORM_USER_ACCESS', 'COM_EASYSOCIAL_VIDEO_CATEGORY_FORM_USER_ACCESS_INFO'); ?>

			<div class="panel-body">
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_FORM_ALLOWED_PROFILE_TYPES'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.profiles', 'create_access[]', 'create_access', $createAccess, array('multiple' => true, 'style="height:150px;"')); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
