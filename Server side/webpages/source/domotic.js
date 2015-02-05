var App = App || {};

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

App.manage = (function($){	
	var transferResult = {
		success: 'SUCCESS',
		fail: 'FAIL'
	}

	var action = {
		on: 'on',
		off: 'off',
		submitAlarm: 'submitAlarm'
	}


	function successHandler(id, returnAction, data){
		if(data.status == transferResult.success) {
			if(returnAction == action.on) {
				document.getElementById("on_"+data.data['i']).disabled = true; 
				document.getElementById("off_"+data.data['i']).disabled = false; 
			} else if(returnAction == action.off) {
				document.getElementById("on_"+data.data['i']).disabled = false; 
				document.getElementById("off_"+data.data['i']).disabled = true; 
			} else if(returnAction == action.submitAlarm) {
				$("#modal"+data.data['i']).modal('hide'); 
			}
		} else if(data.status == transferResult.fail){
			$("#errorMessages").append('<div class="alert alert-danger" role="alert"><strong>Oh snap!</strong> Impossible to contact the Arduino.</div>');
		}
	}

	function errorHandler(action, data){
		      $("#errorMessages").append('<div class="alert alert-danger" role="alert"><strong>Oh snap!</strong> Impossible to contact the Arduino.</div>');
	}

	function buttonClik(e) {
		$("#errorMessages").empty();
		e.preventDefault();
		var transferData = {};
		origin = e['currentTarget']['id'].split('_');
		transferData['type'] = escapeHtml(origin[0]);
		transferData['i'] = escapeHtml(origin[1]);

		if(transferData['type'] == 'on')
			App.ajax.call(action.on, transferData, successHandler, errorHandler);
		else
			App.ajax.call(action.off, transferData, successHandler, errorHandler);
				
		return false;
	}

	function buttonUpDown(e) {
		e.preventDefault();
		var transferData = {};
		origin = e['currentTarget']['id'].split('_');
		transferData['element'] = escapeHtml(origin[0])+"_"+escapeHtml(origin[1]);
		transferData['type'] = escapeHtml(origin[2]);
		transferData['i'] = escapeHtml(origin[3]);

		var modulo;
	
		if(origin[1] == "hour") modulo = 24;
		else modulo = 60;

		if(transferData['type'] == "up") {
			var val = (parseInt($("#"+transferData['element']+"_"+transferData['i']).text())+1)%modulo;
			$("#"+transferData['element']+"_"+transferData['i']).empty();
			if(val < 10)
				$("#"+transferData['element']+"_"+transferData['i']).append('0'+val);
			else
				$("#"+transferData['element']+"_"+transferData['i']).append(val);
		}
		else if(transferData['type'] == "down") {
			var val = (parseInt($("#"+transferData['element']+"_"+transferData['i']).text())-1+modulo)%modulo;
			$("#"+transferData['element']+"_"+transferData['i']).empty();
			if(val < 10)
				$("#"+transferData['element']+"_"+transferData['i']).append('0'+val);
			else
				$("#"+transferData['element']+"_"+transferData['i']).append(val);
		}
				
		return false;
	}


	function buttonSubmitAlarm(e) {
		$("#errorMessages").empty();
		e.preventDefault();
		var transferData = {};
		origin = e['currentTarget']['id'].split('_');
		transferData['i'] = escapeHtml(origin[1]);
		transferData['dur'] = {};
		transferData['dur']['min'] = $("#dur_min_"+transferData['i']).text();
		transferData['dur']['hour'] = $("#dur_hour_"+transferData['i']).text();
		transferData['time'] = {};
		transferData['time']['min'] = $("#time_min_"+transferData['i']).text();
		transferData['time']['hour'] = $("#time_hour_"+transferData['i']).text();
		transferData['inverse'] = $('input[name=inverse'+transferData['i']+']').is(':checked');

		// we get the active buttons : when a button is active the class "active" is added, so we just
		// check if this class is attached to each buttons

		var days = 0;

		for(var i=0; i<7;i++)
			if($("#"+i+"_"+transferData['i']).attr("class").indexOf("active") != -1)
				days += 1<<i;

		transferData['days'] = days;

		App.ajax.call(action.submitAlarm, transferData, successHandler, errorHandler);
				
		return false;
	}

	return{

		buttonClicked: function(){
			// Search button event handling

			for(var i=0;i<8;i++) {
				$("#on_"+i).on('click', buttonClik);
				$("#off_"+i).on('click', buttonClik);
				$("#time_hour_up_"+i).on('click', buttonUpDown);
				$("#time_min_up_"+i).on('click', buttonUpDown);
				$("#time_hour_down_"+i).on('click', buttonUpDown);
				$("#time_min_down_"+i).on('click', buttonUpDown);
				$("#dur_hour_up_"+i).on('click', buttonUpDown);
				$("#dur_min_up_"+i).on('click', buttonUpDown); 
				$("#dur_hour_down_"+i).on('click', buttonUpDown);
				$("#dur_min_down_"+i).on('click', buttonUpDown);
				$("#submitAlarm_"+i).on('click', buttonSubmitAlarm);
			}
		}
	}
}(jQuery));



$(document).ready(function(){
	App.manage.buttonClicked();
});
