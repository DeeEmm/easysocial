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
<div class="es-side-widget is-module">
	<?php echo $this->html('widget.title', 'APP_USER_MARKETPLACES_WIDGET_MARKETPLACE'); ?>

	<div class="es-side-widget__bd">
		<div class="o-flag-list">
			<?php foreach ($listings as $listing) { ?>
				<a href="<?php echo $listing->getPermalink();?>" class="o-flag">
					<div class="o-flag__image o-flag--top">
						<img src="<?php echo $listing->getSinglePhoto(); ?>" alt="" width="64">
					</div>
					<div class="o-flag__body">
						<div class="es-side-widget__bd-title">
							<?php echo $listing->getTitle();?>
						</div>
						<div class="es-side-widget__bd-desc">
							<b><?php echo $listing->getPriceTag(); ?></b>
						</div>
					</div>
				</a>
			 <?php } ?>
		</div>
	</div>

	<div class="es-side-widget__ft">
		<?php echo $this->html('widget.viewAll', 'COM_ES_VIEW_ALL', $viewAll); ?>
	</div>
</div>
