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
		off: 'off'
	}


	function successHandler(id, returnAction, data){
		if(data.status == transferResult.success){
			if(returnAction == action.on){
				document.getElementById("on_"+data.data['i']).disabled = true; 
				document.getElementById("off_"+data.data['i']).disabled = false; 
			}
			else {
				document.getElementById("on_"+data.data['i']).disabled = false; 
				document.getElementById("off_"+data.data['i']).disabled = true; 
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

	return{

		buttonClicked: function(){
			// Search button event handling

			for(var i=0;i<8;i++) {
				$("#on_"+i).on('click', buttonClik);
				$("#off_"+i).on('click', buttonClik);
			}
		}
	}
}(jQuery));



$(document).ready(function(){
	App.manage.buttonClicked();
});
