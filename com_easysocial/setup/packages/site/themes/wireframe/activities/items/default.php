<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

$active = (isset($active)) ? $active : '';
?>
<?php if ($items) { ?>
	<?php foreach ($items as $activity) { ?>
	<li class="type-<?php echo $activity->context; ?> es-stream-mini"
		data-id="<?php echo $activity->uid;?>"
		data-current-state="<?php echo $activity->isHidden; ?>"
		data-activity-item>
		<div class="es-stream <?php echo ($activity->isHidden && $active != 'hidden') ? ' isHidden' : '' ; ?>" data-activity-content>

			<div class="es-stream-control o-btn-group">
				<a class="btn-control" href="javascript:void(0);" data-es-toggle="dropdown">
					<i class="i-chevron i-chevron--down"></i>
				</a>
				<ul class="dropdown-menu dropdown-menu-right">
					<li>
						<a href="javascript:void(0);" data-toggle>
							<?php echo ($activity->isHidden) ? JText::_('COM_EASYSOCIAL_ACTIVITY_SHOW') : JText::_('COM_EASYSOCIAL_ACTIVITY_HIDE'); ?>
						</a>
					</li>
					<?php if ($this->my->id == $activity->actors[0]->id) { ?>
					<li>
						<a href="javascript:void(0);" data-delete>
							<?php echo JText::_('COM_EASYSOCIAL_ACTIVITY_DELETE'); ?>
						</a>
					</li>
					<?php } ?>
				</ul>
			</div>

			<?php echo $activity->privacy; ?>

			<div class="es-activity">

				<div class="activity-title t-lg-mb--md">
					<?php echo $activity->title; ?>
				</div>


				<?php if ($activity->content || $activity->preview) { ?>
				<div class="activity-content">
					<blockquote>
						<?php echo $activity->content; ?>
						<?php echo $activity->preview; ?>
						<?php echo $activity->getMetaHtml(); ?>
					</blockquote>
				</div>
				<?php } ?>

				<div class="activity-meta">
					<i class="far fa-clock"></i> <span><?php echo $activity->friendlyDate;?></span>
				</div>
			</div>
		</div>
	</li>
	<?php } ?>
<?php } ?>
