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
<div class="wrapper-for-full-height">
	<?php if (!$browseView) { ?>
		<?php echo $adapter->getMiniHeader(); ?>
	<?php } ?>

	<div class="es-container es-audios" data-audios-listing data-es-container>

		<?php echo $this->html('html.sidebar'); ?>

		<?php if ($this->isMobile()) { ?>
			<?php echo $this->includeTemplate('site/audios/default/mobile.filters'); ?>
		<?php } ?>

		<div class="es-content">

			<?php echo $this->render('module', 'es-audios-before-contents'); ?>

			<div data-wrapper>
				<?php echo $this->html('listing.loader', 'card', 4, 2, array('snackbar' => true, 'sortbar' => true)); ?>

				<div data-audios-result>
					<div>
						<?php if ($activePlaylist) { ?>
							<?php echo $playlistOutput; ?>
						<?php } else { ?>
							<?php echo $this->includeTemplate('site/audios/default/items'); ?>
						<?php } ?>
					</div>
				</div>
			</div>

			<?php echo $this->render('module' , 'es-audios-after-contents'); ?>
		</div>
	</div>
</div>
