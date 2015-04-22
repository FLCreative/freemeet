$(function() {
  var FADE_TIME = 150; // ms
  var TYPING_TIMER_LENGTH = 400; // ms
  var COLORS = [
    '#e21400', '#91580f', '#f8a700', '#f78b00',
    '#58dc00', '#287b00', '#a8f07a', '#4ae8c4',
    '#3b88eb', '#3824aa', '#a700ff', '#d300e7'
  ];
  
  
  // Initialize varibles
  var $chatboxes = [];
  
  // Prompt for setting a username
  var connected = false;
  var typing = false;
  var lastTypingTime;

  var socket = io.connect('http://localhost:3000');

  function addParticipantsMessage (data) {
    var message = '';
    if (data.numUsers === 1) {
      message += "there's 1 participant";
    } else {
      message += "there are " + data.numUsers + " participants";
    }
    console.log(message);
  }

  // Sets the client's username
  function setUsername () {
      // Tell the server your username
      socket.emit('add user', $('#user_name').text());
  }

  // Sends a chat message
  function sendMessage ($chatBox) {	  

	$inputMessage =  $('input',$chatBox); 
    var $message = $inputMessage.val();
    var $receiver = $chatBox.attr('data-username');
    // Prevent markup from being injected into the message
    $message = cleanInput($message);
    // if there is a non-empty message and a socket connection
    if ($message && connected) {
      $inputMessage.val('');   
      
      var data = {"receiver": $receiver, "message": $message};
      
      addChatMessage(data);
      // tell server to execute 'new message' and send along one parameter
      socket.emit('new message', data);
    }
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
   
    if (typeof data.receiver === 'undefined') {    	
    	$messageDiv = $('div.panel-chat[data-username='+data.username+']');
    	
    	
    	
    	if(!$messageDiv.length)
        {
        	$messageDiv = makeChatBox(data.username);
        	$('body').append($messageDiv);
        }
    }
    else
    {
    	$messageDiv = $('div.panel-chat[data-username='+data.receiver+']');
    	
    }
     
    console.log(data);

    addMessageElement($('div.panel-body',$messageDiv), data.message, options); 
  
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
  function addMessageElement (el, message, options) {
    var $message = $('<li>').text(message);
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
  
  function makeChatBox(username)
  {
	  var $chatBox = $('<div>').addClass('panel panel-default panel-chat');
	  var $heading = $('<div>').addClass('panel-heading');
	  var $title = $('<h3>').addClass('panel-title');
	  var $body = $('<div>').addClass('panel-body');
	  var $footer = $('<div>').addClass('panel-footer');
	  
	  $title.html(username);
	  
	  var $closeButton = $('<button type="button" class="close pull-right" aria-label="Close"><span aria-hidden="true">&times;</span></button>');
	  
	  $heading.append($closeButton)
	          .append($title);
	  
	  var $input = $('<div>').addClass('input-group')
	                         .append($('<input type="text" placeholder="Enter your message here." name="message" class="form-control input-sm">'))
	                         .append($('<span class="input-group-btn"><button type="button" class="btn btn-primary btn-sm">Send</button></span>'));
	  
	  $footer.append($input);
	  
	  $chatBox.append($heading)
	          .append($body)
	          .append($footer);
	  
	  $chatBox.attr('data-username',username);
	  
	  $chatboxes.push($chatBox);
	  
	  addKeybordEvents($chatBox);
	  
	  
	  
	  if($('div.panel-chat').length)
	  {		  
		 var positions = $('div.panel-chat').last().position(); 
		 $chatBox.css('left',positions.left - ($('div.panel-chat').width() + 10));
	  }
	  
	  $('body').append($chatBox);
	  
	  $closeButton.click(function(){closeChatBox($chatBox);});
	  $heading.click(function(){toggleChatBox($chatBox);});
	  
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
	    }
	  });
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
			  			  
			  console.log($(element).css('left'));
			  	  			  
		  });
		  
		  chatBox.remove();	
		  	  
	  }
	  
  }
  
  function toggleChatBox(chatBox)
  {
	  $('div.panel-body, div.panel-footer', chatBox).toggle();
  }

  $('div.chatbox input').on('input', function() {
    updateTyping();
  });
  
  setUsername();
  


  // Click events
  
  $("[id=chat_with]").on('click', function(e){			  
	  $chatBox = makeChatBox($(this).attr('data-username'));
	  
  });

  // Socket events

  // Whenever the server emits 'login', log the login message
  socket.on('login', function (data) {
    connected = true;
    
    addParticipantsMessage(data);
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