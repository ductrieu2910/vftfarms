<?php


	
	?>
	<div class="row  padding-small">
		<div class="col m12">
		<div id="calendar"></div>
		</div>
	</div>
	
	<?php
		$defaultView = "month";
	?>	
<script>
	
	$(document).ready(function() {
	 
		var lastView = '<?php echo $defaultView;?>';
		$('#calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,basicWeek,basicDay,agendaWeek'
			},
			monthNames: ["<?php echo $appSession->getLang()->find("January");?>", "<?php echo $appSession->getLang()->find("February");?>", "<?php echo $appSession->getLang()->find("March");?>", "<?php echo $appSession->getLang()->find("April");?>", "<?php echo $appSession->getLang()->find("May");?>", "<?php echo $appSession->getLang()->find("June");?>", "<?php echo $appSession->getLang()->find("July");?>", "<?php echo $appSession->getLang()->find("August");?>", "<?php echo $appSession->getLang()->find("September");?>", "<?php echo $appSession->getLang()->find("October");?>", "<?php echo $appSession->getLang()->find("November");?>", "<?php echo $appSession->getLang()->find("December");?>"],
			monthNamesShort: ["<?php echo $appSession->getLang()->find("Jan");?>", "<?php echo $appSession->getLang()->find("Feb");?>", "<?php echo $appSession->getLang()->find("Mar");?>", "<?php echo $appSession->getLang()->find("Apr");?>", "<?php echo $appSession->getLang()->find("May");?>", "<?php echo $appSession->getLang()->find("Jun");?>", "<?php echo $appSession->getLang()->find("Jul");?>", "<?php echo $appSession->getLang()->find("Aug");?>", "<?php echo $appSession->getLang()->find("Sep");?>", "<?php echo $appSession->getLang()->find("Oct");?>", "<?php echo $appSession->getLang()->find("Nov");?>", "<?php echo $appSession->getLang()->find("Dec");?>"],
			dayNames: ["<?php echo $appSession->getLang()->find("Sunday");?>", "<?php echo $appSession->getLang()->find("Monday");?>", "<?php echo $appSession->getLang()->find("Tuesday");?>", "<?php echo $appSession->getLang()->find("Wednesday");?>", "<?php echo $appSession->getLang()->find("Thurday");?>", "<?php echo $appSession->getLang()->find("Friday");?>", "<?php echo $appSession->getLang()->find("Saturday");?>"],
			dayNamesShort: ["<?php echo $appSession->getLang()->find("Sun");?>", "<?php echo $appSession->getLang()->find("Mon");?>", "<?php echo $appSession->getLang()->find("Tue");?>", "<?php echo $appSession->getLang()->find("Wed");?>", "<?php echo $appSession->getLang()->find("Thu");?>", "<?php echo $appSession->getLang()->find("Fri");?>", "<?php echo $appSession->getLang()->find("Sat");?>"],
			buttonText:{month:"<?php echo $appSession->getLang()->find("Month");?>",week:"<?php echo $appSession->getLang()->find("Week");?>",day:"<?php echo $appSession->getLang()->find("Day");?>",list:"<?php echo $appSession->getLang()->find("ListCal");?>"},
			allDayText:"<?php echo $appSession->getLang()->find("AllDay");?>",
			currentText:"<?php echo $appSession->getLang()->find("Today");?>",
			
			defaultDate:  moment().format(),
			navLinks: true, 
			resizable: true,
			editable: true,
			droppable: true,
			selectable: true,
			defaultView: '<?php echo $defaultView;?>',
			drop: function( date ) 
			{ 
				
				
				
				//addTask(this.id, this.innerHTML, new Date(date), new Date(date));
			},
			eventDrop: function(event, jsEvent, ui, view) {
				var fdate = dateToDateString(new Date(event.start)) + ' 00:00:00';
				var tdate = dateToDateString(new Date(event.start)) + ' 23:59:59';
				
				var _url = "<?php echo URL;?>admin/module/action/?ac=table_calendar_update&id=" + event.id;
				_url = _url + "&table_name=" + event.name;
				_url = _url + "&start_date=" + event.start_name;
				_url = _url + "&end_date=" + event.end_name;
				_url = _url + "&fdate=" + fdate;
				_url = _url + "&tdate=" + tdate ;	
				loadPage('contentView', _url, function(status, message)
				{
					if(status == 0)
					{
						if(message != "OK")
						{
							alert(message);
						}
					}
					
				}, true);
				
			},
			viewDisplay: function(view) {
				if (lastView != view.name){ 
					
					lastView = view.name;
					$('#calendar').fullCalendar( 'removeEvents');
					$('#calendar').fullCalendar( 'refetchEvents' );
				}
				
			},
			eventRender: function(event, element) {
				element.append( "<span class='closeon'>X</span>" );
				element.find(".closeon").click(function() {
					deleteEvent(event._id);
				   
				});
			},	
			events: function(start, end, timezone, callback) 
			{
				var fdate = new Date(start);
				var tdate = new Date(end);
				var _url = "<?php echo URL;?>admin/module/action/?ac=table_calendar_events&module_id=<?php echo $module_id;?>&$root_module_id=<?php echo $root_module_id;?>&module_model_id=<?php echo $module_model_id;?>";
				_url = _url + "&fdate=" + fdate.getFullYear() + "-" + (fdate.getMonth() + 1) + "-" + fdate.getDate() + " 00:00:00.000";
				_url = _url + "&tdate=" + tdate.getFullYear() + "-" + (tdate.getMonth() + 1) + "-" + tdate.getDate() + " 23:59:59.999";	
				_url = _url + "&view=" + lastView;
				
				loadPage('contentView', _url, function(status, message)
				{
					
					var items = JSON.parse(message);
					callback(items);
					
				}, true);
			},
			eventClick: function(calEvent, jsEvent, view) {
			
				gt('contentView', '<?php echo $module_id;?>', 'form', calEvent.id, '<?php echo $params;?>&view_module_id=<?php echo $view_module_id;?>', '<?php echo $root_module_id;?>');
				
			},
			dayClick: function(date,jsEvent,view) {
				var fdate = dateToString(new Date(date));
				var tdate = dateToDateString(new Date(date)) + ' 23:59:59';
				gt('contentView', '<?php echo $module_id;?>', 'form', '', '<?php echo $params;?>&view_module_id=<?php echo $view_module_id;?>&<?php echo $start_name;?>=' + fdate + '&<?php echo $end_name;?>=' + tdate, '<?php echo $root_module_id;?>');
			}
		});
		
	});
	

</script>
