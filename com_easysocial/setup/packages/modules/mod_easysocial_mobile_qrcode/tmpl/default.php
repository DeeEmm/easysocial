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
<div id="es" class="mod-es mod-es-qrcode-mobile <?php echo $lib->getSuffix();?>">
	<div class="t-text--center">
		<img src="<?php echo ES::getMobileQRLoginUrl(); ?>" width="128" />

		<p class="t-lg-mt--lg"><?php echo JText::_('MOD_ES_QRCODE_SCANNER_INFO');?></p>
	</div>
</div>
