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
		nameOutputChange: 'nameOutputChange',
		changeAction: 'changeAction',
		changePassword: 'changePassword',
		changeSensorUnit: 'changeSensorUnit',
		changeSensorName: 'changeSensorName',
		deleteSensor: 'deleteSensor',
		changeIP: 'changeIP'
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
		} else if(returnAction == action.nameOutputChange) {
			if(data.status == transferResult.success){
				$('#successMessage'+data.data.data['id']).removeClass('hidden');
			} else {
				addError();
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
				$('#successMessageSensors'+data.data.data['i']).removeClass('hidden');
			} else {
				addError();
			}
		} else if(returnAction == action.deleteSensor) {
			if(data.status == transferResult.success){
				$('#sensor'+data.data.data['i']).empty();
			} else {
				addError();
			}
		} else if(returnAction == action.changeIP) {
			if(data.status == transferResult.success){
				$('#successMessageIP').removeClass('hidden');

				if(data.data['ping']) {
					$('#connectedIP').removeClass('hidden');
					$('#impossibleIP').addClass('hidden');
				} else
					$('#impossibleIP').removeClass('hidden');

			} else {
				addError();
			}
		}
			
	}

	function removeMessages() {
		$("#messages").empty();
		var nbOutput = escapeHtml($("#NB_OUTPUT").text());
		for(var i=0;i<nbOutput;i++)
			$('#successMessage'+i).addClass('hidden');

		var nbSensors = escapeHtml($("#NB_SENSORS").text());
		for(var i=0;i<nbSensors;i++)
			$('#successMessageSensors'+i).addClass('hidden');

		$('#successMessageIP').addClass('hidden');
		$('#impossibleIP').addClass('hidden');
		$('#connectedIP').addClass('hidden');

		$('#oldPasswordGroup').removeClass('error');
		$('#newPasswordGroup').removeClass('error');
		$('#retypedNewPasswordGroup').removeClass('error');

		$('#errorMessage2').addClass('hidden');
		$('#successMessage').addClass('hidden');
		$('#errorMessage1').addClass('hidden');
	}

	function addError() {
		$("#messages").append('<div class="alert alert-danger"><strong>Oh snap!</strong> Impossible to make the change.</div>');
	}

	function errorHandler(returnAction, data){
		addError();
	}

	function buttonOnOffIR(e){
		removeMessages();
		e.preventDefault();
		var nbOutput = escapeHtml($("#NB_OUTPUT").text());
		origin = e['currentTarget']['id'].split('_');
		var transferData = {};
		transferData['type'] = escapeHtml(origin[0]);
		transferData['i'] = escapeHtml(origin[1]);
		transferData['j'] = escapeHtml(origin[2]);
		var buttonType = escapeHtml(origin[0]);
		var buttonI = escapeHtml(origin[1]);
		var buttonJ = escapeHtml(origin[2]);

		var valueButtons = {};

		for(var j=0;j<nbOutput;j++) {
			if(document.getElementById("on_"+buttonI+"_"+j).disabled)
				valueButtons[j]=2;
			else if(document.getElementById("off_"+buttonI+"_"+j).disabled)
				valueButtons[j]=3;
			else
				valueButtons[j]=1;

		}

		// we also have to update the value of the clicked button because the disable field is not yet updated
		if(buttonType == 'on')
			valueButtons[buttonJ]=2;
		else if(buttonType == 'off')
			valueButtons[buttonJ]=3;
		else
			valueButtons[buttonJ]=1;

		transferData['buttons'] = valueButtons;

		App.ajax.call(action.changeAction, transferData, successHandler, errorHandler);
		return false;
	}

	function buttonDeleteSensor(e){
		removeMessages();
		e.preventDefault();
		origin = e['currentTarget']['id'].split('_');
		var transferData = {};
		transferData['i'] = escapeHtml(origin[1]);
		transferData['id'] = escapeHtml($("#idSensor_"+transferData['i']).text());
		transferData['address'] = escapeHtml(e['currentTarget']['value']);
		App.ajax.call(action.deleteSensor, transferData, successHandler, errorHandler);
		return false;
	}

	function textUpdateOutputName(e){
		removeMessages();
		e.preventDefault();
		origin = e['currentTarget']['id'].split('_');
		var transferData = {};
		transferData['id'] = escapeHtml(origin[1]);
		transferData['text'] = escapeHtml(e['currentTarget']['value']);
		App.ajax.call(action.nameOutputChange, transferData, successHandler, errorHandler);
		return false;
	}

	function textUpdateSensorName(e){
		removeMessages();
		e.preventDefault();
		origin = e['currentTarget']['id'].split('_');
		var transferData = {};
		transferData['i'] = escapeHtml(origin[1]);
		transferData['id'] = escapeHtml($("#idSensor_"+transferData['i']).text());
		transferData['text'] = escapeHtml(e['currentTarget']['value']);
		App.ajax.call(action.changeSensorName, transferData, successHandler, errorHandler);
		return false;
	}

	function textUpdateSensorUnit(e){
		removeMessages();
		e.preventDefault();
		origin = e['currentTarget']['id'].split('_');
		var transferData = {};
		transferData['i'] = escapeHtml(origin[1]);
		transferData['id'] = escapeHtml($("#idSensor_"+transferData['i']).text());
		transferData['text'] = escapeHtml(e['currentTarget']['value']);
		App.ajax.call(action.changeSensorUnit, transferData, successHandler, errorHandler);
		return false;
	}

	function textUpdateIP(e){
		removeMessages();
		e.preventDefault();
		var transferData = {};
		transferData['ip'] = escapeHtml(e['currentTarget']['value']);
		App.ajax.call(action.changeIP, transferData, successHandler, errorHandler);
		return false;
	}


	return{
		actions: function(){

			var nbOutput = escapeHtml($("#NB_OUTPUT").text());
			var nbButtonsIR = escapeHtml($("#NB_BUTTONS_IR").text());
			var nbSensors = escapeHtml($("#NB_SENSORS").text());

			// Listener for the IR remote buttons
			for(var i=0;i<nbButtonsIR;i++)
				for(var j=0;j<nbOutput;j++) {
					$("#nothing_"+i+"_"+j).on('click', buttonOnOffIR);
					$("#on_"+i+"_"+j).on('click', buttonOnOffIR);
					$("#off_"+i+"_"+j).on('click', buttonOnOffIR);
			}

			// Listener for the delete sensor buttons
			for(var i=0;i<nbSensors;i++)
				$("#delete_"+i).on('click', buttonDeleteSensor);
			
			// Listener for the text field output name
			for(var i=0;i<nbOutput;i++)
				$("#outputName_"+i).on('keyup', textUpdateOutputName);


			// Listener for sensors fields
			for(var i=0;i<nbSensors;i++) {
				$("#nameSensor_"+i).on('keyup', textUpdateSensorName);
				$("#unitSensor_"+i).on('keyup', textUpdateSensorUnit);
			}

			// Listener for ip field
			$("#ip").on('keyup', textUpdateIP);


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
