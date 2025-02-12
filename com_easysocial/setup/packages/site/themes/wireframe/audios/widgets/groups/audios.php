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
<div class="es-side-widget is-module">
	<?php echo $this->html('widget.title', 'APP_AUDIO_PROFILE_WIDGET_TITLE_AUDIOS'); ?>

	<div class="es-side-widget__bd">
		<?php echo $this->html('widget.audios', $audios, 'COM_ES_AUDIO_WIDGETS_NO_AUDIO_CURRENTLY'); ?>
	</div>

	<div class="es-side-widget__ft">
		<?php echo $this->html('widget.viewAll', 'COM_ES_VIEW_ALL', ESR::audios(array('uid' => $group->getAlias(), 'type' => SOCIAL_TYPE_GROUP))); ?>
	</div>
</div>
