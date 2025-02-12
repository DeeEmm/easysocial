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
<style type="text/css">
body #es .es-sharer__info {
	padding: 10px 20px 20px;
}

body #es .es-sharer__login {
	padding: 0 20px;
}
</style>
<form method="post" action="<?php echo JRoute::_('index.php');?>">
	<div id="es">
		<div class="es-sharer">
			<div class="es-sharer__title">
				<img src="<?php echo ES::getLogo();?>" alt="" width="120" />
			</div>

			<p class="es-sharer__info t-lg-mb--no"><?php echo JText::_('COM_ES_SHARER_LOGIN_INFO');?></p>

			<?php if ($alert) { ?>
			<div style="padding: 0 20px;">
				<?php echo $alert; ?>
			</div>
			<?php } ?>

			<div class="es-sharer__login">
				<?php echo $this->html('form.floatinglabel', 'COM_EASYSOCIAL_USERNAME', 'username'); ?>

				<?php echo $this->html('form.floatinglabel', 'COM_EASYSOCIAL_PASSWORD', 'password', 'password'); ?>

				<div class="o-checkbox">
					<input type="checkbox" id="es-quick-remember" type="checkbox" name="remember" value="1" />
					<label for="es-quick-remember">
						<?php echo JText::_('COM_EASYSOCIAL_LOGIN_REMEMBER_YOU');?>
					</label>
				</div>
			</div>

			<?php if ($sso->isEnabled('facebook')) { ?>
			<div class="es-sharer__login">
				<?php echo $sso->getLoginButton('facebook', '', JText::_('COM_ES_LOGIN_FACEBOOK'), $return, $isSharer); ?>
			</div>
			<?php } ?>

			<div class="es-sharer__action">
				<div class="es-story-meta-buttons">
					<button class="btn btn-es-default-o" type="button" onclick="closeWindow();">
						<?php echo JText::_('COM_ES_CANCEL');?>
					</button>
				</div>

				<div class="es-story-actions">
					<button class="btn btn-es-primary es-story-submit" type="submit">
						<i class="fa fa-lock"></i>&nbsp; <?php echo JText::_('COM_ES_LOGIN_BUTTON');?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<?php echo $this->html('form.action', 'account', 'login'); ?>
	<?php echo $this->html('form.hidden', 'return', $return); ?>
	<?php echo $this->html('form.hidden', 'returnFailed', $return); ?>
	<?php echo $this->html('form.hidden', 'fromSharer', true); ?>
</form>

<script>
	window.resizeTo(450, 450);
</script>
