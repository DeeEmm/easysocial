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
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_APPS_APPLICATION_INFO'); ?>

			<div class="panel-body">
				<h2><?php echo $app->title;?></h2>
				<p class="t-lg-mt--sm t-lg-mb--xl"><?php echo $desc; ?></p>

				<table class="table table-striped table-borderless">
					<tbody>
						<tr>
							<td>
								<b><?php echo JText::_('COM_EASYSOCIAL_APPS_INSTALLER_AUTHOR');?></b>
							</td>
							<td>
								<?php echo $meta->author; ?>
							</td>
						</tr>
						<tr>
							<td>
								<b><?php echo JText::_('COM_EASYSOCIAL_APPS_INSTALLER_VERSION');?></b>
							</td>
							<td>
								<?php echo $meta->version; ?>
							</td>
						</tr>
						<tr>
							<td>
								<b><?php echo JText::_('COM_EASYSOCIAL_APPS_INSTALLER_WEBSITE');?></b>
							</td>
							<td>
								<a href="<?php echo $meta->url; ?>" target="_blank"><?php echo $meta->url; ?></a>
							</td>
						</tr>
					</tbody>
				</table>

				<div class="t-lg-mt--md">
					<div class="o-grid">
						<div class="o-grid__cell">
							<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=apps');?>" class="btn btn-es-primary">
								&larr; <?php echo JText::_('COM_EASYSOCIAL_BACK_TO_APPLICATION_LISTINGS'); ?>
							</a>
						</div>

						<div class="o-grid__cell t-text--right">
							<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=apps&layout=install');?>" class="btn btn-es-default"><?php echo JText::_('COM_EASYSOCIAL_APPS_INSTALL_OTHER_APPS');?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
