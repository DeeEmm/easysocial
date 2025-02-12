<?php
/**
* @package      EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>

<?php if ($photos) { ?>
	<div class="es-side-embed-lists">
	<?php foreach ($photos as $photo) { ?>
		<?php if ($photo) { ?>
		<div class="es-side-embed-lists__item">
			<a href="<?php echo $photo->getPermalink();?>"
				class="embed-responsive embed-responsive-4by3"
					data-es-provide="tooltip"
					data-original-title="<?php echo $this->html('string.escape', $photo->_('title'));?>"
					data-placement="bottom"
					data-es-photo="<?php echo $photo->id; ?>"
			>
				<div class="embed-responsive-item" style="background-size: cover; background-image: url('<?php echo $photo->getSource('thumbnail');?>');"> </div>
			</a>
		</div>
		<?php } ?>
	<?php } ?>
	</div>
<?php } else { ?>
<div class="t-text--muted">
	<?php echo $emptyMessage; ?>
</div>
<?php } ?>
