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
<div id="es" class="mod-es mod-es-hashtags <?php echo $lib->getSuffix();?>">
	<div class="o-nav o-nav--stacked">
		<?php foreach ($tags as $tag) { ?>
			<div class="o-nav__item">
				<a class="o-nav__link" href="<?php echo ESR::dashboard(array('layout' => 'hashtag' , 'tag' => $tag->title));?>">#<?php echo $tag->title; ?></a>
				<div class="t-text--muted t-fs--sm"><?php echo JText::sprintf('MOD_EASYSOCIAL_HASHTAGS_STORY_COUNT', $tag->post_count); ?></div>
			</div>
		<?php } ?>
	</div>
</div>
