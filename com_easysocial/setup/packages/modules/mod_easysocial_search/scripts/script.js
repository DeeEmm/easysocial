
EasySocial
.require()
.script( 'site/search/toolbar' )
.done(function($){
	$( '[data-mod-search]' ).implement( EasySocial.Controller.Search.Toolbar );

    // disable the dropdown from closing when user click on the checkbox of the filter types
    // $('[data-nav-search-filter] .dropdown-menu input, [data-nav-search-filter] .dropdown-menu label').on('click', function (e) {
    //     e.stopPropagation();
    // });
});
