<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-stream-login-box">
	<div class="es-stream-embed is-broadcasts">
		<div class="es-stream-embed__context">
			<div class="es-stream-embed__broadcasts-title">
				 <?php echo JText::_('COM_EASYSOCIAL_GUEST_STREAM_LOGIN_DESCRIPTION'); ?>
			</div>

			<div class="es-stream-embed__broadcasts-text">
				<a class="btn btn-es-default btn-sm" href="<?php echo ESR::login(array(), false); ?>">
					<?php echo JText::_('COM_EASYSOCIAL_LOGIN_BUTTON'); ?>
				</a>

				<?php if ($this->config->get('registrations.enabled')) { ?>
				<?php echo JText::_('COM_EASYSOCIAL_GUEST_STREAM_OR'); ?>

				<a class="btn btn-es-primary btn-sm" href="<?php echo ESR::registration(); ?>">
					<?php echo JText::_('COM_EASYSOCIAL_REGISTER_NOW_BUTTON'); ?>
				</a>
				<?php } ?>
			</div>
		</div>
		<div class="es-stream-embed__broadcasts-icon">
			<i class="fa fa-lock"></i>
		</div>
	</div>
</div>