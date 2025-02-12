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
<div class="row">
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_CLUSTERS_CATEGORY_FORM_GENERAL', 'COM_ES_CLUSTERS_CATEGORY_FORM_GENERAL_INFO'); ?>

			<div class="panel-body">
				<div class="form-group" data-category-avatar data-hasavatar="<?php echo $category->hasAvatar(); ?>" data-defaultavatar="<?php echo $category->getDefaultAvatar(); ?>">
					<?php echo $this->html('panel.label', 'COM_ES_CLUSTERS_CATEGORY_FORM_AVATAR'); ?>

					<div class="col-md-7">
						<?php if ($category->id) { ?>
						<div class="mb-20">
							<img src="<?php echo $category->getAvatar();?>" class="es-avatar es-avatar-md es-avatar-border-sm" data-category-avatar-image />
						</div>
						<?php } ?>

						<div>
							<input type="file" name="avatar" data-uniform data-category-avatar-upload />
							<span data-category-avatar-remove-wrap <?php if(!$category->hasAvatar()) { ?>style="display: none;"<?php } ?>>
								<?php echo JText::_('COM_EASYSOCIAL_OR'); ?>
								<a href="javascript:void(0);" class="btn btn-es-danger btn-sm" data-id="<?php echo $category->id;?>" data-category-avatar-remove-button><i class="fa fa-times"></i> <?php echo JText::_('COM_ES_FORM_REMOVE_AVATAR'); ?></a>
							</span>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_SELECT_WORKFLOW', true, '', 5, true); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.workflows', 'workflow_id', $clusterType, $category->getWorkflow()->id); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_CLUSTERS_CATEGORY_FORM_TITLE', true, '', 5, true); ?>

					<div class="col-md-7">
						<input type="text" name="title" id="title" class="o-form-control" value="<?php echo $category->title;?>" placeholder="<?php echo $this->html('string.escape', JText::_('COM_ES_CLUSTERS_CATEGORY_FORM_TITLE_PLACEHOLDER'));?>" />
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_CLUSTERS_CATEGORY_FORM_ALIAS'); ?>

					<div class="col-md-7">
						<input type="text" name="alias" id="alias" class="o-form-control" value="<?php echo $category->alias;?>" />
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_CLUSTERS_CATEGORY_FORM_PARENT'); ?>

					<div class="col-md-7">
						<?php echo $parentList; ?>
					</div>

					<input type="hidden" name="oriParentId" value="<?php echo $category->parent_id;?>" />
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_CLUSTERS_CATEGORY_FORM_USE_AS_CONTAINER'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'container', $category->container, 'container'); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_CLUSTERS_CATEGORY_FORM_DESCRIPTION'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.textarea', 'description', null, $category->description, ['attributes' => 'data-category-description']); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_CLUSTERS_CATEGORY_FORM_PUBLISHING_STATUS'); ?>

					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'state', $category->state, 'state'); ?>
					</div>
				</div>

				<?php if (ES::get('multisites')->exists()) { ?>
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_EASYSOCIAL_CATEGORIES_FORM_SITE_ID'); ?>

					<div class="col-md-7">
						<?php echo ES::get('multisites')->getForm('site_id', $category->site_id); ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_' . strtoupper($clusterType) . 'S_CATEGORY_FORM_USER_ACCESS'); ?>

			<div class="panel-body">
				<div class="form-group">
					<label class="col-md-4">
						<?php echo JText::_('COM_ES_CLUSTERS_CATEGORY_FORM_SELECT_PROFILES');?>
						<i class="fa fa-question-circle pull-right"
							<?php echo $this->html('bootstrap.popover', JText::_('COM_ES_CLUSTERS_CATEGORY_FORM_SELECT_PROFILES'), JText::_('COM_ES_CLUSTERS_CATEGORY_FORM_SELECT_PROFILES_HELP'), 'bottom'); ?>
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
