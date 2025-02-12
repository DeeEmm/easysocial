EasySocial.require()
.script('admin/vendors/flot')
.done(function($) {

	var data = new Array;

	<?php for ($i = 0; $i < count($mailStats->states); $i++) { ?>
		var tmp = new Array;

		<?php for ($x = 0; $x < count($mailStats->states[$i]->items); $x++) { ?>
			tmp.push([<?php echo $x;?>, <?php echo $mailStats->states[$i]->items[$x];?>]);
		<?php } ?>

		var obj = {
			data : tmp,
			label : "<?php echo $mailStats->states[$i]->title;?>"
		}

		data.push(obj);
	<?php } ?>


	$('[data-chart-emails]')
	.plot(data, {
		lines: {
			show : true,
			fill : true,
			lineWidth : 2
		},
		legend: {
			sorted: "asc",
			noColumns: 7,
			container: $("[data-chart-emails-legend]"),
			backgroundColor: "#fff",
			backgroundOpacity: 1
		},
		xaxis: {
			ticks: [
				<?php for ($i = 0; $i < count($axes); $i++) { ?>
					[<?php echo $i; ?>, '<?php echo $axes[$i];?>']<?php echo ($i + 1) != 7 ? ',' : '';?>
				<?php } ?>
			]
		},
		points: {
			show : true,
			fill : true,
			lineWidth : 4
		},
		grid: {
			clickable: true,
			hoverable: true,
			autoHighlight: true,
			mouseActiveRadius: 10,
			aboveData: true,
			backgroundColor: "#fff",
			borderWidth: 0,
			borderColor: "#fc0",
			minBorderMargin: 25,
		},
		colors: ["rgba(178,189,199,1)", "rgba(77,175,140,1)", "rgba(228,123,121,1)"],
		shadowSize: 0
	});

	var previousPoint = null,
		previousLabel = null;

	$('[data-chart-emails]').bind('plothover', function(event , pos , item) {

		if (item) {
			if (previousPoint != item.dataIndex || previousLabel != item.series.label) {
				previousPoint = item.dataIndex;
				previousLabel = item.series.label;

				$("#tooltip").remove();

				var x = item.datapoint[0],
					y = item.datapoint[1];

				window.showTooltip(item.pageX, item.pageY, y + ' ' + '<?php echo JText::_('COM_EASYSOCIAL_EMAILS');?>');
			}
		} else {
			$('#tooltip').remove();

			previousPoint = null;
		}
	}); 

	// Show tooltip
	window.showTooltip = function(x , y , contents) {

		$('<div id="tooltip">' + contents + '</div>')
		.css({
			position: "absolute",
			display: "none",
			top: y + 5,
			left: x + 5,
			padding: "5px",
			'background-color': '#000',
			'font-size': '10',
			color: '#fff',
			opacity: 0.80
		}).appendTo('body').fadeIn(200);
	};
});