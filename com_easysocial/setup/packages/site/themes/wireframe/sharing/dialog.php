<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<dialog>
	<width>450</width>
	<height><?php echo $emailSharing ? '350' : '100'; ?></height>
	<selectors type="json">
	{
		"{shareButton}": "[data-share-button]",
		"{cancelButton}": "[data-cancel-button]"
	}
	</selectors>
	<bindings type="javascript">
	{
		"{cancelButton} click": function() {
			this.parent.close();
		}
	}
	</bindings>
	<title><?php echo $title; ?></title>
	<content>
		<div class="es-sharing" data-sharing>
			
			<?php if (isset($vendors) && $vendors) { ?>
			<ul class="es-sharing-list">
				<?php foreach ($vendors as $vendor) { ?>
				<li>
					<?php echo $vendor->getHTML(); ?>
				</li>
				<?php } ?>
			</ul>
			<?php } ?>

			<hr class="es-hr t-lg-mt--xl" />

			<?php if (isset($email) && $email) { ?>
				<?php echo $email->getHTML(); ?>
			<?php } ?>
		</div>
	</content>
	<?php if ($emailSharing) { ?>
	<buttons>
		<button data-cancel-button type="button" class="btn btn-es-default btn-sm"><?php echo JText::_('COM_ES_CANCEL'); ?></button>
		<button data-share-button type="button" class="btn btn-es-primary btn-sm"><?php echo JText::_('COM_EASYSOCIAL_SHARING_EMAIL_SEND'); ?></button>
	</buttons>
	<?php } ?>
</dialog>