<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2016 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<label class="es-input-smiley" data-comment-smileys>
	<i class="fa fa-smile"></i>

	<span class="es-input-smiley__popup">
		<a href="javascript:void(0);" class="es-input-smiley__close"></a>
		<ul class="es-smileys">
			<?php foreach ($icons as $icon) { ?>
			<li data-comment-smiley-item data-comment-smiley-value=":(<?php echo $icon->title;?>)">
				<?php echo $icon->getIcon(); ?>
			</li>
			<?php } ?>
		</ul>
	</span>

</label>
