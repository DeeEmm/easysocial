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
<form action="index.php" method="post" name="adminForm" class="esForm" id="adminForm" data-table-grid data-apps-store>

	<div class="app-filter-bar">
		<div class="app-filter-bar__cell">
			<?php echo $this->html('filter.search' , $search); ?>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<select name="type" class="o-form-control" data-table-grid-filter>
					<option value=""><?php echo JText::_('Application Type'); ?></option>
					<?php foreach ($types as $appType) { ?>
					<option value="<?php echo $appType;?>" <?php echo $appType == $type ? ' selected="selected"' : '';?>><?php echo ucfirst($appType);?></option>
					<?php } ?>
				</select>
			</div>
		</div>


		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<select name="category" class="o-form-control" data-table-grid-filter>
					<option value=""><?php echo JText::_('Category'); ?></option>
					<?php foreach ($categories as $appCategory) { ?>
					<option value="<?php echo $appCategory;?>" <?php echo $appCategory == $category ? ' selected="selected"' : '';?>><?php echo JText::_($appCategory);?></option>
					<?php } ?>
				</select>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left">
			<div class="app-filter-bar__filter-wrap">
				<select name="company" class="o-form-control" data-table-grid-filter>
					<option value=""><?php echo JText::_('Author'); ?></option>
					<option value="stackideas" <?php echo $company ? ' selected="selected"' : '';?>><?php echo JText::_('StackIdeas');?></option>
				</select>
			</div>
		</div>

		<div class="app-filter-bar__cell app-filter-bar__cell--empty"></div>

		<div class="app-filter-bar__cell app-filter-bar__cell--divider-left app-filter-bar__cell--last t-text--center">
			<div class="app-filter-bar__filter-wrap">
				<?php echo $this->html('filter.limit' , $limit); ?>
			</div>
		</div>
	</div>

	<div class="t-lg-mt--lg">
		<?php if ($featuredApps) { ?>
		<h3>Featured Apps</h3>
		<div class="es-cards es-cards--4">
			<?php foreach ($featuredApps as $featuredApp) { ?>
				<?php echo $this->loadTemplate('admin/apps/store/default.item', array('app' => $featuredApp)); ?>
			<?php } ?>
		</div>
		<?php } ?>

		<?php if ($apps) { ?>
		<div class="es-cards es-cards--4">
			<?php foreach ($apps as $app) { ?>
				<?php echo $this->loadTemplate('admin/apps/store/default.item', array('app' => $app)); ?>
			<?php } ?>
		</div>
		<?php } else if ($isSearch) { ?>
			<div class="languages-wrapper" data-apps-wrapper>
				<div class="languages-loader">
					<?php echo JText::_('COM_EASYSOCIAL_NO_ITEMS_FOUND'); ?>
				</div>
			</div>

		<?php } else { ?>
			<div class="languages-wrapper" data-apps-wrapper>
				<div class="languages-loader">
					<?php echo JText::_('COM_EASYSOCIAL_INITIALIZING_APPS_STORE');?><br />
				</div>

				<div class="invalid-api">
					<i class="fa fa-times text-error"></i>
					<div data-apps-error></div>
				</div>
			</div>
		<?php } ?>
	</div>

	<div class="footer-pagination">
		<?php echo $pagination->getListFooter(); ?>
	</div>

	<?php echo $this->html('form.token'); ?>

	<input type="hidden" name="boxchecked" value="0" data-table-grid-box-checked />
	<input type="hidden" name="task" value="" data-table-grid-task />
	<input type="hidden" name="option" value="com_easysocial" />
	<input type="hidden" name="controller" value="store" />
	<input type="hidden" name="view" value="apps" />
	<input type="hidden" name="layout" value="store" />
</form>
