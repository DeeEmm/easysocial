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
<li class="es-stream-item es-stream-full" data-hidden="0" data-stream-ads-item data-id="<?php echo $advertisement->id; ?>" data-link="<?php echo $advertisement->getLink(); ?>"
>
	<script>
	EasySocial.require()
	.script('site/stream/ads')
	.done(function($) {
		$('[data-stream-ads-item]').implement("EasySocial.Controller.Ads");
	});
	</script>
	<div class="es-stream" data-wrapper>
		<div class="es-stream-meta">
			<div class="o-flag">
				<div class="o-flag__image o-flag--top es-stream-avatar-wrap">
					<div class="es-stream-avatar">
						<?php echo $this->html('avatar.mini', $advertiser->name, '', $advertiser->getLogo(), '', '', '', false); ?>
					</div>
				</div>

				<div class="o-flag__body">
					<div class="es-stream-title">
						<?php echo $advertiser->name; ?>
						<span class="o-label o-label--danger"><?php echo JText::_('COM_ES_ADS_LABEL'); ?></span>
					</div>

					<div class="es-stream-meta-footer t-text--muted">
						<a href="<?php echo ESR::ads(); ?>">
							<?php echo JText::_('COM_ES_ADVERTISEMENT_LABEL'); ?>
						</a>
					</div>
				</div>
			</div>
		</div>

		<div class="es-stream-content es-story--bg-0" data-contents>
			<?php echo $advertisement->intro; ?>
		</div>

		<div class="es-stream-preview" data-preview>
			<div class="es-stream-embed is-ads">
					<a href="<?php echo $advertisement->getLink(); ?>" class="es-stream-embed__cover" data-ads-link>
						<div class="es-stream-embed__cover-img" style="background-image: url('<?php echo $advertisement->getCover(); ?>');"></div>
					</a>
					<div class="o-grid o-grid--center es-stream-embed--border">
						<div class="o-grid__cell">
							<a href="<?php echo $advertisement->getLink(); ?>" class="es-stream-embed__title" data-ads-link>
								<?php echo $advertisement->title; ?>
							</a>
							<div class="es-stream-embed__meta">
								<?php echo $advertisement->getLink(false); ?>
							</div>
							<div class="es-stream-embed__desc t-text--muted">
								<?php echo $advertisement->content; ?>
							</div>
						</div>
						<?php if ($advertisement->hasButton()) { ?>
							<div class="o-grid__cell o-grid__cell--auto-size">
								<div class="es-stream-embed__action">
									<a href="<?php echo $advertisement->getLink(); ?>" class="btn btn-es-default-o" data-ads-link><?php echo $advertisement->getButtonText(); ?></a>
								</div>
							</div>
						<?php } ?>
					</div>


				</div>
		</div>

	</div>
</li>
