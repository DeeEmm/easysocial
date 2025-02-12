<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<style>
#com-users-methods-list-container .btn:hover {color: #fff;}
</style>
<div class="es-container" data-es-container data-mfa-config>

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->includeTemplate('site/profile/mfa/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content">
		<?php echo $this->render('module', 'es-profile-mfa-before-contents'); ?>

		<div class="tab-content">
			<div class="tab-content__item is-active" data-contents data-type="mfa">
				<div class="es-forms__group">
					<div class="es-forms__title">
						<?php echo $this->html('form.title', 'COM_ES_PROFILE_MFA_TITLE'); ?>
					</div>

					<div class="es-forms__content">
						<?php echo $mfaForms; ?>
					</div>
				</div>
			</div>
		</div>

		<?php echo $this->render('module', 'es-profile-mfa-after-contents'); ?>
	</div>
</div>

