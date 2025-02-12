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
<?php echo $this->html('cover.' . $object->getType(), $object, 'apps'); ?>

<?php echo $this->html('html.snackbar', $title, 'h1'); ?>

<div class="es-app-canvas type-<?php echo $app->group;?>">
	<?php echo $contents; ?>
</div>
