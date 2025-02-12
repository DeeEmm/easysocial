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
<?php if ($result) { ?>
	<?php foreach ($result as $page => $items) { ?>
	<div class="es-settings-result">
		<h3 class="es-settings-result__title"><?php echo ucwords($page);?></h3>
		<hr class="es-settings-result__divider" />

		<ul class="es-settings-result__list">
		<?php foreach ($items as $item) { ?>
			<?php $item = (object) $item; ?>
			<li>
				<a href="index.php?option=com_easysocial&view=settings&layout=form&page=<?php echo $page;?>&tab=<?php echo $item->tab;?>&goto=<?php echo $item->id;?>">
					<b><?php echo ucfirst($item->label);?></b>
				</a>
				<span><?php echo ucwords($page);?> &rarr; <?php echo ucfirst($item->tab);?></span>
			</li>
		<?php } ?>
		</ul>
	</div>
	<?php } ?>

<?php } else { ?>
<div class="es-settings-result-empty">
	No results found based on your search keyword
</div>
<?php } ?>
