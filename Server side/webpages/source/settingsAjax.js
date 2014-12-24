var App = App || {};

App.ajax = (function($){
	var ACTION = {
		nameChange: 'nameChange',
		changeAction: 'changeAction',
		changePassword: 'changePassword',
		changeSensorUnit: 'changeSensorUnit',
		changeSensorName: 'changeSensorName'
	}

	function getAddress(action){
		if(action == ACTION.nameChange || action == ACTION.changeAction || action == ACTION.changePassword  || action == ACTION.changeSensorUnit  || action == ACTION.changeSensorName){
			return 'backend/backendSettings.php';
		} else {
			return false;
		}
	}

	return {
		call: function(action, transferData, successHandler, errorHandler){
			var address = getAddress(action);
			if(!address){
				alert("The action is not suitable");
			}
	
			$.ajax({
				url: address,
				type: 'POST',
				dataType: 'json',
				data:{
					'action': action,
					'data': transferData
				},

				success: function(dataFromServer){
					if(successHandler){
						successHandler(action, dataFromServer);
					}
				},

				error: function(xhr, status, error){
					if(errorHandler){
						errorHandler(action, error);
					}
				}
			});
		}
	}
}(jQuery));
