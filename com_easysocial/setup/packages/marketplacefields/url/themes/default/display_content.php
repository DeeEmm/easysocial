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
<?php if ($params->get('linkable')) { ?>
<a href="<?php echo $value; ?>"
	<?php echo $params->get( 'nofollow' ) ? ' rel="nofollow"' : '';?>
	<?php echo $params->get( 'target' ) == '_blank' ? ' target="_blank"' : '';?>
	><?php echo $value;?></a>
<?php } else { ?>
	<?php echo $value;?>
<?php } ?>