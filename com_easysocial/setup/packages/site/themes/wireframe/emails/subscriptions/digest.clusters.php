<?php if ($clusters) { ?>
    <?php foreach ($clusters as $cluster) { ?>
        <?php if ($cluster->posts) { ?>

            <table role="presentation" aria-hidden="true" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px;">
                <tr>
                    <td height="20" style="line-height:20px; font-size:20px;"> </td><!-- Spacer -->
                </tr>
                <tr>
                    <td class="mobile" style="font-family:arial, sans-serif; font-size:18px; line-height:32px; font-weight:bold;">
                        <?php echo $cluster->title; ?>
                    </td>
                </tr>
                <tr>
                    <td class="mobile" style="font-family:arial, sans-serif; font-size:14px; line-height:26px;color:#999">
                        <?php echo JText::sprintf('COM_ES_DIGEST_NEW_UPDATES_POSTED_IN_CLUSTER', $cluster->title); ?>
                    </td>
                </tr>
                <tr>
                    <td height="20" style="line-height:20px; font-size:20px;"> </td><!-- Spacer -->
                </tr>

                <?php foreach ($cluster->posts as $type => $posts) { ?>
                    <tr>
                        <td class="mobile" style="font-family:arial, sans-serif; font-size:16px; line-height:26px;color:#888">
                            <?php echo JText::_('COM_ES_DIGEST_EMAIL_SECTION_' . $type); ?>
                        </td>
                    </tr>
                    <?php foreach ($posts as $post) { ?>
                        <!-- Start Link -->
                        <tr>
                            <td style="font-family:Verdana, Arial, sans serif; font-size: 14px; color: #4d4d4d; line-height:16px;">
                                <a href="<?php echo $post->link; ?>" target="_blank" alias="" style="color: #458BC6; text-decoration: none;">

                                    <?php echo $post->title; ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td height="15" style="line-height:10px; font-size:10px;"> </td><!-- Spacer -->
                        </tr>
                        <!-- End Link -->
                    <?php } ?>

                    <tr>
                        <td height="10" style="line-height:10px; font-size:10px;"> </td><!-- Spacer -->
                    </tr>
                <?php } ?>

                <tr>
                    <td height="20" style="font-family:Verdana, Arial, sans serif; font-size: 12px; color: #4d4d4d; line-height:16px;">
                        <?php echo JText::sprintf( 'COM_ES_DIGEST_CLUSTER_SUBSCRIPTION_STATEMENT', '<a href="'.$cluster->link.'" target="_blank" alias="" style="color: #458BC6; text-decoration: none;">' . $cluster->title . '</a>'); ?>
                    </td>
                </tr>

                <tr>
                    <td height="20" style="line-height:20px; font-size:20px;"> </td><!-- Spacer -->
                </tr>
            </table>

            <!-- Start Divider Decor -->
            <table role="presentation" aria-hidden="true" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px;" class="container" bgcolor="#eee">
                <tr>
                    <td>
                        <img style="min-width:320px; display:block; margin:0; padding:0" class="mobileOff" width="320" height="1" src="<?php echo rtrim(JURI::root(), '/'); ?>/media/com_easysocial/images/spacer.gif"/>
                    </td>
                </tr>
            </table>
            <!-- End Divider Decor -->
            
        <?php } ?>
    <?php } ?>
<?php } ?>
