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

<?php echo $this->html('html.miniheader', $object, $active); ?>

<div class="es-container" data-es-container>
	<div class="es-streams" data-es-streams data-cluster="<?php echo $stream->isCluster() ? '1' : '0';?>" data-currentdate="<?php echo ES::date()->toSql(); ?>" data-excludeids>

		<?php echo $this->render('module', 'es-stream-before-stream'); ?>

		<ul class="es-stream-list" data-stream-list>
			<?php echo $this->includeTemplate('site/stream/default/item'); ?>
		</ul>

		<?php echo $this->render('module', 'es-stream-after-stream'); ?>
	</div>
</div>
