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

$day	= $this->html( 'string.date', $value, 'j' );
$month	= $this->html( 'string.date', $value, 'n' );
$year	= $this->html( 'string.date', $value, 'Y' );
?>

<div id="echo empty( $id ) ? $name : $id;" name="<?php echo $name; ?>" <?php echo $attributes; ?> data-date-form>

	<input type="text" class="o-form-control" name="date-day" value="<?php echo $day; ?>" placeholder="DD" data-date-day data-date-default="<?php echo $day; ?>" />

	<select name="date-month" data-date-month data-date-default="<?php echo $month; ?>" class="o-form-control">
		<option value="1" title="<?php echo JText::_( 'JANUARY_SHORT' ); ?>"<?php echo $month == 1 ? ' selected="selected"' : '';?>>
			<?php echo JText::_( 'JANUARY' ); ?>
		</option>
		<option value="2" title="<?php echo JText::_( 'FEBRUARY_SHORT' ); ?>"<?php echo $month == 2 ? ' selected="selected"' : '';?>>
			<?php echo JText::_( 'FEBRUARY' ); ?>
		</option>
		<option value="3" title="<?php echo JText::_( 'MARCH_SHORT' ); ?>"<?php echo $month == 3 ? ' selected="selected"' : '';?>>
			<?php echo JText::_( 'MARCH' ); ?>
		</option>
		<option value="4" title="<?php echo JText::_( 'APRIL_SHORT' ); ?>"<?php echo $month == 4 ? ' selected="selected"' : '';?>>
			<?php echo JText::_( 'APRIL' ); ?>
		</option>
		<option value="5" title="<?php echo JText::_( 'MAY_SHORT' ); ?>"<?php echo $month == 5 ? ' selected="selected"' : '';?>>
			<?php echo JText::_( 'MAY' ); ?>
		</option>
		<option value="6" title="<?php echo JText::_( 'JUNE_SHORT' ); ?>"<?php echo $month == 6 ? ' selected="selected"' : '';?>>
			<?php echo JText::_( 'JUNE' ); ?>
		</option>
		<option value="7" title="<?php echo JText::_( 'JULY_SHORT' ); ?>"<?php echo $month == 7 ? ' selected="selected"' : '';?>>
			<?php echo JText::_( 'JULY' ); ?>
		</option>
		<option value="8" title="<?php echo JText::_( 'AUGUST_SHORT' ); ?>"<?php echo $month == 8 ? ' selected="selected"' : '';?>>
			<?php echo JText::_( 'AUGUST' ); ?>
		</option>
		<option value="9" title="<?php echo JText::_( 'SEPTEMBER_SHORT' ); ?>"<?php echo $month == 9 ? ' selected="selected"' : '';?>>
			<?php echo JText::_( 'SEPTEMBER' ); ?>
		</option>
		<option value="10" title="<?php echo JText::_( 'OCTOBER_SHORT' ); ?>"<?php echo $month == 10 ? ' selected="selected"' : '';?>>
			<?php echo JText::_( 'OCTOBER' ); ?>
		</option>
		<option value="11" title="<?php echo JText::_( 'NOVEMBER_SHORT' ); ?>"<?php echo $month == 11 ? ' selected="selected"' : '';?>>
			<?php echo JText::_( 'NOVEMBER' ); ?>
		</option>
		<option value="12" title="<?php echo JText::_( 'DECEMBER_SHORT' ); ?>"<?php echo $month == 12 ? ' selected="selected"' : '';?>>
			<?php echo JText::_( 'DECEMBER' ); ?>
		</option>
	</select>

	<input type="text" class="o-form-control" name="date-year" value="<?php echo $year; ?>" placeholder="YYYY" data-date-year data-date-default="<?php echo $year; ?>" />

</div>
