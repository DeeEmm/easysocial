EasySocial.require().script('admin/maintenance/maintenance').done(function($) {
     $('[data-table-scripts]').addController('EasySocial.Controller.Maintenance.Execute');
});
