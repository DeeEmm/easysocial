<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2019 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');
?>
<div class="es-container" data-es-container data-es-reviews data-id="<?php echo $cluster->id; ?>" data-type="<?php echo $cluster->getType(); ?>">

	<?php echo $this->html('html.sidebar'); ?>

	<?php if ($this->isMobile()) { ?>
		<?php echo $this->output('site/reviews/default/mobile.filters'); ?>
	<?php } ?>

	<div class="es-content">
		<div class="es-reviews app-contents<?php echo !$items ? ' is-empty' : '';?>" data-reviews-wrapper>
			<?php echo $this->html('html.loading'); ?>
			<?php echo $this->html('html.emptyBlock', 'APP_REVIEWS_EMPTY', 'fa-database'); ?>

			<div data-reviews-contents>
				<?php foreach ($items as $review) { ?>
					<?php echo $this->loadTemplate('site/reviews/default/items', array('review' => $review, 'params' => $params, 'cluster' => $cluster, 'isAdmin' => $isAdmin)); ?>
				<?php } ?>

				<?php echo $pagination->getListFooter('site'); ?>
			</div>
		</div>
	</div>
</div>
