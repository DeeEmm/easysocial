<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div id="es" class="mod-es mod-es-photos <?php echo $lib->getSuffix();?>">
	<div class="es-side-embed-lists">
		<?php foreach ($photos as $photo) { ?>
		<div class="es-side-embed-lists__item">
			<a href="<?php echo $photo->getPermalink();?>"
				class="embed-responsive embed-responsive-16by9"
				alt="<?php echo $lib->html('string.escape', $photo->get('title'));?>"
				data-es-provide="tooltip"
				data-original-title="<?php echo $lib->html('string.escape', $photo->get('title') );?>"
				<?php if ($params->get('display_popup', true)) { ?>
				data-es-photo="<?php echo $photo->id;?>"
				<?php } ?>
			>
				<div class="embed-responsive-item" style="background-image: url('<?php echo $photo->getSource('thumbnail');?>'); background-size: cover;">
				</div>
			</a>
		</div>
		<?php } ?>
	</div>
</div>
