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
				url : 'url.php',
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