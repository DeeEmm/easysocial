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
<div id="es">
	<div class="es-sharer">
		<div class="es-sharer__title">
			<img src="<?php echo ES::sharer()->getLogo();?>" width="16" alt=""> <?php echo JText::sprintf('COM_ES_SHARER_SHARE_ON', $siteName); ?>
		</div>

		<p style="padding: 20px;"><?php echo JText::_('COM_ES_SHARER_POSTED_SUCCESSFULLY');?>

		<div class="es-sharer__action">
			<div class="es-story-meta-buttons">
			</div>

			<div class="es-story-actions">
				<button class="btn btn-es-default es-story-submit" type="button" onclick="closeWindow();">
					<?php echo JText::_('COM_ES_CLOSE');?>
				</button>
			</div>
		</div>
	</div>
</div>

<script>
window.resizeTo(400, 250);
setTimeout(function () {
	window.close();
}, 5000);

</script>
