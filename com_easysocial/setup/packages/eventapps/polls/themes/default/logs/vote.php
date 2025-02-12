<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<?php if (!$target) { ?>
<?php echo JText::sprintf('APP_USER_LINKS_ACTIVITY_LOG_YOU_SHARED_A_LINK', '<a href="' . $assets->get('link') . '">' . $assets->get('link') . '</a>' );?>
<?php } else { ?>
	<?php echo $this->html('html.user', $actor->id); ?> <i class="ies-arrow-right"></i> <?php echo $this->html('html.user', $target->id);?>
<?php } ?>

<div class="es-stream-preview">
	<div class="mt-10">
		<div class="stream-preview-title">
			<a target="_blank" href="<?php echo $assets->get( 'link' );?>"><i class="ies-link"></i> <?php echo $assets->get( 'title' ); ?></a>
			<div class="t-fs--sm t-lg-ml--xl">
				<a target="_blank" href="<?php echo $assets->get( 'link' );?>"><?php echo $assets->get( 'link' ); ?></a>
			</div>
		</div>

		<p class="mt-5">
			<?php if ($assets->get('image')) { ?>
			<img align="left" style="padding: 0 10px 10px 0; max-width: 150px;" src="<?php echo $assets->get( 'image' );?>">
			<?php } ?> <?php echo $assets->get( 'content' ); ?>
		</p>
	</div>
</div>

