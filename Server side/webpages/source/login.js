var App = App || {};

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

App.index = (function($){
	var transferResult = {
		success: 'SUCCESS',
		fail: 'FAIL'
	}

	var action = {
		login: 'login'
	}

	function successHandler(returnAction, data){
		if(data.status == transferResult.success){
			$('#errorMessage').addClass('hidden');
			$('#successMessage').removeClass('hidden');
			if(returnAction == action.login){
				console.log('Success');
				window.location.replace("main.php");
			}
		} else if(data.status == transferResult.fail){
			$('#errorMessage').removeClass('hidden');
			$('#successMessage').addClass('hidden');
		}
	}

	function errorHandler(action, data){
		$('#successMessage').addClass('hidden');
		$('#errorMessage').addClass('hidden');
	}

	return{
		buttonClicked: function(){		
			$("#loginButton").on('click', function(e){
				e.preventDefault();
				var username = escapeHtml($('#inputUsername').val());
				var password = escapeHtml($('#inputPassword').val());
				$('#usernameGroup').removeClass('error');
				$('#passwordGroup').removeClass('error');
				if(!username.match(/\S/) || !password.match(/\S/)){
					if(!username.match(/\S/)){
						$('#usernameGroup').addClass('error');
					}
					if(!password.match(/\S/)){
						$('#passwordGroup').addClass('error');
					}
				} else {
					var transferData = {};
					transferData['user'] = username;
					transferData['pass'] = password;
					App.ajax.call(action.login, transferData, successHandler, errorHandler);
					return false;
				}
			});
			return false;
		}
		
	}
}(jQuery));

$(document).ready(function(){
	App.index.buttonClicked();
})
