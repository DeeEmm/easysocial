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
<div class="es-restricted es-stream-restricted">
	<?php echo $this->html('html.miniheader', $cluster); ?>

	<?php echo $this->html('html.restricted', 'COM_EASYSOCIAL_STREAM_RESTRICTED_CONTENT', 'COM_EASYSOCIAL_STREAM_PRIVACY_NOT_ALLOWED_DESC', true,
		$this->my->id ? '' : '<div class="t-lg-mt--md"><a href="javascript:void(0);" class="btn btn-es-primary btn-sm" onclick="EasySocial.login();">' . JText::_('COM_ES_LOGIN_BUTTON') . '</a></div>'
	); ?>
</div>
