
EasySocial.require()
.library('ui/resizable', 'ui/draggable', 'moment')
.script('<?php echo rtrim( JURI::root() , '/' );?>/media/com_easysocial/apps/user/calendar/assets/scripts/calendar.js')
.done(function($) {

	window.updateEvent = function(event) {

		var startDate = $.moment( event.start ).format( 'YYYY-MM-DD HH:mm:ss' );
		var endDate = event.end === null ? startDate : $.moment( event.end ).format( 'YYYY-MM-DD HH:mm:ss' );

		EasySocial.ajax( 'apps/user/calendar/controllers/calendar/store', {
			"id"		: event.id,
			"all_day"	: event.allDay,
			"startVal"	: startDate,
			"endVal"	: endDate
		}).done(function() {
			return false;
		});
	};

	var date = new Date();
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();
	var options = {
					header:
					{
						left: 'prev,next,today',
						center: 'title',
						right: 'month,agendaWeek,agendaDay'
					},

					firstDay 			: <?php echo $params->get('start_week', 1);?>,
					ignoreTimezone		: true,
					selectable			: true,
					selectHelper		: true,
					editable			: true,
					isRTL				: <?php echo $isRTL ? 'true' : 'false';?>,
					monthNames: [ "<?php echo JText::_( 'JANUARY' , true );?>",
									"<?php echo JText::_( 'FEBRUARY' , true );?>",
									"<?php echo JText::_( 'MARCH' , true );?>",
									"<?php echo JText::_( 'APRIL' , true );?>",
									"<?php echo JText::_( 'MAY' , true );?>",
									"<?php echo JText::_( 'JUNE' , true );?>",
									"<?php echo JText::_( 'JULY' , true );?>",
									"<?php echo JText::_( 'AUGUST' , true );?>",
									"<?php echo JText::_( 'SEPTEMBER' , true );?>",
									"<?php echo JText::_( 'OCTOBER' , true );?>",
									"<?php echo JText::_( 'NOVEMBER' , true );?>",
									"<?php echo JText::_( 'DECEMBER' , true );?>"
								],
					monthNamesShort: [
									"<?php echo JText::_( 'JANUARY_SHORT' , true );?>",
									"<?php echo JText::_( 'FEBRUARY_SHORT' , true );?>",
									"<?php echo JText::_( 'MARCH_SHORT' , true );?>",
									"<?php echo JText::_( 'APRIL_SHORT' , true );?>",
									"<?php echo JText::_( 'MAY_SHORT' , true );?>",
									"<?php echo JText::_( 'JUNE_SHORT' , true );?>",
									"<?php echo JText::_( 'JULY_SHORT' , true );?>",
									"<?php echo JText::_( 'AUGUST_SHORT' , true );?>",
									"<?php echo JText::_( 'SEPTEMBER_SHORT' , true );?>",
									"<?php echo JText::_( 'OCTOBER_SHORT' , true );?>",
									"<?php echo JText::_( 'NOVEMBER_SHORT' , true );?>",
									"<?php echo JText::_( 'DECEMBER_SHORT' , true );?>"
								],

					dayNames: [
									"<?php echo JText::_( 'SUNDAY' , true );?>",
									"<?php echo JText::_( 'MONDAY' , true );?>",
									"<?php echo JText::_( 'TUESDAY' , true );?>",
									"<?php echo JText::_( 'WEDNESDAY' , true );?>",
									"<?php echo JText::_( 'THURSDAY' , true );?>",
									"<?php echo JText::_( 'FRIDAY' , true );?>",
									"<?php echo JText::_( 'SATURDAY' , true );?>"
								],
					dayNamesShort: [
									"<?php echo JText::_( 'SUN' , true );?>",
									"<?php echo JText::_( 'MON' , true );?>",
									"<?php echo JText::_( 'TUE' , true );?>",
									"<?php echo JText::_( 'WED' , true );?>",
									"<?php echo JText::_( 'THU' , true );?>",
									"<?php echo JText::_( 'FRI' , true );?>",
									"<?php echo JText::_( 'SAT' , true );?>"
								],

					buttonText: {
						today: "<?php echo JText::_( 'APP_CALENDAR_TODAY' , true );?>",
						month: "<?php echo JText::_( 'APP_CALENDAR_MONTH' , true );?>",
						week: "<?php echo JText::_( 'APP_CALENDAR_WEEK' , true );?>",
						day: "<?php echo JText::_( 'APP_CALENDAR_DAY' , true );?>"
					},

					allDayText:"<?php echo JText::_( 'APP_CALENDAR_ALLDAY' , true );?>",
					axisFormat: "<?php echo $params->get('agenda_timeformat', '12') == '24' ? 'HH:mm' : 'h(:mm)tt';?>",
					timeFormat: {
						agenda: "<?php echo $params->get('agenda_timeformat', '12') == '24' ? 'HH:mm{ - HH:mm}' : 'h:mm{ - h:mm}';?>",
						'': "<?php echo $params->get('agenda_timeformat', '12') == '24' ? 'HH:mm{ - HH:mm}' : 'h:mmtt{ - h:mmtt}';?>"
					},

					select				: function(start, end, allDay)
					{


						start 	= $.moment( start ).format( 'YYYY-MM-DD HH:mm:ss' );
						end 	= $.moment( end ).format( 'YYYY-MM-DD HH:mm:ss' );

						<?php if( $user->isViewer() ){ ?>
						EasySocial.dialog(
						{
							content : EasySocial.ajax( 'apps/user/calendar/controllers/calendar/form' , {
									"start"		: start,
									"end"		: end,
									"allday"	: allDay === false ? "0" : "1"
							}),
							bindings:
							{
								"{createButton} click" : function()
								{
									// Close the dialog
									EasySocial.dialog().close();

									var title 		= this.title().val(),
										desc 		= this.description().val(),
										startVal 	= this.start().val(),
										endVal 		= this.end().val(),
										reminder 	= this.reminder().val(),
										stream 		= this.stream().val(),
										startValJs	= startVal.split(/[- :]/),
										endValJs	= endVal.split(/[- :]/),
										allDay		= this.allDay().val();


									var start 		= new Date( startValJs[0] , startValJs[1] - 1, startValJs[2] , startValJs[3] , startValJs[4] , startValJs[5]),
										end 		= new Date( endValJs[0] , endValJs[1] - 1, endValJs[2] , endValJs[3] , endValJs[4] , endValJs[5] );

									// Update the start and end value so that it doesn't send double timezones.
									start 	= $.moment( start ).format( 'YYYY-MM-DD HH:mm:ss' );
									end 	= $.moment( end ).format( 'YYYY-MM-DD HH:mm:ss' );

									var myDate	= new Date(),
										offset	= (myDate.getTimezoneOffset() / 60),
										offset 	= offset < 0 ? Math.abs( offset ) : -Math.abs( offset );

									// Save the calendar
									EasySocial.ajax( 'apps/user/calendar/controllers/calendar/store' ,
									{
										"title" 		: title,
										"description"	: desc,
										"startVal"		: startVal,
										"endVal"		: endVal,
										"reminder"		: reminder,
										"stream"		: stream,
										"all_day"		: allDay
									})
									.done(function( eventId )
									{
										var eventObj 	= {
																"id"	: eventId,
																"title"	: title,
																"start"	: start,
																"end"	: end,
																"allDay": allDay == "0" ? false : true
															};

										// Update the calendar
										$( '[data-apps-calendar]' ).fullCalendar( 'renderEvent' , eventObj );
									});
								}
							}
						});

						<?php } ?>
					},
					eventDrop: function( event , dayDelta , minuteDelta , allDay , revertFunc )
					{
						window.updateEvent( event );
					},
					eventResize: function( event , dayDelta , minuteDelta , revertFunc )
					{
						window.updateEvent( event );
					},
					viewDisplay: function(event, element, view) {

						// remove the span 1st.
						$('#fd.es .fc-header .fc-header-title>span').remove();

						// Append a description below the title
						var title = $('#fd.es .fc-header .fc-header-title').html();

						//$('#fd.es .fc-header .fc-header-title').append('<span><?php echo JText::_('COM_EASYSOCIAL_USER_APP_CALENDAR_TIPS');?></span>');
						$('#fd.es .fc-header .fc-header-title').html(title + '<span><?php echo JText::_('COM_EASYSOCIAL_USER_APP_CALENDAR_TIPS');?></span>');
					},
					eventClick: function( event )
					{

						EasySocial.dialog(
						{
							content 	: EasySocial.ajax( 'apps/user/calendar/controllers/calendar/view' , { id : event.id }),
							bindings:
							{
								"{deleteButton} click" : function()
								{
									EasySocial.dialog(
									{
										content 	: EasySocial.ajax( 'apps/user/calendar/controllers/calendar/confirmDelete' ,
										{
											id 	: event.id
										}),
										bindings	:
										{
											"{deleteButton} click" : function()
											{
												EasySocial.ajax( 'apps/user/calendar/controllers/calendar/delete' ,
												{
													id 	: event.id
												}).done(function()
												{
													EasySocial.dialog().close();

													$( '[data-apps-calendar]' ).fullCalendar( 'removeEvents' , [ event._id ] );
												});
											}
										}
									})
								},
								"{editButton} click" : function()
								{
									EasySocial.dialog(
									{
										content : EasySocial.ajax( 'apps/user/calendar/controllers/calendar/form' , {
											"id"	: event.id
										}),
										bindings:
										{
											"{updateButton} click" : function()
											{
												// Close the dialog
												EasySocial.dialog().close();

												// Remove the previous item
												var title 		= this.title().val(),
													desc 		= this.description().val(),
													startVal 	= this.start().val(),
													endVal 		= this.end().val(),
													reminder 	= this.reminder().val(),
													stream 		= this.stream().val(),
													startValJs	= startVal.split(/[- :]/),
													endValJs	= endVal.split(/[- :]/),
													allDay		= this.allDay().val(),
													id 			= this.id().val();

												var start 		= new Date( startValJs[0] , startValJs[1] - 1, startValJs[2] , startValJs[3] , startValJs[4] , startValJs[5]),
													end 		= new Date( endValJs[0] , endValJs[1] - 1, endValJs[2] , endValJs[3] , endValJs[4] , endValJs[5] );

												// Update the start and end value so that it doesn't send double timezones.
												start 	= $.moment( start ).format( 'YYYY-MM-DD HH:mm:ss' );
												end 	= $.moment( end ).format( 'YYYY-MM-DD HH:mm:ss' );

												var myDate	= new Date(),
													offset	= (myDate.getTimezoneOffset() / 60),
													offset 	= offset < 0 ? Math.abs( offset ) : -Math.abs( offset );

												// Save the calendar
												EasySocial.ajax( 'apps/user/calendar/controllers/calendar/store' ,
												{
													"id"			: id,
													"title" 		: title,
													"description"	: desc,
													"startVal"		: startVal,
													"endVal"		: endVal,
													"reminder"		: reminder,
													"stream"		: stream,
													"all_day"		: allDay
												})
												.done(function( eventId )
												{
													// calendar.fullCalendar( 'removeEvents' , [ event._id ] );

													event.title 	= title;
													event.start 	= start;
													event.end 		= end;
													event.allDay	= allDay == "0" ? false : true;

													// Update the calendar
													$( '[data-apps-calendar]' ).fullCalendar( 'updateEvent' , event );
												});
											}
										}
									});
								}
							}
						});
					},
					events:
					[
					<?php if( $schedules ){ ?>
						<?php $i = 1; ?>
						<?php foreach( $schedules as $schedule ){ ?>
						{
							"id"		: "<?php echo $schedule->id;?>",
							"title"		: "<?php echo addslashes($schedule->title);?>",
							"start"		: new Date('<?php echo $schedule->getStartDate()->format('Y');?>','<?php echo $schedule->getStartDate()->format('m') - 1;?>','<?php echo $schedule->getStartDate()->format('d');?>' , '<?php echo $schedule->getStartDate()->format('H');?>' ,'<?php echo $schedule->getStartDate()->format('i');?>'),
							"end"		: new Date('<?php echo $schedule->getEndDate()->format('Y');?>','<?php echo $schedule->getEndDate()->format('m') - 1;?>','<?php echo $schedule->getEndDate()->format('d');?>' , '<?php echo $schedule->getEndDate()->format('H');?>' ,'<?php echo $schedule->getEndDate()->format('i');?>'),
							"allDay"	: <?php echo $schedule->all_day ? 'true' : 'false';?>
						}<?php if( $i != count( $schedules ) ){ ?>,<?php } ?>

						<?php $i++;?>
						<?php } ?>
					<?php } ?>
					]
				};

	var calendar = $('[data-apps-calendar]').fullCalendar( options );

	<?php if( isset( $calendar ) && $calendar->id ){ ?>
		calendar.fullCalendar( 'gotoDate' , '<?php echo $calendar->getStartDate()->format( 'Y' );?>'  , '<?php echo $calendar->getStartDate()->format( 'n' ) - 1;?>' , '<?php echo $calendar->getStartDate()->format( 'j' );?>' );
		calendar.fullCalendar( 'changeView' , 'agendaDay' );
	<?php } ?>
});
