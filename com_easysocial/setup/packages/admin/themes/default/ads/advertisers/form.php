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
<form name="adminForm" id="adminForm" class="pointsForm" method="post" enctype="multipart/form-data">
<div class="row">
	<div class="col-md-7">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_ES_ADS_FORM_BUSINESS'); ?>

			<div class="panel-body">

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_BUSINESS_LOGO'); ?>

					<div class="col-md-7">
						<?php if ($advertiser->logo) { ?>
						<div class="mb-20">
							<div class="es-img-holder">
								<img src="<?php echo $advertiser->getLogo();?>" width="128" />
							</div>
						</div>
						<?php } ?>

						<div style="clear:both;" class="t-lg-mb--xl">
							<input type="file" name="logo" id="logo" class="input" style="width:265px;" data-uniform />
						</div>
						<br />

						<div class="help-block">
							<?php echo JText::_('COM_ES_ADS_LOGO_SIZE_NOTICE'); ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_BUSINESS_NAME', true, '', 5, true); ?>

					<div class="col-md-7">
						<input type="text" class="o-form-control" value="<?php echo $this->html('string.escape', $advertiser->name);?>" name="name" id="name"  />
					</div>
				</div>

				<?php if (!$advertiser->isUnderModeration()) { ?>
				<div class="form-group">
					<?php echo $this->html('panel.label', 'COM_ES_ADS_FORM_STATE'); ?>
					<div class="col-md-7">
						<?php echo $this->html('form.toggler', 'state', $advertiser->state); ?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>

</div>

<?php echo $this->html('form.action', 'ads'); ?>
<input type="hidden" name="id" value="<?php echo $advertiser->id; ?>" />

<?php if ($advertiser->isUnderModeration()) { ?>
	<?php echo $this->html('form.hidden', 'cid[]', $advertiser->id); ?>
<?php } ?>

</form>
