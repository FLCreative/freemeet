$(function() {
  
  var FADE_TIME = 150; // ms
  var TYPING_TIMER_LENGTH = 1000; // ms

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

    scrollChatBox(el.closest('div.panel-chat'));
    
    ion.sound.play("notify");
    
  }

  // Prevents input from having injected markup
  function cleanInput (input) {
    return $('<div/>').text(input).text();
  }
  
  // Adds the visual chat typing message
  function addChatTyping (data) {
	
	var photo = $('<a class="image" href="#">').append($('<img width="35px" height="35px">').attr('src',data));  
		
	var $message = $('<li class="left" id="typing">').append(photo).append($('<div class="message">').text('...'));  
	
    $('div.panel-chat[data-username='+data+']').find('ul.chats').append($message);
    
    scrollChatBox($('div.panel-chat[data-username='+data+']'));

  }

  // Removes the visual chat typing message
  function removeChatTyping (data, type) {	  
	  if(type === 'quick')
	  {
		  	getTypingMessages(data).remove();
		  	
		  	return;
      }
	  
	  getTypingMessages(data).fadeOut(function () {
      $(this).remove();
      
    });
  }

  // Updates the typing event
  function updateTyping (username) {
    if (connected) {
      if (!typing) {
        
    	typing = true;
        console.log(typing);
    	socket.emit('typing',username);
      }
      lastTypingTime = (new Date()).getTime();

      setTimeout(function () {
        var typingTimer = (new Date()).getTime();
        var timeDiff = typingTimer - lastTypingTime;
        if (timeDiff >= TYPING_TIMER_LENGTH && typing) {
          socket.emit('stop typing',username);
          typing = false;
          console.log(typing);
        }
      }, TYPING_TIMER_LENGTH);
    }
  }

  // Gets the 'X is typing' messages of a user
  function getTypingMessages (data) {
    return  $('div.panel-chat[data-username='+data+']').find('li#typing');
  }
  
  function makeChatBox(username, conversation)
  {
	  var $chatBox = $('<div>').addClass('panel panel-primary panel-chat');
	  var $heading = $('<div>').addClass('panel-heading');
	  var $title = $('<h3>').addClass('panel-title');
	  var $body = $('<div>').addClass('panel-body');
	  var $footer = $('<div>').addClass('panel-footer');
	  var $chats = $('<ul class="chats" data-id="'+conversation+'">');
	  
	  $title.html(username);
	  
	  var $closeButton = $('<button type="button" class="close pull-right" aria-label="Close"><span aria-hidden="true">&times;</span></button>');
	  
	  $heading.append($closeButton)
	          .append($title);
	  
	  $body.append($chats);
	  
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
	  
	  $.get('/mailbox/load-messages/'+conversation, function(r) {
		  
		  $chats.append(r.messages);
		  
		  if($('div.panel-chat').length)
		  {		  
			 var positions = $('div.panel-chat').last().position(); 
			 $chatBox.css('left',positions.left - ($('div.panel-chat').width() + 10));
		  }
		  
		  $('div.chatBoxes').append($chatBox);
		  
		  scrollChatBox($chatBox);
		  
		  $closeButton.click(function(){closeChatBox($chatBox);});
		  $heading.click(function(){toggleChatBox($chatBox);});
		  
		  $.post('/mailbox/update-chatbox-status', {conversation: conversation, status: "open"});
		  
		  return $chatBox;
	  });
  }
  
  function scrollChatBox(chatBox)
  {
	  var $body = $('div.panel-body',chatBox);
	  
	  $body.scrollTop( $('div.panel-body ul',chatBox).height());
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
  
  // Resize window events
  
  $( window ).resize(function() {
	  resizeChatBoxes();
  });
  
  function resizeChatBoxes()
  {
	  $opened = $("div.panel-chat:visible").length;
	  
	  $size = $opened * 260;	  
	  
	  if($size > $(window).width())
	  {
		  $toRemove = ($size - $(window).width()) / 260 ;
		  
		  for (i = 0; i < $toRemove; i++) { 
		  
			  $('div.panel-chat:visible').first().hide();
		  }
		  
		  console.log( $toRemove );  
	  }
	  else
	  {
		  $toDisplay = Math.floor(($(window).width() - $size) / 260) ;
		  
		  for (i = 0; i < $toDisplay; i++) { 
		  
			  $('div.panel-chat:hidden').first().show();
		  }
	  }
  }
  
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
			  	  			  
		  });
		  
		  chatBox.remove();
		  
		  $.post('/mailbox/update-chatbox-status', {conversation: $('input[name=conversation]',chatBox).val(), status: "close"});
		  	  
	  }
	  
  }
  
  function toggleChatBox(chatBox)
  {
	  chatBox.toggleClass( "panel-primary");
	  
	  $('div.panel-body, div.panel-footer', chatBox).toggle();
	  
	  if($('div.panel-body, div.panel-footer', chatBox).is(':visible'))
	  {
		  status = 'open';
	  }
	  else
      {
		  status = 'reduce';
	  }
	  
	  $.post('/mailbox/update-chatbox-status', {conversation: $('input[name=conversation]',chatBox).val(), status: status});
  }

  $('div.panel-chat textarea').on('input', function() {
	  
	  $chatBox = $(this).closest('div.panel-chat');
	  
	  var username = $chatBox.attr('data-username'); 
	  
	  updateTyping(username);
  });
  

  // Click events
  
  $("[id=chat_with]").on('click', function(e){			  
	  
	  $.get('/mailbox/compose/'+$(this).attr('data-id'), function(r){
		
		  makeChatBox(r.username,r.id);
	  
	  });
	  
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
	removeChatTyping(data.sender,'quick');
    addChatMessage(data);
  });

  // Whenever the server emits 'user joined', log it in the chat body
  socket.on('user joined', function (data) {
    console.log(data.username + ' joined');
  });

  // Whenever the server emits 'user left', log it in the chat body
  socket.on('user left', function (data) {
    console.log(data.username + ' left');
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