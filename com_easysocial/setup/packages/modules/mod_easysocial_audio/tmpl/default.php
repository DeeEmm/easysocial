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
<div id="es" class="mod-es mod-es-audios <?php echo $lib->getSuffix();?>">

	<div class="mod-es-list--vertical">
		<?php foreach ($audio as $item) {  ?>
		<div class="mod-es-item">
			<?php if ($item->isUpload()) { ?>
				<?php echo $item->getUploadEmbedCodes(true); ?>
			<?php } else { ?>
				<div class="es-audio-container is-<?php echo strtolower($item->getLinkProvider()); ?> <?php echo $item->isSpotifyPodcast() ? 'is-podcast' : ''; ?>">
					<?php echo $item->getLinkEmbedCodes(); ?>
				</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>

	<div class="mod-es-action">
		<a href="<?php echo ESR::audios(); ?>" class="btn btn-es-default-o btn-sm btn-block"><?php echo JText::_('MOD_ES_AUDIO_VIEW_ALL_AUDIO'); ?></a>
	</div>
</div>
