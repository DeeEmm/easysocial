
EasySocial.require()
    .script('admin/grid/ordering')
    .done(function($) {

        $('[data-grid-column]').implement(EasySocial.Controller.Grid.Ordering);
    });