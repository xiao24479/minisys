	$("#dialog").remove();
	function dialog(title, html, width, height){
			$("#dialog").remove();
			$('body').append("<div id='dialog'><div class='dialog-top'><div class='dialog-title'>"+title+"</div><span class='close-icon' >✖</span></div><div class='dialog-content'>"+html+"</div></div>");
			
			$('.close-icon').mousedown(function(e){
				e.stopPropagation();
				$("#dialog").remove();
			});	
			
			$('.dialog-content').css({
				width: width + 'px',
				maxHeight: height + 'px',
			});
			
			var move = false;
			var maxW = $(window).width();
			var maxH = $(window).height();

			var w = $('#dialog').outerWidth();
			var h = $('#dialog').outerHeight();

			var l = (maxW - width)/2;
			var t = (maxH - height)/2;
	
			$('#dialog').css({
				left: l + 'px',
				top: t + $(window).scrollTop() + 'px'
			})
			
			$(window).scroll(function(e){
				$('#dialog').css({
					top: parseInt(t+e.currentTarget.scrollY) + 'px'
				});
				maxH = e.currentTarget.outerHeight + e.currentTarget.scrollY - 100;
			})
			
			$('.dialog-top').mousedown(function(ed){
				var offx = ed.clientX - $(this).offset().left;
				var offy = ed.clientY - $(this).offset().top;
				move = true;
				
	//			console.log($('#dialog').offset())
				
				$(window).mousemove(function(em) {
					var x = em.clientX - offx ;
					var y = em.clientY - offy ;
					
					if(x < 0){
						x = 0;
					}else if(x > maxW - w){
						x = maxW - w;
					}
					
					if(y < 60){
						y = 60;
					}else if(y > maxH - h){
						y = maxH - h;
					}		
					
					if(move == true){
						$('#dialog').css({
							top: y + 'px',
							left: x + 'px'
						})
	//					console.log($('#dialog').offset());
					}
				})
				
				$(window).mouseup(function() {
					move = false;
				})
				
			})
	}

