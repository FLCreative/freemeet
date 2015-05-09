$(function() {
  
  var FADE_TIME = 150; // ms
  var TYPING_TIMER_LENGTH = 400; // ms
  var COLORS = [
    '#e21400', '#91580f', '#f8a700', '#f78b00',
    '#58dc00', '#287b00', '#a8f07a', '#4ae8c4',
    '#3b88eb', '#3824aa', '#a700ff', '#d300e7'
  ];
  
  ion.sound({
	    sounds: [
	        {name: "notify"},
	    ],

	    // main config
	    path: "/sounds/",
	    preload: true,
	    multiplay: true,
	    volume: 0.9
	});
  
  
  // Initialize varibles
  var $chatboxes = [];
  
  // Prompt for setting a username
  var connected = false;
  var typing = false;
  var lastTypingTime;

  var socket = io.connect('http://freemeet.local:3000');
  
  socket.emit('connect user', $("#user_name").text());
  
  function addParticipantsMessage (data) {
    var message = '';
    if (data.numUsers === 1) {
      message += "there's 1 participant";
    } else {
      message += "there are " + data.numUsers + " participants";
    }
    console.log(message);
  }

  // Sends a chat message
  function sendMessage (element) {	  

	var form = element.closest('form');
	
	$.post(form.attr('action'), form.serialize(), function(r){
		if(r.status == 'success')
		{				
			var row = $("<li>").attr('class','right');
			var date = $("<span>").attr('class','date-time').text(r.date);
			var username = $("<a>").attr('class','name').text('Vous');
			var photoLink = $("<a>").attr('class','image')
			                        .attr('href','#');
			
			var photo = $("<img>").attr('src',r.photo);
			var message = $("<div>").attr('class','message').html(r.message);
			
			photoLink.append(photo);
			$("#mainChat ul.chats[data-id="+r.conversation+"]").append(row.append(date)
						                                       .append(username)
						                                       .append(photoLink)
						                                       .append(message));
			
			var row = $("<li>").attr('class','right');
			var message = $("<div>").attr('class','message').html(r.message);
			
			var chats = $("div.panel-chat ul.chats[data-id="+r.conversation+"]");
			
			var chatbox = chats.closest('div.panel-chat');
			
			chats.append(row
                 .append(message));
			
			$('textarea', form).val('');
			
			$('div.panel-body',chatbox).scrollTop(chats.height());
									
		}
		else
		{
			$.toaster({ priority : 'danger', title : '<span class="glyphicon glyphicon-remove"></span>', message : r.message });
		}

	});
  }

  // Log a message
  function log (message, options) {
    var $el = $('<li>').addClass('log').text(message);
    addMessageElement($el, options);
  }

  // Adds the visual chat message to the message list
  function addChatMessage (data, options) {
    // Don't fade the message in if there is an 'X was typing'
    var $typingMessages = getTypingMessages(data.message);
    options = options || {};
    if ($typingMessages.length !== 0) {
      options.fade = false;
      $typingMessages.remove();
    }
   
    $messageDiv = $('div.panel-chat[data-username='+data.sender+']');
    	

    if(!$messageDiv.length)
    {
        $messageDiv = makeChatBox(data.sender, data.conversation);
        $('body').append($messageDiv);
    }

    addMessageElement($('div.panel-body ul',$messageDiv), data, options); 
  
  }

  // Adds the visual chat typing message
  function addChatTyping (data) {
    data.typing = true;
    data.message = 'is typing';
    addChatMessage(data);
  }

  // Removes the visual chat typing message
  function removeChatTyping (data) {
    getTypingMessages(data).fadeOut(function () {
      $(this).remove();
    });
  }

  // Adds a message element to the messages and scrolls to the bottom
  // el - The element to add as a message
  // options.fade - If the element should fade-in (default = true)
  // options.prepend - If the element should prepend
  //   all other messages (default = false)
  function addMessageElement (el, data, options) {
    
	var photo = $('<a class="image" href="#">').append($('<img width="35px" height="35px">').attr('src',data.photo));  
	
	var $message = $('<li class="left">').append(photo).append($('<div class="message">').text(data.content));
    // Setup default options
    if (!options) {
      options = {};
    }
    if (typeof options.fade === 'undefined') {
      options.fade = true;
    }
    if (typeof options.prepend === 'undefined') {
      options.prepend = false;
    }

    // Apply options
    if (options.fade) {
    	$message.hide().fadeIn(FADE_TIME);
    }
    if (options.prepend) {
      el.prepend($message);
    } else {
      el.append($message);
    }

    el.scrollTop = el.scrollHeight;
    
    ion.sound.play("notify");
    
  }

  // Prevents input from having injected markup
  function cleanInput (input) {
    return $('<div/>').text(input).text();
  }

  // Updates the typing event
  function updateTyping () {
    if (connected) {
      if (!typing) {
        typing = true;
        socket.emit('typing');
      }
      lastTypingTime = (new Date()).getTime();

      setTimeout(function () {
        var typingTimer = (new Date()).getTime();
        var timeDiff = typingTimer - lastTypingTime;
        if (timeDiff >= TYPING_TIMER_LENGTH && typing) {
          socket.emit('stop typing');
          typing = false;
        }
      }, TYPING_TIMER_LENGTH);
    }
  }

  // Gets the 'X is typing' messages of a user
  function getTypingMessages (data) {
    return $('.typing.message').filter(function (i) {
      return $(this).data('username') === data.username;
    });
  }

  // Gets the color of a username through our hash function
  function getUsernameColor (username) {
    // Compute hash code
    var hash = 7;
    for (var i = 0; i < username.length; i++) {
       hash = username.charCodeAt(i) + (hash << 5) - hash;
    }
    // Calculate color
    var index = Math.abs(hash % COLORS.length);
    return COLORS[index];
  }
  
  function makeChatBox(username, conversation)
  {
	  var $chatBox = $('<div>').addClass('panel panel-primary panel-chat');
	  var $heading = $('<div>').addClass('panel-heading');
	  var $title = $('<h3>').addClass('panel-title');
	  var $body = $('<div>').addClass('panel-body');
	  var $footer = $('<div>').addClass('panel-footer');
	  
	  $title.html(username);
	  
	  var $closeButton = $('<button type="button" class="close pull-right" aria-label="Close"><span aria-hidden="true">&times;</span></button>');
	  
	  $heading.append($closeButton)
	          .append($title);
	  
	  $body.append($('<ul class="chats" data-id="'+conversation+'">'));
	  
	  var $form = $('<form method="POST" name="reply_conversation" action="/mailbox/reply">');
	  
	  var $input = $('<div>').append($('<input type="hidden" name="conversation" value="'+conversation+'" class="form-control input-sm">'))
	                         .append($('<textarea name="content" class="form-control input-sm">'));
	  $form.append($input);
	  
	  $footer.append($form);
	  
	  $chatBox.append($heading)
	          .append($body)
	          .append($footer);
	  
	  $chatBox.attr('data-username',username);
	  
	  $chatboxes.push($chatBox);
	  
	  addKeybordEvents($input);	  
	  
	  if($('div.panel-chat').length)
	  {		  
		 var positions = $('div.panel-chat').last().position(); 
		 $chatBox.css('left',positions.left - ($('div.panel-chat').width() + 10));
	  }
	  
	  $('body').append($chatBox);
	  
	  $closeButton.click(function(){closeChatBox($chatBox);});
	  $heading.click(function(){toggleChatBox($chatBox);});
	  
	  $.post('/mailbox/update-chatbox-status', {conversation: conversation, status: "open"});
	  
	  return $chatBox;
  }

  // Keyboard events
  function addKeybordEvents(el)
  {
	  el.keydown(function (event) {		
		// When the client hits ENTER on their keyboard
	    if (event.which === 13) {	          	
	    	sendMessage(el);
	        socket.emit('stop typing');
	        typing = false;
	        
	        event.preventDefault();
	        return false;
	    }
	  });
  }
  
  // Button event
  $('form#reply_conversation input[type=submit]').on('click', function(e) {	  	  
	  sendMessage($(this));
      socket.emit('stop typing');
      typing = false;
  });
  
  // Form submit event
  
  $(document).on('submit','form[name="reply_conversation"]', function(e)
  {
	  e.preventDefault();
  });
  
  function closeChatBox(chatBox)
  {
	  if(chatBox.is('div.panel-chat:last-child'))
      {
		  chatBox.remove();
	  }
	  else
	  {
		  var nextChatBox = chatBox.nextAll('div.panel-chat');
		  	  
		  nextChatBox.each(function(index,element) {
			  
			  if(index == 0)
			  {
				  positions = $(element).prev().position();
				  $(element).css('left',positions.left);
			  }
			  
			  else
			  {
				  positions = $(element).prev().position();
				  $(element).css('left',positions.left - ($(element).width()+10)); 
			  }
			  			  
			  console.log($(element).css('left'));
			  	  			  
		  });
		  
		  chatBox.remove();	
		  	  
	  }
	  
  }
  
  function toggleChatBox(chatBox)
  {
	  chatBox.toggleClass( "panel-primary");
	  
	  $('div.panel-body, div.panel-footer', chatBox).toggle();
  }

  $('div.chatbox input').on('input', function() {
	  updateTyping();
  });
  

  // Click events
  
  $("[id=chat_with]").on('click', function(e){			  
	  $chatBox = makeChatBox($(this).attr('data-username'));
	  
  });
  
  $('div.panel-chat').each(function(index) {
	  
	  var $chatBox = $(this);
	  
	  // Add events to opened chat box
	  
	  $('div.panel-heading button',$chatBox).click(function(){closeChatBox($chatBox);});
	  $('div.panel-heading',$chatBox).click(function(){toggleChatBox($chatBox);});
	  
	  addKeybordEvents($('textarea',$chatBox));
	  
	  // Move all boxes

	  if(index > 0)
	  {
		  positions = $chatBox.prev().position();
		  $chatBox.css('left',positions.left - ($('div.panel-chat').width() + 10));
	  }
	  
	  var $body = $('div.panel-body',$chatBox);
	  
	  $body.scrollTop( $('div.panel-body ul',$chatBox).height());
		  			  
  });
  

  
  

  // Socket events

  // Whenever the server emits 'login', log the login message
  socket.on('login', function () {
    connected = true;
    console.log('connected');
  });

  // Whenever the server emits 'new message', update the chat body
  socket.on('new message', function (data) {
	console.log(data);
    addChatMessage(data);
  });

  // Whenever the server emits 'user joined', log it in the chat body
  socket.on('user joined', function (data) {
    console.log(data.username + ' joined');
    
    console.log(data);
    
    addParticipantsMessage(data);
  });

  // Whenever the server emits 'user left', log it in the chat body
  socket.on('user left', function (data) {
    console.log(data.username + ' left');
    addParticipantsMessage(data);
    removeChatTyping(data);
  });

  // Whenever the server emits 'typing', show the typing message
  socket.on('typing', function (data) {
    addChatTyping(data);
  });

  // Whenever the server emits 'stop typing', kill the typing message
  socket.on('stop typing', function (data) {
    removeChatTyping(data);
  });
});