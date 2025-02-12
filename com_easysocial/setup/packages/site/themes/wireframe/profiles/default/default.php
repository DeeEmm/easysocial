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
<div class="es-group-category">

	<?php echo $this->html('header.profilestype', $profile); ?>

	<div class="es-container t-lg-mt--xl">

		<?php echo $this->html('html.sidebar'); ?>

		<?php if ($this->isMobile()) { ?>
			<?php echo $this->includeTemplate('site/profiles/default/mobile'); ?>
		<?php } ?>

		<div class="es-content">
			<?php echo $this->html('html.snackbar', 'COM_EASYSOCIAL_GROUPS_RECENT_UPDATES', 'div'); ?>

			<div class="es-content-wrap" data-es-group-item-content>
				<?php echo $stream->html();?>
			</div>
		</div>
	</div>
</div>
