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
<div id="es">
	<ul class="o-nav o-nav--stacked feed-items" data-posttype-wrapper data-context="<?php echo $context; ?>">
		<?php if ($postTypes) { ?>
			<?php foreach ($postTypes as $postType) { ?>
			<li class="o-nav__item t-lg-mb--lg">
				<div class="o-checkbox">
					<input type="checkbox" value="<?php echo $postType->alias;?>" name="mod-post-types[]" id="mod-post-type-<?php echo $postType->alias;?>" data-es-mod-types />
					<label for="mod-post-type-<?php echo $postType->alias;?>" class="t-lg-pl--md"><?php echo JText::_($postType->title);?></label>
				</div>
			</li>
			<?php } ?>
		<?php } ?>
	</ul>
</div>
