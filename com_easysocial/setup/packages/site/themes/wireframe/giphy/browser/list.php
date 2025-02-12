<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<?php if ($giphies) { ?>
	<?php foreach ($giphies as $giphy) { ?>
		<li>
			<a href="javascript:void(0);" class="es-gif-holder"
				data-giphy-item
				data-original="<?php echo $giphy->images->original->url; ?>"
				style="background-image: url('<?php echo $giphy->images->fixed_width->url; ?>');">
			</a>
		</li>
	<?php } ?>
<?php } ?>