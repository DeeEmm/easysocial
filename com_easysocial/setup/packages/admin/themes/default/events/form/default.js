EasySocial.require()
.script('admin/users/form', 'shared/fields/validate', 'admin/events/users')
.done(function($) {

    var form = $('[data-events-form]');

    form.implement('EasySocial.Controller.Users.Form', {
        mode: 'adminedit'
    });

    <?php if (!$isNew) { ?>

    form.find('[data-tabnav]').click(function(event) {
        var name = $(this).data('for');

        form.find('[data-active-tab]').val(name);
    });

    $('[data-members-dropdown]').addController('EasySocial.Controller.Events.Users', {
        eventid: <?php echo $event ? $event->id : 0; ?>,
        "error": {
            "empty": "<?php echo JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST', true);?>"
        }
    });

    <?php } ?>

    $.Joomla('submitbutton', function(task) {
        if (task === 'cancel') {
            window.location = "<?php echo FRoute::url(array('view' => 'events')); ?>";

            return false;
        }

        var dfd = [];

        dfd.push(form.validate());

        $.when.apply(null, dfd)
            .done(function() {
                <?php if ($isNew || !$event->hasRecurringEvents()) { ?>

                $.Joomla('submitform', [task]);

                <?php } else { ?>

                EasySocial.dialog({
                    content: EasySocial.ajax('admin/views/events/applyRecurringDialog'),
                    bindings: {
                        '{applyThisButton} click': function() {
                            $('input[name="applyRecurring"]').val(0);
                            $.Joomla('submitform', [task]);
                        },

                        '{applyAllButton} click': function() {
                            $('input[name="applyRecurring"]').val(1);
                            $.Joomla('submitform', [task]);
                        },

                        '{cancelButton} click': function() {
                            EasySocial.dialog().close();
                        }
                    }
                });

                <?php } ?>
            })
            .fail(function() {
                EasySocial.dialog({
                    content: EasySocial.ajax('admin/views/users/showFormError')
                });
            });
    });

    // Insert a new dropdown button on the toolbar
    $('[data-members-dropdown]')
        .removeClass('t-hidden')
        .appendTo('#toolbar');
});
