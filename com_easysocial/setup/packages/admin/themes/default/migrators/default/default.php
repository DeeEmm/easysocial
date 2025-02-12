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
<div class="migrators">

	<ul class="g-list-unstyled">
		<li>
			<div class="media">

				<div class="media-body">
					<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=migrators&layout=jomsocial');?>">
						<h3><?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_JOMSOCIAL_USERS' ); ?></h3>
					</a>
					<p class="migrator-info">
						<?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_JOMSOCIAL_USERS_DESC' ); ?>
					</p>
				</div>
			</div>
		</li>
		<li>
			<div class="media">

				<div class="media-body">
					<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=migrators&layout=jomsocialgroup');?>">
						<h3><?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_JOMSOCIAL_GROUPS' ); ?></h3>
					</a>
					<p class="migrator-info">
						<?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_JOMSOCIAL_GROUPS_DESC' ); ?>
					</p>
				</div>
			</div>
		</li>
		<li>
			<div class="media">

				<div class="media-body">
					<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=migrators&layout=jomsocialevent');?>">
						<h3><?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_JOMSOCIAL_EVENTS' ); ?></h3>
					</a>
					<p class="migrator-info">
						<?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_JOMSOCIAL_EVENTS_DESC' ); ?>
					</p>
				</div>
			</div>
		</li>
		<li>
			<div class="media">

				<div class="media-body">
					<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=migrators&layout=jomsocialvideo');?>">
						<h3><?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_JOMSOCIAL_VIDEOS' ); ?></h3>
					</a>
					<p class="migrator-info">
						<?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_JOMSOCIAL_VIDEOS_DESC' ); ?>
					</p>

					<div>
						<span class="label label-danger"><?php echo JText::_( 'COM_EASYSOCIAL_NOTE' );?>:</span> <?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_JOMSOCIAL_VIDEOS_NOTE' );?>
					</div>
				</div>
			</div>
		</li>
		<li>
			<div class="media">

				<div class="media-body">
					<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=migrators&layout=easyblog');?>">
						<h3><?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_EASYBLOG_STREAMS' ); ?></h3>
					</a>
					<p class="migrator-info">
						<?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_EASYBLOG_STREAMS_DESC' ); ?>
					</p>

					<div>
						<span class="label label-danger"><?php echo JText::_( 'COM_EASYSOCIAL_NOTE' );?>:</span> <?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_EASYBLOG_STREAMS_NOTE' );?>
					</div>
				</div>
			</div>
		</li>
		<li>
			<div class="media">

				<div class="media-body">
					<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=migrators&layout=kunena');?>">
						<h3><?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_KUNENA_STREAMS' ); ?></h3>
					</a>
					<p class="migrator-info">
						<?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_KUNENA_STREAMS_DESC' ); ?>
					</p>
					<div>
						<span class="label label-danger"><?php echo JText::_( 'COM_EASYSOCIAL_NOTE' );?>:</span> <?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_KUNENA_STREAMS_NOTE' );?>
					</div>
				</div>
			</div>
		</li>
		<li>
			<div class="media">

				<div class="media-body">
					<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=migrators&layout=joomla');?>">
						<h3><?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_JOOMLA_REGISTRATIONS' ); ?></h3>
					</a>
					<p class="migrator-info">
						<?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_JOOMLA_REGISTRATIONS_DESC' ); ?>
					</p>
				</div>
			</div>
		</li>
		<li>
			<div class="media">

				<div class="media-body">
					<a href="<?php echo FRoute::_('index.php?option=com_easysocial&view=migrators&layout=cb');?>">
						<h3><?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_CB' ); ?></h3>
					</a>
					<p class="migrator-info">
						<?php echo JText::_( 'COM_EASYSOCIAL_MIGRATORS_CB_DESC' ); ?>
					</p>
				</div>
			</div>
		</li>
	</ul>

</div>
