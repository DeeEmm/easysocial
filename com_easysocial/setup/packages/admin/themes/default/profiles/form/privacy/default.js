<?php defined( '_JEXEC' ) or die( 'Unauthorized Access' ); ?>

EasySocial.require()
.script( 'admin/users/privacy' )
.done(function($){

	$( '[data-edit-privacy]' ).implement(
		'EasySocial.Controller.Profile.Privacy',{});
});
