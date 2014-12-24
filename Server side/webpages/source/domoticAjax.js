var App = App || {};

App.ajax = (function($){
	var ACTION = {
		on: 'on',
		off: 'off'
	}

	var backEndAddr = {
		switchP: 'backend/backendSwitch.php'
	}

	function getAddress(action){
		if(action == ACTION.on || action == ACTION.off){
			return backEndAddr.switchP;
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
			

			if(action == ACTION.on || action == ACTION.off){
				$.ajax({
					url: address,
					type: 'POST',
					dataType: 'json',
					data: transferData,

					success: function(dataFromServer){
						if(successHandler){
							successHandler(0, action, dataFromServer);
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
	}
}(jQuery));
