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
<div class="es-card es-card--mini no-hd t-lg-mt--lg t-lg-mb--lg">
	<div class="es-card__bd">
		<div class="t-d--flex sm:t-flex-direction--c sm:t-text--center">
			<div class="lg:t-pr--md">
				<?php echo $this->html('avatar.user', $user); ?>
			</div>
			<div class="t-flex-grow--1 lg:t-pr--md">
				<div class="t-d--flex sm:t-flex-direction--c">
					<div class="t-flex-grow--1">
						<div class="l-stack l-spaces--xs">
							<b><?php echo $this->html('html.user', $user, false); ?></b>

							<?php if ($this->config->get('users.layout.profiletitle')) { ?>
							<div>
								<div class="l-cluster l-spaces--xs">
									<div class="sm:t-justify-content--c">
										<div>
											<a href="<?php echo $user->getProfile()->getPermalink();?>" >
												<?php echo $user->getProfile()->getBadge();?>&nbsp; <?php echo $user->getProfile()->get('title');?>
											</a>
										</div>
									</div>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>

					<div class="t-flex-shrink--0 sm:t-mt--md">
						<div class="l-cluster t-overflow--inherit">
							<div class="sm:t-justify-content--c">

								<?php if ($this->config->get('friends.enabled')) { ?>
								<a href="<?php echo ESR::friends(['userid' => $user->getAlias()]);?>" class="btn btn-es-default btn-sm">
									<?php echo JText::sprintf(ES::string()->computeNoun('COM_ES_FRIENDS_BUTTON_META', $user->getTotalFriends()), '<b>' . $user->getTotalFriends() . '</b>'); ?>
								</a>
								<?php } ?>

								<?php if ($this->config->get('followers.enabled')) { ?>
								<a href="<?php echo ESR::followers(['userid' => $user->getAlias()]);?>" class="btn btn-es-default btn-sm">
									<?php echo JText::sprintf(ES::string()->computeNoun('COM_ES_FOLLOWERS_BUTTON_META', $user->getTotalFollowers()), '<b>' . $user->getTotalFollowers() . '</b>'); ?>
								</a>
								<?php } ?>

								<?php if (!$user->isViewer()) { ?>
									<?php echo $this->html('user.friends', $user); ?>

									<?php echo $this->html('user.subscribe', $user); ?>

									<?php if ($this->my->getPrivacy()->validate('profiles.post.message', $user->id, SOCIAL_TYPE_USER)) { ?>
										<?php echo $this->html('user.conversation', $user); ?>
									<?php } ?>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
