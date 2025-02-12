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
<div id="es" class="mod-es mod-es-sidebar-friends <?php echo $this->lib->getSuffix();?>" data-es-followers-filters>
	<div class="es-sidebar" data-sidebar>

		<?php echo $this->lib->render('module', 'es-followers-sidebar-top'); ?>

		<div class="es-side-widget">
			<?php echo $this->lib->html('widget.title', 'COM_EASYSOCIAL_FOLLOWERS_FOLLOWERS_TITLE'); ?>

			<div class="es-side-widget__bd">
				<ul class="o-tabs o-tabs--stacked">
					<li class="o-tabs__item has-notice <?php echo $filter == 'followers' ? ' active' : '';?>" data-filter-item data-type="followers">
						<a href="<?php echo $filters->followers->link;?>" class="o-tabs__link" title="<?php echo $filters->followers->page_title;?>">
							<?php echo $filters->followers->label;?>
							<span class="o-tabs__bubble" data-followers-count><?php echo $counter->followers;?></span>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>

					<li class="o-tabs__item has-notice <?php echo $filter == 'following' ? ' active' : '';?>" data-filter-item data-type="following">
						<a href="<?php echo $filters->following->link;?>" class="o-tabs__link <?php echo $filter == 'following' ? ' active' : '';?>" title="<?php echo $filters->following->page_title;?>">
							<?php echo $filters->following->label;?>
							<span class="o-tabs__bubble" data-following-count><?php echo $counter->following;?></span>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>

					<?php if ($user->isViewer()) { ?>
					<li class="o-tabs__item has-notice <?php echo $filter == 'suggest' ? ' active' : '';?>" data-filter-item data-type="suggest">
						<a href="<?php echo $filters->suggestion->link;?>" class="o-tabs__link <?php echo $filter == 'suggest' ? ' active' : '';?>" title="<?php echo $filters->suggestion->page_title;?>">
							<?php echo $filters->suggestion->label;?>
							<span class="o-tabs__bubble" data-suggest-count><?php echo $counter->suggestion;?></span>
						</a>
						<div class="o-loader o-loader--sm"></div>
					</li>
					<?php } ?>
				</ul>
			</div>
		</div>

		<?php echo $this->lib->render('module', 'es-followers-sidebar-bottom'); ?>
	</div>
</div>

<script>
EasySocial
.require()
.script('site/followers/filter')
.done(function($){
	$('body').addController(EasySocial.Controller.Followers.Filter);
});
</script>
