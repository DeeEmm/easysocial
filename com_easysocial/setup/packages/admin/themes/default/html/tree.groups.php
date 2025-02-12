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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );
?>
<div class="es-tree">
<?php for( $i = 0, $n = count( $groups); $i < $n; $i++ ){ ?>
	<?php
		$item 	=& $groups[ $i ];

		if ((!$checkSuperAdmin) || $isSuperAdmin || (!JAccess::checkGroup($item->id, 'core.admin')))
		{
			// Setup  the variable attributes.
			$eid = $count . 'group_' . $item->id;

			// Don't call in_array unless something is selected
			$checked = '';

			if( $selected )
			{
				$checked = in_array($item->id, $selected) ? ' checked="checked"' : '';
			}

			$rel = ($item->parent_id > 0) ? ' rel="' . $count . 'group_' . $item->parent_id . '"' : '';
	?>
	<div class="tree-control">
		<label for="<?php echo $eid;?>" class="checkbox">
			<input type="checkbox" id="<?php echo $eid;?>" value="<?php echo $item->id;?>" name="<?php echo $name;?>[]"<?php echo $checked;?><?php echo $rel;?> />
			<div class="tree-title">
				<?php echo str_repeat( '<span class="gi"></span>' , $item->level );?> <b><?php echo $item->title;?></b>
			</div>
		</label>
	</div>
	<?php
		}
	?>
<?php } ?>
</div>
