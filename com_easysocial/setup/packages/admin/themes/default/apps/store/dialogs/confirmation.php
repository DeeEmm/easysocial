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
<dialog>
	<?php if ($app->isExternal()) { ?>
	<width>450</width>
	<height>200</height>
	<?php } else { ?>
	<width>600</width>
	<height>250</height>
	<?php } ?>

	<selectors type="json">
	{
		"{cancelButton}": "[data-cancel-button]",
		"{purchaseForm}": "[data-purchase-form]",
		"{purchaseButton}": "[data-purchase-button]",
		"{installButton}": "[data-install-button]",
		"{installForm}": "[data-install-form]",
		"{checkbox}": "[data-install-checkbox]",
		"{alert}": "[data-alert-tnc]"
	}
	</selectors>
	<bindings type="javascript">
	{
		isChecked: function() {
			var checked = this.checkbox().is(':checked');

			return checked;
		},

		showAlert: function() {
			this.alert().removeClass('t-hidden');
		},

		hideAlert: function() {
			this.alert().addClass('t-hidden');
		},

		"{cancelButton} click": function() {
			this.parent.close();
		},

		"{installButton} click": function() {
			if (!this.isChecked()) {
				this.showAlert();
				return;
			}

			this.hideAlert();
			this.installForm().submit();
		},

		"{purchaseButton} click": function() {
			if (!this.isChecked()) {
				this.showAlert();
				return;
			}

			this.hideAlert();
			this.purchaseForm().submit();
		}
	}
	</bindings>
	<title>
		<?php if ($app->isInstalled()) { ?>
			<?php echo JText::_('Update Application'); ?>
		<?php } else { ?>
			<?php echo JText::_('Install Application'); ?>
		<?php } ?>
	</title>
	<content>
		<?php if ($app->isExternal()) { ?>
		<p class="t-lg-mt--md">
			<img src="<?php echo $app->getLogo();?>" align="right" width="64" height="64" class="t-lg-mr--xl t-lg-ml--md t-lg-mb--md" />
			The application that you are trying to install requires you to download the app from their own site.<br /><br />Click on the button below to visit the app page.
		</p>
		<?php } else { ?>

			<?php if ($app->isDownloadable() && $app->isDownloadableFromApi()) { ?>
			<p class="t-lg-mt--md">
				<?php echo JText::_('COM_EASYSOCIAL_APPS_STORE_INSTALL_APP'); ?>
			</p>
			<?php } ?>

			<?php if (!$app->isDownloadable() && $app->hasPaymentSupport()) { ?>
			<p class="t-lg-mt--md">
				<img src="<?php echo $app->getLogo();?>" align="right" width="64" height="64" class="t-lg-mr--xl t-lg-ml--md t-lg-mb--md" />
				This is a paid app and you will need to purchase this app before the system is able to install it. The cost of this app is <b>$<?php echo $app->getPrice();?></b> and it is only a <b>one time</b> fee.
				<br /> <br />
				Once you have purchased this app, you will not need to re-purchase it again as it would follow your account.
			</p>
			<?php } ?>

			<div class="o-checkbox t-lg-mt--xl">
				<input type="checkbox" id="terms" data-install-checkbox />
				<label for="terms">
					I agree to the <a href="https://stackideas.com/terms" target="_blank">terms and conditions</a> set by <a href="https://stackideas.com" target="_blank">stackideas.com</a>.
				</label>
			</div>

			<div class="t-text--danger t-hidden" data-alert-tnc>Please accept the terms and conditions before proceeding</div>
		<?php } ?>

		<div class="t-lg-mt--xl">
			<b>NOTE:</b> Support will not be provided for 3rd party extensions.
		</div>

		<form method="post" action="<?php echo JRoute::_('index.php');?>" data-install-form>
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="store" />
			<input type="hidden" name="task" value="install" />
			<input type="hidden" name="id" value="<?php echo $app->id;?>" />
			<?php if ($return) { ?>
				<input type="hidden" name="return" value="<?php echo $return;?>" />
			<?php } ?>
			<?php echo $this->html('form.token'); ?>
		</form>

		<form method="post" action="<?php echo JRoute::_('index.php');?>" data-purchase-form>
			<input type="hidden" name="option" value="com_easysocial" />
			<input type="hidden" name="controller" value="store" />
			<input type="hidden" name="task" value="purchase" />
			<input type="hidden" name="id" value="<?php echo $app->id;?>" />

			<?php if ($return) { ?>
				<input type="hidden" name="return" value="<?php echo $return;?>" />
			<?php } ?>
			
			<?php echo $this->html('form.token'); ?>
		</form>
	</content>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>

		<?php if ($app->isExternal()) { ?>
		<a href="<?php echo $app->getExternalPermalink();?>" class="btn btn-es-primary" target="_blank"><?php echo JText::_('View App'); ?> &nbsp;<i class="fa fa-external-link"></i></a>
		<?php } ?>

		<?php if ($app->isDownloadable() && $app->isDownloadableFromApi()) { ?>
			<button class="btn btn-es-primary"  data-install-button>
				<?php echo $app->isInstalled() ? JText::_('Update App') : JText::_('Install App'); ?>
			</button>
		<?php } ?>

		<?php if (!$app->isExternal() && !$app->isDownloadable() && $app->hasPaymentSupport()) { ?>
			<button type="button" class="btn btn-es-primary btn-sm" data-purchase-button><?php echo JText::_('Get Application Now!'); ?></button>
		<?php } ?>
	</buttons>
</dialog>
