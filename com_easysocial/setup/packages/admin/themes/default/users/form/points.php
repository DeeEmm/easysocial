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
<div class="row">

	<div class="col-lg-7">
		<div class="panel">
			<div class="panel-head">
				<b><?php echo JText::_( 'COM_EASYSOCIAL_USERS_POINTS_ACHIEVEMENT_HISTORY' ); ?></b>
				<p>
					<?php echo JText::_( 'COM_EASYSOCIAL_USERS_POINTS_ACHIEVEMENT_HISTORY_DESC' ); ?>
				</p>
			</div>

			<div class="panel-body">
				<table class="table table-striped">
					<thead>
						<td class="center" width="5%">
							<?php echo JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_ID' ); ?>
						</td>
						<td>
							<?php echo JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_ACHIEVEMENT' ); ?>
						</td>
						<td>
							<?php echo JText::_( 'COM_EASYSOCIAL_TABLE_COLUMN_REASON' ); ?>
						</td>
					</thead>
					<tbody>
						<?php if( $pointsHistory ){ ?>
							<?php foreach( $pointsHistory as $history ){ ?>
							<tr>
								<td class="center">
									<?php echo $history->id;?>
								</td>
								<td>
									<div>
									<?php if( $history->points > 0 ){ ?>
										<?php echo ucfirst( JText::_( 'COM_EASYSOCIAL_POINTS_EARNED' ) );?>
									<?php } else { ?>
										<?php echo ucfirst( JText::_( 'COM_EASYSOCIAL_POINTS_LOST' ) );?>
									<?php } ?>

									<?php echo $history->points; ?> <?php echo JText::_( 'COM_EASYSOCIAL_POINTS_POINTS' );?>
									</div>

									<div class="points-date t-fs--sm">
										<em><?php echo $this->html( 'string.date' , $history->created );?></em>
									</div>
								</td>
								<td>
									<?php if( $history->message ){ ?>
										<?php echo JText::_($history->message); ?>
									<?php } else { ?>
										<?php echo JText::_($history->points_title); ?>
									<?php } ?>
								</td>
							</tr>
							<?php } ?>
						<?php } else { ?>
							<tr class="is-empty">
								<td class="empty" colspan="3">
									<?php echo JText::_( 'COM_EASYSOCIAL_USERS_DID_NOT_EARN_POINTS_YET' ); ?>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="col-lg-5">
		<div class="panel">
			<?php echo $this->html('panel.heading', 'COM_EASYSOCIAL_USERS_POINTS_CURRENT'); ?>

			<div class="panel-body">
				<div class="form-group">
					<label class="col-md-3">
						<?php echo JText::_('COM_EASYSOCIAL_USERS_POINTS_CURRENT_POINTS');?>
					</label>
					<div class="col-md-9">
						<input type="text" class="text-center input-mini o-form-control input-sm" name="points" style="width: 80px;" value="<?php echo $user->getPoints();?>" /> <?php echo JText::_('COM_EASYSOCIAL_POINTS');?>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>
