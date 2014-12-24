var App = App || {};

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

App.settings = (function($){
	var transferResult = {
		success: 'SUCCESS',
		fail: 'FAIL'
	}

	var action = {
		nameChange: 'nameChange',
		changeAction: 'changeAction',
		changePassword: 'changePassword',
		changeSensorUnit: 'changeSensorUnit',
		changeSensorName: 'changeSensorName'
	}

	function successHandler(returnAction, data){
		if(returnAction == action.changeAction) {
			if(data.status == transferResult.success){
			if(data.data.data['type'] == 'on') {
				document.getElementById("on_"+data.data.data['i']+"_"+data.data.data['j']).disabled = true; 
				document.getElementById("off_"+data.data.data['i']+"_"+data.data.data['j']).disabled = false; 
				document.getElementById("nothing_"+data.data.data['i']+"_"+data.data.data['j']).disabled = false; 
			} else if (data.data.data['type'] == 'off') {
				document.getElementById("on_"+data.data.data['i']+"_"+data.data.data['j']).disabled = false; 
				document.getElementById("off_"+data.data.data['i']+"_"+data.data.data['j']).disabled = true; 
				document.getElementById("nothing_"+data.data.data['i']+"_"+data.data.data['j']).disabled = false; 
			} else if (data.data.data['type'] == 'nothing') {
				document.getElementById("on_"+data.data.data['i']+"_"+data.data.data['j']).disabled = false; 
				document.getElementById("off_"+data.data.data['i']+"_"+data.data.data['j']).disabled = false; 
				document.getElementById("nothing_"+data.data.data['i']+"_"+data.data.data['j']).disabled = true; 
			}
			$("#messages").append('<div class="alert alert-success">Change is saved.</div>');}
		} else if(returnAction == action.nameChange) {
			if(data.status == transferResult.success){
				$('#successMessage'+data.data.data['id']).removeClass('hidden');
			} else {
				$("#messages").append('<div class="alert alert-danger"><strong>Oh snap!</strong> Impossible to make the change.</div>');
			}
		} else if(returnAction == action.changePassword) {
			if(data.status == transferResult.success){
				$('#errorMessage2').addClass('hidden');
				$('#errorMessage1').addClass('hidden');
				$('#successMessage').removeClass('hidden');
			} else if(data.status == transferResult.fail){
				$('#errorMessage2').removeClass('hidden');
				$('#successMessage').addClass('hidden');
				$('#errorMessage1').addClass('hidden');
			}
		} else if(returnAction == action.changeSensorUnit || returnAction == action.changeSensorName) {
			if(data.status == transferResult.success){
				$('#successMessageSensors'+data.data.data['id']).removeClass('hidden');
			}
		}
			
	}

	function removeMessages() {
		$("#messages").empty();
		var nbOutput = $("#NB_OUTPUT").text();
		for(var i=0;i<nbOutput;i++)
			$('#successMessage'+i).addClass('hidden');

		var nbSensors = $("#NB_SENSORS").text();
		for(var i=0;i<nbSensors;i++)
			$('#successMessageSensors'+i).addClass('hidden');

		$('#oldPasswordGroup').removeClass('error');
		$('#newPasswordGroup').removeClass('error');
		$('#retypedNewPasswordGroup').removeClass('error');

		$('#errorMessage2').addClass('hidden');
		$('#successMessage').addClass('hidden');
		$('#errorMessage1').addClass('hidden');
	}

	function errorHandler(returnAction, data){
		$("#messages").append('<div class="alert alert-danger"><strong>Oh snap!</strong> Impossible to make the change.</div>');
	}

	function buttonOnClick(e){
		removeMessages();
		e.preventDefault();
		origin = e['currentTarget']['id'].split('_');
		var transferData = {};
		transferData['type'] = origin[0];
		transferData['i'] = origin[1];
		transferData['j'] = origin[2];
		App.ajax.call(action.changeAction, transferData, successHandler, errorHandler);
		return false;
	}

	function textUpdateOutputName(e){
		removeMessages();
		e.preventDefault();
		origin = e['currentTarget']['id'].split('_');
		var transferData = {};
		transferData['id'] = origin[1];
		transferData['text'] = escapeHtml(e['currentTarget']['value']);
		App.ajax.call(action.nameChange, transferData, successHandler, errorHandler);
		return false;
	}

	function textUpdateSensorName(e){
		removeMessages();
		e.preventDefault();
		origin = e['currentTarget']['id'].split('_');
		var transferData = {};
		transferData['id'] = origin[1];
		transferData['text'] = escapeHtml(e['currentTarget']['value']);
		App.ajax.call(action.changeSensorName, transferData, successHandler, errorHandler);
		return false;
	}

	function textUpdateSensorUnit(e){
		removeMessages();
		e.preventDefault();
		origin = e['currentTarget']['id'].split('_');
		var transferData = {};
		transferData['id'] = origin[1];
		transferData['text'] = escapeHtml(e['currentTarget']['value']);
		App.ajax.call(action.changeSensorUnit, transferData, successHandler, errorHandler);
		return false;
	}


	return{
		actions: function(){

			var nbOutput = $("#NB_OUTPUT").text();
			var nbButtonsIR = $("#NB_BUTTONS_IR").text();
			var nbSensors = $("#NB_SENSORS").text();

			// Listener for the IR remote buttons
			for(var i=0;i<nbButtonsIR;i++)
				for(var j=0;j<nbOutput;j++) {
					$("#nothing_"+i+"_"+j).on('click', buttonOnClick);
					$("#on_"+i+"_"+j).on('click', buttonOnClick);
					$("#off_"+i+"_"+j).on('click', buttonOnClick);
			}
			
			// Listener for the text field output name
			for(var i=0;i<nbOutput;i++)
				$("#outputName_"+i).on('keyup', textUpdateOutputName);


			// Listener for sensors fields
			for(var i=0;i<nbSensors;i++) {
				$("#nameSensor_"+i).on('keyup', textUpdateSensorName);
				$("#unitSensor_"+i).on('keyup', textUpdateSensorUnit);
			}


			// Listener on password change button
			$("#buttonPass").on('click', function(e){
				e.preventDefault();
				var oldPassword = escapeHtml($('#oldPassword').val());
				var newPassword = escapeHtml($('#newPassword').val());
				var retypedNewPassword = escapeHtml($('#retypedNewPassword').val());

				removeMessages();

				if(!newPassword.match(/\S/) || !retypedNewPassword.match(/\S/) || !oldPassword.match(/\S/)){
					if(!newPassword.match(/\S/)){
						$('#newPasswordGroup').addClass('error');
					}
					if(!oldPassword.match(/\S/)){
						$('#oldPasswordGroup').addClass('error');
					}
					if(!retypedNewPassword.match(/\S/)){
						$('#retypedNewPasswordGroup').addClass('error');
					}
				} else {
					if(newPassword == retypedNewPassword){
						var transferData = {};
						transferData['old'] = oldPassword;
						transferData['new'] = retypedNewPassword;
						App.ajax.call(action.changePassword, transferData, successHandler, errorHandler);
					} else {
						$('#retypedNewPasswordGroup').addClass('error');
						$('#errorMessage1').removeClass('hidden');
					}
				}		
			});

		}
	}
}(jQuery));

$(document).ready(function(){
	App.settings.actions();
})
