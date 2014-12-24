var App = App || {};

App.ajax = (function($){
	var ACTION = {
		login: 'login'
	}

	var backEndAddr = {
		login: 'backend/backendLogin.php'
	}

	function getAddress(action){
		if(action == ACTION.login){
			return backEndAddr.login;
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
