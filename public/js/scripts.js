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

function getQueryString(fieldname) {
    var url = document.location+''; // Insures string
    q=url.split('?');
    if (q[1]) {
        var pairs = q[1].split('&');
        for (i=0;i<pairs.length;i++) {
            var keyval = pairs[i].split('=');
            if (keyval[0] == fieldname) { var v = keyval[1]; break; }
        }
    }
    if (v) { return v; }
}

function createSection(sectionData, containerName){
	var HTML = '';
	for(var i = 0; i < sectionData.length; i++){
		HTML += '<div class="thumbnail_content">';
		HTML += '<a href="/view-post/?id='+sectionData[i].post_id+'" class="thumbnail">';
		HTML += '<h4 class="title">'+sectionData[i].title+'</h4>';
		HTML += '<div class="author"><a href="/view-author/?id='+sectionData[i].author_id+'">'+sectionData[i].author+'</a></div>';
		HTML += '<div class="description">'+sectionData[i].description+'</div>';
		HTML += '<div style="margin-top: 0" class="likes_section pull-left">';
		HTML += '<div style="margin-right:15px" class="pull-left">';
		HTML += '<span class="fa fa-thumbs-up"></span> '+sectionData[i].likes;
		HTML += '</div>';
		HTML += '<div class="pull-left">';
		HTML += '<span class="fa fa-thumbs-down"></span> '+sectionData[i].unlikes;
		HTML += '</div>';
		HTML += '<div class="clearfix"></div>';
		HTML += '</div>';
		HTML += '<button class="pull-right btn btn-primary btn-xs" type="button">Read</button>';
		HTML += '<div class="clearfix"></div>';
		HTML += '</a>';
		HTML += '</div>';
	}
	$(containerName).html(HTML);
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
				$('.welcome_message').html('Hi, ' + response.username + '<span class="glyphicon glyphicon-chevron-down"></span>');
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
						location.reload();
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
				alert(response.message);
				if(response.status == 1){
					location.reload();
				}
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
					alert(response.message);
					if(response.status == true){
						location.href = response.url;
					}
				},
				error : function(response){
					alert('Error Occured')
				}
        	})
        }

	})

	$(document).on('click', '#post_update', function(){
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
        	var blogID = getQueryString('id');
        	var blogPostObj = {};
        	blogPostObj.post_id = blogID;
        	blogPostObj.title = $('#post_create #post_title').val();
        	blogPostObj.body = escape(bodyMessage);
        	blogPostObj.tags = $("input#tags").tagsinput('items');
        	$.ajax({
        		url : '/post/update/',
        		dataType : 'json',
        		method : 'POST',
        		contentType: "application/json",
				data : JSON.stringify(blogPostObj),
				success : function(response){
					alert(response.message);
					if(response.status == true){
						location.href = response.url;
					}
				},
				error : function(response){
					alert('Error Occured')
				}
        	})
        }

	})

	$(document).on('click', '.likes a', function(){
		var blogID = getQueryString('id');
		var thisButton = $(this);
		if($('.dislikes a').hasClass('disliked')){
			alert('You have already disliked this Post. Please click on the Dislike button again to remove your dislike and then like this post');
		}else{

			var likeObj = {};
			likeObj.post_id = Number(blogID);

			$.ajax({
				url : '/like/',
				method : 'POST',
				dataType : 'json',
				contentType: "application/json",
				data : JSON.stringify(likeObj),
				success : function(response){
					if(response.status == 1){
						alert(response.message);
						if(thisButton.hasClass('like')){
							thisButton.parent().html('<a class="fa fa-thumbs-up liked"></a> '+ response.likes);
						}else{
							thisButton.parent().html('<a class="fa fa-thumbs-up like"></a> '+ response.likes)
						}
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

	$(document).on('click', '.delete_comment', function(){
		var thisCommentID = $(this).attr('id');
		var thisComment = $(this).parent();
		deleteCommentObj = {id : thisCommentID};
		$.ajax({
			url : '/comment/delete/',
			method : 'POST',
			dataType : 'json',
			contentType : 'application/json',
			data : JSON.stringify(deleteCommentObj),
			success : function(response){
				if(response.status == 1){
					thisComment.remove()
				}else{
					alert('Comment could not be deleted')
				}
			},
			error : function(response){
				alert('Error Occured');
			}
		})
	})

	$(document).on('click', '.dislikes a', function(){
		var blogID = getQueryString('id');
		var thisButton = $(this);
		if($('.likes a').hasClass('liked')){
			alert('You have already liked this Post. Please click on the Like button again to remove your like and then dislike this post');
		}else{

			var dislikeObj = {};
			dislikeObj.post_id = Number(blogID);

			$.ajax({
				url : '/dislike/',
				method : 'POST',
				dataType : 'json',
				contentType: "application/json",
				data : JSON.stringify(dislikeObj),
				success : function(response){
					if(response.status == 1){
						alert(response.message);
						if(thisButton.hasClass('dislike')){
							thisButton.parent().html('<a class="fa fa-thumbs-down disliked"></a> '+ response.unlikes);
						}else{
							thisButton.parent().html('<a class="fa fa-thumbs-down dislike"></a> '+ response.unlikes)
						}
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

	$(document).on('click', '.delete_blog_post', function(){
		var post_id = getQueryString('id');
		var deletePostObj = {id : post_id};
		$.ajax({
			url : '/post/delete/',
			dataType : 'json',
			method : 'POST',
			contentType : 'application/json',
			data : JSON.stringify(deletePostObj),
			success : function(response){
				if(response.status == 1){
					alert('Your Post has been deleted');
					location.href = '/'
				}else{
					alert('Your Post could not be deleted')
				}
			},
			error : function(response){
				alert('Error Occured');
			}
		})
	})

	$(document).on('click', '.comment_submit', function(){
		var blogID = getQueryString('id');
		var comment = $('.comment_post').val();
		if(comment){
			var commentObj = {
				post_id : blogID,
				content : comment
			};

			$.ajax({
				url : '/comment/create/',
				dataType : 'json',
				method : 'POST',
				contentType: "application/json",
				data : JSON.stringify(commentObj),
				success : function(response){
					if(response.status == 1){
						var commentHTML = $.parseHTML(unescape(response.html_content));
						$('.comments_parent').append(commentHTML);
						$('textarea.comment_post').val('');
					}else{
						alert(response.message);
					}
				},
				error : function(response){
					alert('Error Occured');
				}
			})

		}else{
			alert('Comment Field cannot be blank')
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
					alert(response.message)
					if(response.status == 1){
						location.reload();
					}
				},
				error : function(errResponse){
					alert('Error Occured')
				}
			})

		}
	})

	$(document).on('click', '#profile_edit', function(){

		var noErrors = true;

		$('#profile_page #home .form-group').each(function(){
			var thisInput = $(this).find('input');
			if(!thisInput.val()){
				noErrors = false;
				var thisField = thisInput.parent().find('label').text();
				alert('Please ensure that the ' +thisField+ ' is not empty');
				return false;
			}
		})

		if(noErrors){
			var submitObj = {};
			submitObj.first_name = $('input#first_name').val();
			submitObj.last_name = $('input#last_name').val();
			submitObj.email = $('input#email').val();

			$.ajax({
				url : '/user/update',
				method : 'POST',
				dataType : 'json',
				contentType: "application/json",
				data : JSON.stringify(submitObj),
				success : function(response){
					alert(response.message);
					if(response.status == 1){
						location.reload();
					}
				},
				error : function(response){
					alert('Error Occured')
				}
			})

		}

	})

	$(document).on('click', '#change_password_submit', function(){
		
		var noErrors = true;

		$('#password_modal .form-group').each(function(){
			var thisInput = $(this).find('input');
			if(!thisInput.val()){
				noErrors = false;
				var thisField = thisInput.parent().find('label').text();
				alert('Please ensure that the ' +thisField+ ' is not empty');
				return false;
			}
		})



		if(noErrors === true && $('#new_password').val() !== $('#reenter_new_password').val()){
			noErrors = false;
			alert('Please ensure that the Passwords match');
		}

		if(noErrors){
			var submitObj = {};
			submitObj.old_password = $('#old_password').val();
			submitObj.new_password = $('#new_password').val();

			$.ajax({
				url : '/login/change-password',
				method : 'POST',
				dataType : 'json',
				contentType : 'application/json',
				data : JSON.stringify(submitObj),
				success : function(response){
					alert(response.message);
					if(response.status == 1){
						$('#password_modal').modal('hide')
					}
				},
				error : function(response){
					alert('Error Occured');
				}
			})

		}

	})

})