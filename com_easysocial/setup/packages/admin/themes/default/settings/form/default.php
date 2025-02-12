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
<form id="adminForm" name="adminForm" class="adminForm" action="index.php" method="post" enctype="multipart/form-data">
	<div class="wrapper accordion">
		<div class="tab-box tab-box-alt">
			<div class="tabbable">

				<ul class="nav nav-tabs nav-tabs-icons">
					<?php foreach ($tabs as $tab) { ?>
					<li class="<?php echo $tab->active ? 'active' : '';?>">
						<a href="#<?php echo $tab->id; ?>" data-es-toggle="tab"><?php echo $tab->title; ?></a>
					</li>
					<?php } ?>
				</ul>

				<div class="tab-content">
					<?php foreach ($tabs as $tab) { ?>
						<div id="<?php echo $tab->id; ?>" class="tab-pane <?php echo $tab->active ? 'active' : '';?>">
							<?php echo $tab->contents;?>
						</div>
					<?php } ?>
				</div>

			</div>
		</div>
	</div>

	<div class="hidden btn-wrapper es-settings-search" data-search-wrapper>
		<input type="text" class="es-settings-search__input" data-settings-search placeholder="Search for settings ..."/>

		<div class="hidden es-settings-search__result" data-search-result style="">
		</div>
	</div>

	<div id="toolbar-actions" class="btn-wrapper t-hidden" data-toolbar-actions="others">
		<div class="dropdown">
			<button type="button" class="btn btn-small dropdown-toggle" data-toggle="dropdown">
				<span class="icon-cog"></span> <?php echo JText::_('Other Actions');?> &nbsp;<span class="caret"></span>
			</button>

			<ul class="dropdown-menu">
				<li>
					<a href="javascript:void(0);" data-action="export">
						<?php echo JText::_('COM_EASYSOCIAL_SETTINGS_EXPORT_SETTINGS'); ?>
					</a>
				</li>
				<li>
					<a href="javascript:void(0);" data-action="import">
						<?php echo JText::_('COM_EASYSOCIAL_SETTINGS_IMPORT_SETTINGS'); ?>
					</a>
				</li>
				<li class="divider">
				<li>
					<a href="javascript:void(0);" data-action="reset">
						<?php echo JText::_('COM_EASYSOCIAL_RESET_TO_FACTORY'); ?>
					</a>
				</li>
			</ul>
		</div>
	</div>


	<?php echo $this->html('form.hidden', 'page', $page); ?>
	<?php echo $this->html('form.hidden', 'active', $active, 'data-active-tab'); ?>
	<?php echo $this->html('form.action', 'settings'); ?>
</form>
