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
<form name="installerForm" class="installerForm" method="post" enctype="multipart/form-data" data-installer-form>
<div class="row">
	<div class="col-md-7">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_APPS_INSTALLER_UPLOAD_PACKAGE'); ?>

			<div class="panel-body">
				<input type="file" name="package" id="package" class="input" data-uniform />
				<button class="btn btn-es-default-o t-lg-ml--md btn-sm" data-install-upload><?php echo JText::_('COM_ES_UPLOAD_AND_INSTALL');?></button>
			</div>
		</div>

		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_APPS_INSTALLER_DIRECTORY_PACKAGE'); ?>

			<div class="panel-body">
				<div class="o-input-group">
					<input type="text" name="package-directory" id="package-directory" value="<?php echo $temporaryPath;?>" class="o-form-control" />

					<div class="o-input-group__btn">
						<button class="btn btn-es-default-o" data-install-directory><?php echo JText::_('COM_ES_UPLOAD_AND_INSTALL');?> &raquo;</button>
					</div>
				</div>
			</div>

		</div>
	</div>

	<div class="col-md-5">
		<div class="panel">	
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_APPS_INSTALLER_DIRECTORY_PERMISSIONS'); ?>

			<div class="panel-body">
				<table class="table table-striped table-noborder" style="table-layout: fixed">
					<thead>
						<th><?php echo JText::_('COM_EASYSOCIAL_APPS_DIRECTORY');?></th>
						<th width="24%"><?php echo JText::_('COM_EASYSOCIAL_APPS_PERMISSIONS' );?></th>
					</thead>
					<tbody>
						<?php foreach( $directories as $directory ){ ?>
						<tr>
							<td>
								<span style="word-break:break-all">
									<span class="word-wrap">
										<?php echo $directory->path; ?>
									</span>
								</span>
							</td>
							<td>
								<?php if( $directory->writable ){ ?>
									<span class="text-success" data-es-provide="tooltip" data-original-title="<?php echo JText::_( 'COM_EASYSOCIAL_APPS_INSTALLER_WRITABLE_DESC' , true );?>">
										<?php echo JText::_( 'COM_EASYSOCIAL_APPS_DIRECTORY_WRITABLE' ); ?>
									</span>
								<?php } else { ?>
									<span class="text-danger" data-es-provide="tooltip" data-original-title="<?php echo JText::_( 'COM_EASYSOCIAL_APPS_INSTALLER_UNWRITABLE_DESC' , true );?>">
										<?php echo JText::_( 'COM_EASYSOCIAL_APPS_DIRECTORY_UNWRITABLE' ); ?>
									</span>
								<?php } ?>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_easysocial" />
<input type="hidden" name="controller" value="apps" />
<input type="hidden" name="task" value="install" />
</form>
