function initEditor() {
	tinyMCE.init({
		width: '100%',
		height: 350,
		editor_selector: "tinymce",
		mode: "specific_textareas",
		forced_root_block: false,
		force_p_newlines: false,
		remove_linebreaks: false,
		force_br_newlines: false,
		convert_newlines_to_brs: false,
		remove_redundant_brs: false,
		remove_trailing_nbsp: false,
		verify_html: true
	});
}

$(document).ready(function(){

	initEditor();

	$('[data-toggle="popover"]').popover({
		trigger : 'hover'
	})
	
	$.ajax({
		url : '/login/check-login',
		dataType : 'json',
		success : function(response){
			var HTML = $.parseHTML(unescape(response.html_content));
			$('header').html(HTML);
			if(response.loggedin){
				
			}else{
				
			}
		},
		error : function(response){
			alert('Error Occured');
		}
	})
	
	$(document).on('click', '#login_submit', function(){
		var noErrors = true;
		if(!$('#login_modal #username').val()){
			noErrors = false;
			alert('Username cannot be empty');
			return false;
		}
		
		if(!$('#login_modal #password').val()){
			noErrors = false;
			alert('Password field cannot be empty');
			return false;
		}
		
		if(noErrors){
			var userObj = {};
			userObj.username = $('#login_modal #username').val();
			userObj.password = $('#login_modal #password').val();
			$.ajax({
				url : '/login/user-login/',
				method : 'POST',
				dataType : 'json',
				contentType: "application/json",
				data : JSON.stringify(userObj),
				success : function(response){
					if(response.status == 1){
						var HTML = $.parseHTML(unescape(response.html_content));
						$('header').html(HTML);
						var userName = response.user_name;
						$('.welcome_message').html('Hi, '+userName+ ' <span class="glyphicon glyphicon-chevron-down"></span>');
						$('#login_modal').modal('hide')
					}else{
						alert(response.message);
					}
				},
				error : function(response){
					alert('Error Occured');
				}
			})
		}
		
	})
	
	$(document).on('click', '.logout', function(){
		$.ajax({
			url : '/logout',
			dataType : 'json',
			success : function(response){
				var HTML = $.parseHTML(unescape(response.html_content));
				$('header').html(HTML);
			},
			error : function(response){
				alert('Error Occured');
			}
		})
	})

	$(document).on('click', '.welcome_message', function(){
		if($('.welcome_message  span.glyphicon').hasClass('glyphicon-chevron-down')){
			$('.welcome_message span').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
			$('.user_options').slideDown('slow');
		}else{
			$('.welcome_message span').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
			$('.user_options').slideUp('slow');
		}
		
	})

	$(document).on('click', '#post_submit', function(){
		var noErrors = true;

		if(!$('#post_create #post_title').val()){
			noErrors = false;
			alert('Please enter a title for the Blog Post');
			return false;
		}

		var bodyMessage = tinyMCE.activeEditor.getContent();
		if(typeof bodyMessage == 'undefined' || bodyMessage.length < 1){
			alert("Blog Post Body cannot be Empty");
            $("#post_create textarea").focus();
            noErrors = false;
            return false;
        }

        if(noErrors){
        	var blogPostObj = {};
        	blogPostObj.title = $('#post_create #post_title').val();
        	blogPostObj.body = escape(bodyMessage);
        	blogPostObj.tags = $("input#tags").tagsinput('items');
        	$.ajax({
        		url : '/post/create/',
        		dataType : 'json',
        		method : 'POST',
        		contentType: "application/json",
				data : JSON.stringify(blogPostObj),
				success : function(response){
					console.log(response);
				},
				error : function(response){
					alert('Error Occured')
				}
        	})
        }

	})

	$(document).on('click', '#signup_submit', function(){

		var noErrors = true;

		$('#signup_modal .form-group').each(function(){
			var thisInput = $(this).find('input');
			if(!thisInput.val()){
				noErrors = false;
				var thisField = thisInput.parent().find('label').text();
				alert('Please ensure that the ' +thisField+ ' is not empty');
				return false;
			}
		})

		if(noErrors === true && $('#signup_password').val() !== $('#reenter_password').val()){
			noErrors = false;
			alert('Please ensure that the Passwords match');
		}

		if(noErrors){
			var submitObj = {};
			submitObj.first_name = $('#first_name').val();
			submitObj.last_name = $('#last_name').val();
			submitObj.email = $('#email').val();
			submitObj.password = $('#signup_password').val();

			$.ajax({
				url : '/user/create',
				method : 'POST',
				dataType : 'json',
				contentType: "application/json",
				data : JSON.stringify(submitObj),
				success : function(response){
					console.log(response);
				},
				error : function(errResponse){
					alert('Error Occured')
				}
			})

		}else{
			
		}

	})

})