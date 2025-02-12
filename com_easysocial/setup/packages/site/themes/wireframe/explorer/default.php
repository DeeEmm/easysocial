<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-explorer is-side-open <?php echo !$showUpload ? 'is-no-upload' : '';?>"
	data-es-explorer data-fd-explorer="<?php echo $uuid;?>"
	data-uid="<?php echo $uid; ?>"
	data-type="<?php echo $type; ?>"
	data-url="site/controllers/explorer/hook"
	data-controller-name="<?php echo isset($options['controllerName']) ? $options['controllerName'] : 'groups';?>"
	data-allowed-extensions="<?php echo isset($options['allowedExtensions']) ? $options['allowedExtensions'] : '';?>">

	<div class="es-explorer__sidebar">
		<?php if ($showUpload) { ?>
		<div class="es-explorer__sidebar-hd">
			<button class="btn btn-es-default-o btn-sm btn-add-folder" data-fd-explorer-button="addFolder">
				<i class="fa fa-plus"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_EXPLORER_ADD_FOLDER');?>
			</button>

			<?php if ($isMobile) { ?>
			<button class="btn btn-link btn-sm pull-right t-text--muted t-lg-ml--xl es-explorer__btn-close" data-close>
				<i class="fa fa-times"></i>
			</button>
			<?php } ?>
		</div>
		<?php } ?>

		<div class="es-explorer__sidebar-bd">
			<div class="es-explorer__sidebar-title">
				<?php echo JText::_('COM_EASYSOCIAL_EXPLORER_FOLDERS');?>
			</div>
			<div class="fd-explorer-folder-group" data-explorer-folder-group></div>
		</div>
	</div>
	<div class="es-explorer__content">

		<div class="es-explorer__content-hd">
			<div class="es-explorer-browser-action">

				<div class="es-explorer-browser-action-bar">
					<?php if ($isMobile) { ?>
					<div class="es-explorer-browser-action-bar__cell-back">
						<div class="es-explorer-browser-action-bar__back">
							<a href="javascript:void(0);" data-es-explorer-back><i class="fa fa-chevron-left"></i></a>
						</div>

					</div>
					<?php } ?>

					<?php if ($showUpload || $showClose || $showUse) { ?>
					<div class="es-explorer-browser-action-bar__cell-action">
						<?php if ($showAction) { ?>
						<div class="o-checkbox o-checkbox--inline">
							<input type="checkbox" data-fd-explorer-select-all id="fd-explorer-select-all"/>
							<label class="" for="fd-explorer-select-all">
								&nbsp;
							</label>
						</div>

						<a href="javascript:void(0);" data-fd-explorer-button="removeFile"><?php echo JText::_('COM_EASYSOCIAL_EXPLORER_DELETE_SELECTED');?></a>
						<?php } ?>

						<?php if ($showClose) { ?>
						<button class="btn btn-link btn-sm pull-right t-text--muted t-lg-ml--xl t-sm-visible " data-close>
							<i class="fa fa-times"></i>
						</button>
						<?php } ?>


					</div>
					<div class="es-explorer-browser-action-bar__cell-upload">
						<div class="">
							<?php if ($showUpload) { ?>
								<span class="t-sm-block">
									<?php if (isset($options['uploadLimit'])) { ?>
									<span class="upload-limit">
										<?php echo JText::sprintf('COM_EASYSOCIAL_EXPLORER_UPLOAD_LIMIT', $options['uploadLimit']); ?>
									</span>
									<?php } ?>

									<button class="btn btn-es-default-o btn-sm fd-explorer-upload-button" data-plupload-upload-button>
										<i class="fa fa-upload"></i> <?php echo JText::_('COM_EASYSOCIAL_EXPLORER_UPLOAD');?>
									</button>
								</span>
							<?php } ?>

							<?php if ($showUse) { ?>
								<div class="o-btn-group t-lg-ml--md t-xs-mt--lg">

									<button class="btn btn-es-default-o btn-sm btn-file-insert" data-fd-explorer-button="useFile">
										<i class="fa fa-check"></i>&nbsp; <?php echo JText::_('COM_EASYSOCIAL_EXPLORER_INSERT');?>
									</button>
								</div>
							<?php } ?>

							<?php if ($showClose) { ?>
							<button class="btn btn-link btn-sm pull-right t-text--muted t-lg-ml--xl t-sm-hidden" data-close>
								<i class="fa fa-times"></i>
							</button>
							<?php } ?>
						</div>
					</div>
					<?php } ?>
				</div>

				<div class="o-loader o-loader--sm"></div>
			</div>
		</div>

		<div class="es-explorer-browser">
			<div class="fd-explorer-viewport"></div>
		</div>
	</div>
</div>
