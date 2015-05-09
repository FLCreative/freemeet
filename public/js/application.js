jQuery(document).ready(function() {
	  
	
	App.init();
	  $.ajaxSetup({ cache: true });
	  $.getScript('//connect.facebook.net/fr_FR/sdk.js', function(){
	    FB.init({
	      appId: '818411338205601',
	      version    : 'v2.2'
	    });
	    FB.getLoginStatus(function(response) {
	    	console.log(response);
	    });
	    
	    $("#import").on('click',function(){
	    FB.login(function(response) {
	    	  console.log(response);
	    	  if (response.status === 'connected') {
	    	    // Logged into your app and Facebook.
	    		  FB.api(
	    				    "/me/albums",
	    				    function (response) {
	    				      if (response && !response.error) {
	    				        console.log(response);
	    				      }
	    				    }
	    				);
	    		  
	    	  } else if (response.status === 'not_authorized') {
	    	    // The person is logged into Facebook, but not your app.
	    	  } else {
	    	    // The person is not logged into Facebook, so we're not sure if
	    	    // they are logged into this app or not.
	    	  }
	    },{scope: 'user_photos'});
	    });
	  });

	//------FLEXSLIDER homepage------------------
	
	$('#main_flexslider').flexslider({
	namespace: "flex-",             //{NEW} String: Prefix string attached to the class of every element generated by the plugin
	selector: ".slides > li",       //{NEW} Selector: Must match a simple pattern. '{container} > {slide}' -- Ignore pattern at your own peril
	animation: "fade",              //String: Select your animation type, "fade" or "slide"
	easing: "swing",               //{NEW} String: Determines the easing method used in jQuery transitions. jQuery easing plugin is supported!
	direction: "horizontal",        //String: Select the sliding direction, "horizontal" or "vertical"
	reverse: false,                 //{NEW} Boolean: Reverse the animation direction
	animationLoop: true,             //Boolean: Should the animation loop? If false, directionNav will received "disable" classes at either end
	smoothHeight: false,            //{NEW} Boolean: Allow height of the slider to animate smoothly in horizontal mode
	startAt: 0,                     //Integer: The slide that the slider should start on. Array notation (0 = first slide)
	slideshow: true,                //Boolean: Animate slider automatically
	slideshowSpeed: 10000,           //Integer: Set the speed of the slideshow cycling, in milliseconds
	animationSpeed: 600,            //Integer: Set the speed of animations, in milliseconds
	initDelay: 0,                   //{NEW} Integer: Set an initialization delay, in milliseconds
	randomize: false,               //Boolean: Randomize slide order
	 
	// Usability features
	pauseOnAction: true,            //Boolean: Pause the slideshow when interacting with control elements, highly recommended.
	pauseOnHover: true,            //Boolean: Pause the slideshow when hovering over slider, then resume when no longer hovering
	useCSS: true,                   //{NEW} Boolean: Slider will use CSS3 transitions if available
	touch: true,                    //{NEW} Boolean: Allow touch swipe navigation of the slider on touch-enabled devices
	video: false,                   //{NEW} Boolean: If using video in the slider, will prevent CSS3 3D Transforms to avoid graphical glitches
	 
	// Primary Controls
	controlNav: true,               //Boolean: Create navigation for paging control of each clide? Note: Leave true for manualControls usage
	directionNav: true,             //Boolean: Create navigation for previous/next navigation? (true/false)
	prevText: "Previous",           //String: Set the text for the "previous" directionNav item
	nextText: "Next",               //String: Set the text for the "next" directionNav item
	 
	// Secondary Navigation
	keyboard: true,                 //Boolean: Allow slider navigating via keyboard left/right keys
	multipleKeyboard: false,        //{NEW} Boolean: Allow keyboard navigation to affect multiple sliders. Default behavior cuts out keyboard navigation with more than one slider present.
	mousewheel: false,              //{UPDATED} Boolean: Requires jquery.mousewheel.js (https://github.com/brandonaaron/jquery-mousewheel) - Allows slider navigating via mousewheel
	pausePlay: false,               //Boolean: Create pause/play dynamic element
	pauseText: 'Pause',             //String: Set the text for the "pause" pausePlay item
	playText: 'Play',               //String: Set the text for the "play" pausePlay item
	 
	// Special properties
	controlsContainer: "",          //{UPDATED} Selector: USE CLASS SELECTOR. Declare which container the navigation elements should be appended too. Default container is the FlexSlider element. Example use would be ".flexslider-container". Property is ignored if given element is not found.
	manualControls: "",             //Selector: Declare custom control navigation. Examples would be ".flex-control-nav li" or "#tabs-nav li img", etc. The number of elements in your controlNav should match the number of slides/tabs.
	sync: "",                       //{NEW} Selector: Mirror the actions performed on this slider with another slider. Use with care.
	asNavFor: "",                   //{NEW} Selector: Internal property exposed for turning the slider into a thumbnail navigation for another slider
	 
	// Carousel Options
	itemWidth: 0,                   //{NEW} Integer: Box-model width of individual carousel items, including horizontal borders and padding.
	itemMargin: 0,                  //{NEW} Integer: Margin between carousel items.
	minItems: 0,                    //{NEW} Integer: Minimum number of carousel items that should be visible. Items will resize fluidly when below this.
	maxItems: 0,                    //{NEW} Integer: Maxmimum number of carousel items that should be visible. Items will resize fluidly when above this limit.
	move: 0,                        //{NEW} Integer: Number of carousel items that should move on animation. If 0, slider will move all visible items.
	 
	// Callback API
	start: function(){
		jQuery('.flex-active-slide .container .carousel-caption').addClass('show');
		},            //Callback: function(slider) - Fires when the slider loads the first slide
	before: function(){
		jQuery('.flex-active-slide .container .carousel-caption').removeClass('show');
		},           //Callback: function(slider) - Fires asynchronously with each slider animation
	after: function(slider){
		jQuery('.flex-active-slide .container .carousel-caption').addClass('show');
		},            //Callback: function(slider) - Fires after each slider animation completes
	end: function(){},              //Callback: function(slider) - Fires when the slider reaches the last slide (asynchronous)
	added: function(){},            //{NEW} Callback: function(slider) - Fires after a slide is added
	removed: function(){}           //{NEW} Callback: function(slider) - Fires after a slide is removed
	
		});
	
	$("#register").on("click",function(e){
		e.preventDefault();
		var button = $(this);
		$.post( "/register", $("#registerForm").serialize(), function( data ) {
			
			button.html('Validation en cours...');
			
			if(data.status == "success") 
			{
				button.html('<span class="glyphicon glyphicon-ok"></span> Inscription validée').unbind();
				
				button.popover({title:'Inscription réussie !', content:'Un email de confirmation vient de vous être envoyé pour activer votre compte.', placement:'top'});
				button.popover('show');
			}
			else
			{
				var count = 1;
				
				$.each(data.messages,function(obj,errors){				
					$('#registerForm input[name='+obj+']').closest('div.form-group').addClass('has-error');
					
					var element = $('#registerForm input[name='+obj+']');
					
					$.each(errors, function() {
						
						if(count == 1)
						{
							element.tooltip({title: this,placement: "top", trigger: 'manual'});
							element.tooltip('show');
						}
						else
						{
							element.tooltip({title: this,placement: "top", trigger: 'hover'});
						}
						
						element.keydown(function() {
							element.tooltip('destroy');
							element.unbind();
	
							element.closest('div.form-group').removeClass('has-error');
						});
		
					});
									
					button.html('Valider mon inscription');
					
					count++;
					
				});
			}
			
		});
	});
	
	$("#edit_password").on("click",function(e){
		$.post('/account/edit-password', $('form#editPassword').serialize(), function(r){
			console.log(r);
			if(r.status == 'success')
			{				
				$('form#editPassword input').val("");
				$.toaster({ priority : 'success', title : '<span class="glyphicon glyphicon-ok"></span>', message : 'Votre mot de passe a été modifié' });
			}
			else
			{
				$.toaster({ priority : 'danger', title : '<span class="glyphicon glyphicon-remove"></span>', message : 'Une erreur est survenue !' });
			}
		});
	});
	
	$("#add_favorite").on("click",function(e){
		e.preventDefault();
		$.post($(this).attr('href'), {user:$(this).attr('data-id')}, function(r){
			console.log(r);
			if(r.status == 'success')
			{				
				$.toaster({ priority : 'success', message : 'Votre mot de passe a bien été modifié' });
			}
			else
			{
				$.toaster({ priority : 'danger', title : '<span class="glyphicon glyphicon-remove"></span>', message : r.message });
			}

		});
	});
	
	$("#delete_favorite").on("click",function(e){
		e.preventDefault();
		$.post("/user/delete-favorite", {user:$(this).attr('data-id')}, function(r){
			if(r.status == 'success')
			{				
				$.toaster({ priority : 'success', message : 'Pseudo a bien été supprimé de vos favoris' });
				
				$(this).parentsUntil('div.row').remove();
			}
			else
			{
				$.toaster({ priority : 'danger', title : '<span class="glyphicon glyphicon-remove"></span>', message : r.message });
			}

		});
	});
	
	$("#send_flash").on("click",function(e){
		e.preventDefault();
		$.post('/user/flash', {user:$(this).attr('data-id')}, function(r){
			console.log(r);
			if(r.status == 'success')
			{				
				$.toaster({ priority : 'success', title : '<span class="glyphicon glyphicon-ok"></span>', message : 'Votre flash a bien été envoyé !' });
			}
			else
			{
				$.toaster({ priority : 'danger', title : '<span class="glyphicon glyphicon-remove"></span>', message : r.message });
			}

		});
	});
	
	if($("ul.chats").length)
	{
		setInterval(function() {
			$("span[class=date-time]").each(function() {
				$(this).text(moment($(this).attr("data-time")).fromNow());
			});
			}, 1000 * 60 * 1);

			
	}
		
	var test = $("input[id=add_photo]").fileinput({
		allowedFileTypes: ["image"],
	    allowedFileExtensions: ["jpg", "png"],
	    browseClass: "btn btn-success btn-xs btn-block",
	    browseLabel: "Télécharger une photo",
		browseIcon: '<span class="glyphicon glyphicon-upload"></span> ',
		elErrorContainer : '#uploadMessages',
		uploadUrl: '/photo/add',
		showCaption: false,
		showRemove: false,
		showUpload: false,
		showCancel: false,
		showPreview: false,
	});
	
	test.on('fileloaded', function(event, file, previewId, index, reader) {
		test.fileinput('uploadBatch');
    });
	
	test.on('filebatchuploadsuccess', function(event, data, previewId, index) {
		
		var filename = data.response['filename'];
		var photoId = data.response['photo_id'];
		var picture = $('<img>').attr('src','/photos/pendings/'+filename);
		var submit = $('<button>').attr('class','btn btn-success btn-xs btn-block').html('Valider');
		var thumbnail = $(this).parentsUntil('div.row').find('div.thumbnail');
		
		thumbnail.html(picture);						
		thumbnail.append($("#controls"));
		
		$(this).closest('p').html(submit);
		 		
		picture.on('load', function(){
	        // Initialize plugin (with custom event)
	        picture.guillotine({width: 250, height: 250});
	        picture.guillotine('fit');
	        // Bind button actions
	        $('#rotate_left').click(function(){ picture.guillotine('rotateLeft'); });
	        $('#rotate_right').click(function(){ picture.guillotine('rotateRight'); });
	        $('#fit').click(function(){ picture.guillotine('fit'); });
	        $('#zoom_in').click(function(){ picture.guillotine('zoomIn'); });
	        $('#zoom_out').click(function(){ picture.guillotine('zoomOut'); });
	        
	        submit.on('click',function(e){
	        	e.preventDefault();
	        	$.post('/photo/crop',{crop:picture.guillotine('getData'),photoId:photoId},function(data){
	        		
	        		var deleteBtn = $('<a>').attr('class','btn btn-danger btn-xs btn-block')
	        		                        .attr('href','/photo/delete/'+photoId)
	        		                        .html('Supprimer la photo');
	        		
	        		thumbnail.find('#controls').remove();
	        		thumbnail.parent().find('p').html(deleteBtn);
	        		
	        		var d = new Date();
	        		var crop = $('<img>').attr('src','/photos/pendings/'+filename+'?'+d.getTime());
	        		
	        		thumbnail.html(crop);
	        	});
	        });

	    });
		
		
		//$("#crop").modal('show');
		
	});
	
	//With JQuery
	$("#ex6, #ex2").slider();
	$("#ex6").on("slide", function(slideEvt) {
		$("#ex6SliderVal").text(slideEvt.value);
	});

});

var handleSlimScroll = function() {
    "use strict";
    
	console.log('test');
    
    $("[data-scrollbar=true]").each(function() {
        generateSlimScroll($(this));
    });
};
var generateSlimScroll = function(e) {
	
	var t = $(e).attr("data-height");
    t = !t ? $(e).height() : t;
    var n = {
        height: t,
        start: 'bottom',
        alwaysVisible: true
    };
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        n.wheelStep = 3;
        n.touchScrollStep = 100;
    }
    $(e).slimScroll(n);
};

var App = function() {
    "use strict";
    return {
        init: function() {
            handleSlimScroll();

        }
    };
}();

$(window).load(function() {
	  // The slider being synced must be initialized first
	  $('#carousel').flexslider({
	    animation: "slide",
	    controlNav: false,
	    animationLoop: false,
	    slideshow: false,
	    itemWidth: 80,
	    itemMargin: 5,
	    asNavFor: '#slider'
	  });
	   
	  $('#slider').flexslider({
	    animation: "fade",
	    controlNav: false,
	    animationLoop: false,
	    slideshow: false,
	    sync: "#carousel"
	  });
	});