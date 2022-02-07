/* 
*	this file js need jquery 
*	the compressor is also required
*/

define(['jquery', "lns/sb/js/uploaderjs/compressjs/index"], function($, Compress){
	window.lnsImgEditor = {
		placeholderId: '#image-editor-placeholder',
		placeholder: '',
		inputId: '#lnsImgUpload',
		input: '',
		message: '',
		canvas: '',
		image: '',
		aspectRatioWidth: 400,
		aspectRatioHeight: 300,
		prefsize: '',
		file: '',
		jcrop_api: '',
		context: '',
		ajaxUrl: '',
		draganddropcaption: '<p class="uploader-caption-1">Drag a photo here</p><p class="uploader-caption-2">- or -</p><p  class="uploader-caption-3">Select a photo from your computer</p>',
		newFileName: '',
		uploadData: null,

		addCss: function(){
			var css = "<style>.jcrop-holder{direction:ltr;text-align:left;margin: auto;}.jcrop-vline,.jcrop-hline{background:#FFF  url('data:image/gif;base64,R0lGODlhCAAIAJEAAKqqqv///wAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQJCgAAACwAAAAACAAIAAACDZQFCadrzVRMB9FZ5SwAIfkECQoAAAAsAAAAAAgACAAAAg+ELqCYaudeW9ChyOyltQAAIfkECQoAAAAsAAAAAAgACAAAAg8EhGKXm+rQYtC0WGl9oAAAIfkECQoAAAAsAAAAAAgACAAAAg+EhWKQernaYmjCWLF7qAAAIfkECQoAAAAsAAAAAAgACAAAAg2EISmna81UTAfRWeUsACH5BAkKAAAALAAAAAAIAAgAAAIPFA6imGrnXlvQocjspbUAACH5BAkKAAAALAAAAAAIAAgAAAIPlIBgl5vq0GLQtFhpfaIAACH5BAUKAAAALAAAAAAIAAgAAAIPlIFgknq52mJowlixe6gAADs=');font-size:0;position:absolute;}.jcrop-vline{height:100%;width:1px!important;}.jcrop-vline.right{right:0;}.jcrop-hline{height:1px!important;width:100%;}.jcrop-hline.bottom{bottom:0;}.jcrop-tracker{-webkit-tap-highlight-color:transparent;-webkit-touch-callout:none;-webkit-user-select:none;height:100%;width:100%;}.jcrop-handle{background-color:#333;border:1px #EEE solid;font-size:1px;height:7px;width:7px;}.jcrop-handle.ord-n{left:50%;margin-left:-4px;margin-top:-4px;top:0;}.jcrop-handle.ord-s{bottom:0;left:50%;margin-bottom:-4px;margin-left:-4px;}.jcrop-handle.ord-e{margin-right:-4px;margin-top:-4px;right:0;top:50%;}.jcrop-handle.ord-w{left:0;margin-left:-4px;margin-top:-4px;top:50%;}.jcrop-handle.ord-nw{left:0;margin-left:-4px;margin-top:-4px;top:0;}.jcrop-handle.ord-ne{margin-right:-4px;margin-top:-4px;right:0;top:0;}.jcrop-handle.ord-se{bottom:0;margin-bottom:-4px;margin-right:-4px;right:0;}.jcrop-handle.ord-sw{bottom:0;left:0;margin-bottom:-4px;margin-left:-4px;}.jcrop-dragbar.ord-n,.jcrop-dragbar.ord-s{height:7px;width:100%;}.jcrop-dragbar.ord-e,.jcrop-dragbar.ord-w{height:100%;width:7px;}.jcrop-dragbar.ord-n{margin-top:-4px;}.jcrop-dragbar.ord-s{bottom:0;margin-bottom:-4px;}.jcrop-dragbar.ord-e{margin-right:-4px;right:0;}.jcrop-dragbar.ord-w{margin-left:-4px;}.jcrop-light .jcrop-vline,.jcrop-light .jcrop-hline{background:#FFF;filter:alpha(opacity=70)!important;opacity:.70!important;}.jcrop-light .jcrop-handle{-moz-border-radius:3px;-webkit-border-radius:3px;background-color:#000;border-color:#FFF;border-radius:3px;}.jcrop-dark .jcrop-vline,.jcrop-dark .jcrop-hline{background:#000;filter:alpha(opacity=70)!important;opacity:.7!important;}.jcrop-dark .jcrop-handle{-moz-border-radius:3px;-webkit-border-radius:3px;background-color:#FFF;border-color:#000;border-radius:3px;}.solid-line .jcrop-vline,.solid-line .jcrop-hline{background:#FFF;}.jcrop-holder img,img.jcrop-preview{max-width:none;}.lns-image-message,\
			"+lnsImgEditor.placeholderId+" {position: relative;}.imageupload-loader,.canvas_file_picker,.lns-image-action {display:none;}.canvas_file_picker.active {display: block;}.lns-image-message.active, .lns-image-action.active{display:inline;}#lnsImgUpload{display:none;}.imageupload-loader.active {display: block;background: rgba(0,0,0,0.8);position: absolute;width: 100%;height: 100%;top: 0;left: 0;z-index: 999;}.imageupload-loader.active img {display: block;position: absolute;top: 50%;left: 50%;transform: translate3d(-50%, -50%, 0px);-ms-transform: translate3d(-50%, -50%, 0px);}</style>";
			$('head').append(css);
		},

		init: function(){
			lnsImgEditor.placeholder = $(lnsImgEditor.placeholderId);
			lnsImgEditor.addCss();
			lnsImgEditor.renderControls();
			lnsImgEditor.input = $(lnsImgEditor.inputId);
		},

		loadImage: function(input) {
			var i = input;
			lnsImgEditor.file = '';
			if (i.files && i.files[0]) {
				var reader = new FileReader();
				this.canvas = null;
				reader.onload = function(e) {
					lnsImgEditor.image = new Image();
					lnsImgEditor.image.onload = lnsImgEditor.drawImage;
					lnsImgEditor.image.src = e.target.result;
				}
				reader.readAsDataURL(i.files[0]);
				lnsImgEditor.file = i.files[0];
			}
		},

		loadImageFromElement: function(e){
			lnsImgEditor.canvas = null;
			lnsImgEditor.file = '';
			
			lnsImgEditor.image = new Image();
			lnsImgEditor.image.onload = lnsImgEditor.loadImageFromElementHelper;
			lnsImgEditor.image.src = e.src;
			$(".canvas_file_picker").removeClass('active');
			$(".lns-image-action.cancel").addClass('active');
			$(".lns-image-action.crop").addClass('active');
			$(".lns-image-action.rotate").addClass('active');
		},

		loadImageFromElementHelper: function(){
			lnsImgEditor.drawImage();
			var blob = lnsImgEditor.dataURLtoBlob(lnsImgEditor.canvas.toDataURL(lnsImgEditor.file.type));
			var fileOfBlob = new File([blob], lnsImgEditor.rename());
			lnsImgEditor.file = fileOfBlob;
		},

		rename: function(){
			var filename = lnsImgEditor.image.src.replace(/^.*[\\\/]/, '');
			return lnsImgEditor.fileNameGenerator(8) + '_' + filename;
		},

		fileNameGenerator: function(len){
			charSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			var randomString = '';
			for (var i = 0; i < len; i++) {
				var randomPoz = Math.floor(Math.random() * charSet.length);
				randomString += charSet.substring(randomPoz,randomPoz+1);
			}
			return randomString;
		},

		drawImage: function() {
			$(lnsImgEditor.placeholderId + ' #canvas_container').empty();
			$(lnsImgEditor.placeholderId + ' #canvas_container').append("<canvas id=\"canvas\"></canvas>");
			
			lnsImgEditor.canvas = $("#canvas")[0];
			lnsImgEditor.context = lnsImgEditor.canvas.getContext("2d");
			lnsImgEditor.canvas.width = lnsImgEditor.image.width;
			lnsImgEditor.canvas.height = lnsImgEditor.image.height;
			lnsImgEditor.context.drawImage(lnsImgEditor.image, 0, 0);
			lnsImgEditor.startCrop();
		},

		renderControls: function(){
			var controls = '<input id="lnsImgUpload" type="file" name="file"><div id="canvas_container"></div><div class="lns-image-controls clearfix"><div class="canvas_file_picker active"><div class="canvas_file_picker_icon"><i class="fa fa-cloud-upload"></i></div><div class="canvas_file_picker_caption">' + this.draganddropcaption + '</div></div><p class="lns-image-message"></p><button class="btn btn-success lns-image-action crop"><p><i class="fa fa-crop" aria-hidden="true"></i>Crop</p></button><button class="btn btn-success lns-image-action rotate"><p><i class="fa fa-repeat" aria-hidden="true"></i>Rotate</p></button><button class="btn btn-success lns-image-action save"><p><i class="fa fa-cloud-upload" aria-hidden="true"></i>Upload</p></button><button class="btn btn-success lns-image-action cancel"><p>Cancel</p></button></div><div class="imageupload-loader"><img src=" data:image/gif;base64,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"/></div>';
			lnsImgEditor.placeholder.append(controls);
		},

		startCrop: function(){
			var	cmw = 350;
			var ratio = false;
			var arH = parseInt(lnsImgEditor.aspectRatioHeight);
			var arW = parseInt(lnsImgEditor.aspectRatioWidth);
			if(arW == 0 && arH == 0){
				ratio = arW / arH;
			}
			$("#canvas").Jcrop({
				onSelect: lnsImgEditor.selectcanvas,
				onRelease: lnsImgEditor.clearcanvas,
				boxWidth: cmw,
				trueSize: [lnsImgEditor.image.width, lnsImgEditor.image.height],
				aspectRatio: ratio,
			}, function() {
				lnsImgEditor.jcrop_api = this;
				lnsImgEditor.jcrop_api.setSelect([0,0, lnsImgEditor.canvas.width, lnsImgEditor.canvas.height]);
				lnsImgEditor.jcrop_api.enable();
			});
			lnsImgEditor.clearcanvas();
		},
		clearcanvas: function() {
			lnsImgEditor.prefsize = {
				x: 0,
				y: 0,
				w: lnsImgEditor.canvas.width,
				h: lnsImgEditor.canvas.height,
			};
		},

		selectcanvas: function(coords) {
		  	lnsImgEditor.prefsize = {
				x: Math.round(coords.x),
				y: Math.round(coords.y),
				w: Math.round(coords.w),
				h: Math.round(coords.h)
			};
		},
		applyCrop: function() {
			lnsImgEditor.canvas.width = lnsImgEditor.prefsize.w;
			lnsImgEditor.canvas.height = lnsImgEditor.prefsize.h;
			lnsImgEditor.context.drawImage(lnsImgEditor.image, lnsImgEditor.prefsize.x, lnsImgEditor.prefsize.y, lnsImgEditor.prefsize.w, lnsImgEditor.prefsize.h, 0, 0, lnsImgEditor.canvas.width, lnsImgEditor.canvas.height);
			lnsImgEditor.loadCanvasImage();
			$(".lns-image-action.save").addClass('active');
		},
		applyRotate: function() {
			lnsImgEditor.canvas.width = lnsImgEditor.image.height;
			lnsImgEditor.canvas.height = lnsImgEditor.image.width;
			lnsImgEditor.context.clearRect(0, 0, lnsImgEditor.canvas.width, lnsImgEditor.canvas.height);
			lnsImgEditor.context.translate(lnsImgEditor.image.height / 2, lnsImgEditor.image.width / 2);
			lnsImgEditor.context.rotate(Math.PI / 2);
			lnsImgEditor.context.drawImage(lnsImgEditor.image, -lnsImgEditor.image.width / 2, -lnsImgEditor.image.height / 2);
			lnsImgEditor.loadCanvasImage();
		},	
		loadCanvasImage: function(elem){
			lnsImgEditor.image = new Image();
			lnsImgEditor.image.src = lnsImgEditor.canvas.toDataURL(lnsImgEditor.file.type);
			lnsImgEditor.image.onload = lnsImgEditor.drawImage;
		},
		dataURLtoBlob: function(dataURL) {
			var BASE64_MARKER = ';base64,';
			if (dataURL.indexOf(BASE64_MARKER) == -1) {
				var parts = dataURL.split(',');
				var contentType = parts[0].split(':')[1];
				var raw = decodeURIComponent(parts[1]);

				return new Blob([raw], {
					type: contentType
				});
			}
			var parts = dataURL.split(BASE64_MARKER);
			var contentType = parts[0].split(':')[1];
			var raw = window.atob(parts[1]);
			var rawLength = raw.length;
			var uInt8Array = new Uint8Array(rawLength);
			for (var i = 0; i < rawLength; ++i) {
				uInt8Array[i] = raw.charCodeAt(i);
			}

			return new Blob([uInt8Array], {
				type: contentType
			});
		},

		generateFile: function(){
			var blob = lnsImgEditor.dataURLtoBlob(lnsImgEditor.canvas.toDataURL(lnsImgEditor.file.type));
			var fileOfBlob = new File([blob], lnsImgEditor.file.name);
			return fileOfBlob;
		},

		urltoFile: function(dataurl, filename, mimeType){
			mimeType = mimeType || (dataurl.match(/^data:([^;]+);/)||'')[1];
			return (fetch(dataurl)
			.then(function(res){return res.arrayBuffer();})
			.then(function(buf){return new File([buf], filename, {type:mimeType});})
			);
		},

		getMime: function(){
			if(lnsImgEditor.file.type.length > 0){
				return lnsImgEditor.file.type.length;
			} else {
				return 'image/png';
			}
		},

		getFile: function(){
			const compressor = new Compress();
			const files = [lnsImgEditor.generateFile()];
			return compressor.compress(files, {
				size: 4,
				quality: .75
			}).then((results) => {
				const output = results[0];
				var dataurl = 'data:' + lnsImgEditor.getMime() + ';base64,' + output.data;
				return lnsImgEditor.urltoFile(dataurl, lnsImgEditor.file.name, lnsImgEditor.file.type);
			});
		},

		startUpload: function(){
			var a = this;
			$('.imageupload-loader').addClass('active');
			
			formData = new FormData();
			/*var blob = lnsImgEditor.dataURLtoBlob(lnsImgEditor.canvas.toDataURL(lnsImgEditor.file.type));

			var fileOfBlob = new File([blob], lnsImgEditor.rename());*/
			a.getFile()
			.then(function(file){
				formData.append('file', file);
				formData.append('filename', lnsImgEditor.file.name);
				
				$.ajax({
					url: a.ajaxUrl,
					type: "POST",
					data: formData,
					contentType: false,
					cache: false,
					processData: false,
					beforeSend: function(){
						
					},
					success: function(responseData){
						a.uploadData = responseData;
					},
					error: function(data){

					},
					complete: function(data) {
						$('.imageupload-loader').removeClass('active');
					}
				});
			});
		}
	}
	lnsImgEditor.init();

	lnsImgEditor.input.on('change', function() {
		lnsImgEditor.loadImage(this);
	});

	$(".lns-image-action.crop").on('click', function(e) {
		e.preventDefault();
		lnsImgEditor.applyCrop();
	});
	$(".lns-image-action.rotate").on('click', function(e) {
		e.preventDefault();
		lnsImgEditor.applyRotate();
	});
	$(".lns-image-action.save").on('click', function(e) {
		e.preventDefault();
		lnsImgEditor.startUpload();
	});

	$("#lnsImgUpload").on('change', function(e) {
		$(".canvas_file_picker").removeClass('active');
		$(".lns-image-action.cancel").addClass('active');
		$(".lns-image-action.crop").addClass('active');
		$(".lns-image-action.rotate").addClass('active');
	});

	$(".canvas_file_picker").on('click', function(e) {
		$("#lnsImgUpload").click();
	});

	$(".canvas_file_picker").on('dragover', function(e) { 
		e.preventDefault(); 
		e.stopPropagation(); 
	});
	$(".canvas_file_picker").on("drop", function(e) { 
		e.preventDefault(); 
		e.stopPropagation(); 

		lnsImgEditor.loadImage(e.originalEvent.dataTransfer);
		$(".canvas_file_picker").removeClass('active');
		$(".lns-image-action.cancel").addClass('active');
		$(".lns-image-action.crop").addClass('active');
		$(".lns-image-action.rotate").addClass('active');
	});

	$(".lns-image-action.cancel").click(function(e){
		e.preventDefault();
		$(".canvas_file_picker").addClass('active');
		$(".lns-image-action").removeClass('active');
		$(lnsImgEditor.placeholderId + ' #canvas_container').empty();
		lnsImgEditor.file = '';
	});

/* start of croper */
(function(a){a.Jcrop=function(b,c){function i(a){return Math.round(a)+"px"}function j(a){return d.baseClass+"-"+a}function k(){return a.fx.step.hasOwnProperty("backgroundColor")}function l(b){var c=a(b).offset();return[c.left,c.top]}function m(a){return[a.pageX-e[0],a.pageY-e[1]]}function n(b){typeof b!="object"&&(b={}),d=a.extend(d,b),a.each(["onChange","onSelect","onRelease","onDblClick"],function(a,b){typeof d[b]!="function"&&(d[b]=function(){})})}function o(a,b,c){e=l(D),bc.setCursor(a==="move"?a:a+"-resize");if(a==="move")return bc.activateHandlers(q(b),v,c);var d=_.getFixed(),f=r(a),g=_.getCorner(r(f));_.setPressed(_.getCorner(f)),_.setCurrent(g),bc.activateHandlers(p(a,d),v,c)}function p(a,b){return function(c){if(!d.aspectRatio)switch(a){case"e":c[1]=b.y2;break;case"w":c[1]=b.y2;break;case"n":c[0]=b.x2;break;case"s":c[0]=b.x2}else switch(a){case"e":c[1]=b.y+1;break;case"w":c[1]=b.y+1;break;case"n":c[0]=b.x+1;break;case"s":c[0]=b.x+1}_.setCurrent(c),bb.update()}}function q(a){var b=a;return bd.watchKeys
(),function(a){_.moveOffset([a[0]-b[0],a[1]-b[1]]),b=a,bb.update()}}function r(a){switch(a){case"n":return"sw";case"s":return"nw";case"e":return"nw";case"w":return"ne";case"ne":return"sw";case"nw":return"se";case"se":return"nw";case"sw":return"ne"}}function s(a){return function(b){return d.disabled?!1:a==="move"&&!d.allowMove?!1:(e=l(D),W=!0,o(a,m(b)),b.stopPropagation(),b.preventDefault(),!1)}}function t(a,b,c){var d=a.width(),e=a.height();d>b&&b>0&&(d=b,e=b/a.width()*a.height()),e>c&&c>0&&(e=c,d=c/a.height()*a.width()),T=a.width()/d,U=a.height()/e,a.width(d).height(e)}function u(a){return{x:a.x*T,y:a.y*U,x2:a.x2*T,y2:a.y2*U,w:a.w*T,h:a.h*U}}function v(a){var b=_.getFixed();b.w>d.minSelect[0]&&b.h>d.minSelect[1]?(bb.enableHandles(),bb.done()):bb.release(),bc.setCursor(d.allowSelect?"crosshair":"default")}function w(a){if(d.disabled)return!1;if(!d.allowSelect)return!1;W=!0,e=l(D),bb.disableHandles(),bc.setCursor("crosshair");var b=m(a);return _.setPressed(b),bb.update(),bc.activateHandlers(x,v,a.type.substring
(0,5)==="touch"),bd.watchKeys(),a.stopPropagation(),a.preventDefault(),!1}function x(a){_.setCurrent(a),bb.update()}function y(){var b=a("<div></div>").addClass(j("tracker"));return g&&b.css({opacity:0,backgroundColor:"white"}),b}function be(a){G.removeClass().addClass(j("holder")).addClass(a)}function bf(a,b){function t(){window.setTimeout(u,l)}var c=a[0]/T,e=a[1]/U,f=a[2]/T,g=a[3]/U;if(X)return;var h=_.flipCoords(c,e,f,g),i=_.getFixed(),j=[i.x,i.y,i.x2,i.y2],k=j,l=d.animationDelay,m=h[0]-j[0],n=h[1]-j[1],o=h[2]-j[2],p=h[3]-j[3],q=0,r=d.swingSpeed;c=k[0],e=k[1],f=k[2],g=k[3],bb.animMode(!0);var s,u=function(){return function(){q+=(100-q)/r,k[0]=Math.round(c+q/100*m),k[1]=Math.round(e+q/100*n),k[2]=Math.round(f+q/100*o),k[3]=Math.round(g+q/100*p),q>=99.8&&(q=100),q<100?(bh(k),t()):(bb.done(),bb.animMode(!1),typeof b=="function"&&b.call(bs))}}();t()}function bg(a){bh([a[0]/T,a[1]/U,a[2]/T,a[3]/U]),d.onSelect.call(bs,u(_.getFixed())),bb.enableHandles()}function bh(a){_.setPressed([a[0],a[1]]),_.setCurrent([a[2],
a[3]]),bb.update()}function bi(){return u(_.getFixed())}function bj(){return _.getFixed()}function bk(a){n(a),br()}function bl(){d.disabled=!0,bb.disableHandles(),bb.setCursor("default"),bc.setCursor("default")}function bm(){d.disabled=!1,br()}function bn(){bb.done(),bc.activateHandlers(null,null)}function bo(){G.remove(),A.show(),A.css("visibility","visible"),a(b).removeData("Jcrop")}function bp(a,b){bb.release(),bl();var c=new Image;c.onload=function(){var e=c.width,f=c.height,g=d.boxWidth,h=d.boxHeight;D.width(e).height(f),D.attr("src",a),H.attr("src",a),t(D,g,h),E=D.width(),F=D.height(),H.width(E).height(F),M.width(E+L*2).height(F+L*2),G.width(E).height(F),ba.resize(E,F),bm(),typeof b=="function"&&b.call(bs)},c.src=a}function bq(a,b,c){var e=b||d.bgColor;d.bgFade&&k()&&d.fadeTime&&!c?a.animate({backgroundColor:e},{queue:!1,duration:d.fadeTime}):a.css("backgroundColor",e)}function br(a){d.allowResize?a?bb.enableOnly():bb.enableHandles():bb.disableHandles(),bc.setCursor(d.allowSelect?"crosshair":"default"),bb
.setCursor(d.allowMove?"move":"default"),d.hasOwnProperty("trueSize")&&(T=d.trueSize[0]/E,U=d.trueSize[1]/F),d.hasOwnProperty("setSelect")&&(bg(d.setSelect),bb.done(),delete d.setSelect),ba.refresh(),d.bgColor!=N&&(bq(d.shade?ba.getShades():G,d.shade?d.shadeColor||d.bgColor:d.bgColor),N=d.bgColor),O!=d.bgOpacity&&(O=d.bgOpacity,d.shade?ba.refresh():bb.setBgOpacity(O)),P=d.maxSize[0]||0,Q=d.maxSize[1]||0,R=d.minSize[0]||0,S=d.minSize[1]||0,d.hasOwnProperty("outerImage")&&(D.attr("src",d.outerImage),delete d.outerImage),bb.refresh()}var d=a.extend({},a.Jcrop.defaults),e,f=navigator.userAgent.toLowerCase(),g=/msie/.test(f),h=/msie [1-6]\./.test(f);typeof b!="object"&&(b=a(b)[0]),typeof c!="object"&&(c={}),n(c);var z={border:"none",visibility:"visible",margin:0,padding:0,position:"absolute",top:0,left:0},A=a(b),B=!0;if(b.tagName=="IMG"){if(A[0].width!=0&&A[0].height!=0)A.width(A[0].width),A.height(A[0].height);else{var C=new Image;C.src=A[0].src,A.width(C.width),A.height(C.height)}var D=A.clone().removeAttr("id").
css(z).show();D.width(A.width()),D.height(A.height()),A.after(D).hide()}else D=A.css(z).show(),B=!1,d.shade===null&&(d.shade=!0);t(D,d.boxWidth,d.boxHeight);var E=D.width(),F=D.height(),G=a("<div />").width(E).height(F).addClass(j("holder")).css({position:"relative",backgroundColor:d.bgColor}).insertAfter(A).append(D);d.addClass&&G.addClass(d.addClass);var H=a("<div />"),I=a("<div />").width("100%").height("100%").css({zIndex:310,position:"absolute",overflow:"hidden"}),J=a("<div />").width("100%").height("100%").css("zIndex",320),K=a("<div />").css({position:"absolute",zIndex:600}).dblclick(function(){var a=_.getFixed();d.onDblClick.call(bs,a)}).insertBefore(D).append(I,J);B&&(H=a("<img />").attr("src",D.attr("src")).css(z).width(E).height(F),I.append(H)),h&&K.css({overflowY:"hidden"});var L=d.boundary,M=y().width(E+L*2).height(F+L*2).css({position:"absolute",top:i(-L),left:i(-L),zIndex:290}).mousedown(w),N=d.bgColor,O=d.bgOpacity,P,Q,R,S,T,U,V=!0,W,X,Y;e=l(D);var Z=function(){function a(){var a={},b=["touchstart"
,"touchmove","touchend"],c=document.createElement("div"),d;try{for(d=0;d<b.length;d++){var e=b[d];e="on"+e;var f=e in c;f||(c.setAttribute(e,"return;"),f=typeof c[e]=="function"),a[b[d]]=f}return a.touchstart&&a.touchend&&a.touchmove}catch(g){return!1}}function b(){return d.touchSupport===!0||d.touchSupport===!1?d.touchSupport:a()}return{createDragger:function(a){return function(b){return d.disabled?!1:a==="move"&&!d.allowMove?!1:(e=l(D),W=!0,o(a,m(Z.cfilter(b)),!0),b.stopPropagation(),b.preventDefault(),!1)}},newSelection:function(a){return w(Z.cfilter(a))},cfilter:function(a){return a.pageX=a.originalEvent.changedTouches[0].pageX,a.pageY=a.originalEvent.changedTouches[0].pageY,a},isSupported:a,support:b()}}(),_=function(){function h(d){d=n(d),c=a=d[0],e=b=d[1]}function i(a){a=n(a),f=a[0]-c,g=a[1]-e,c=a[0],e=a[1]}function j(){return[f,g]}function k(d){var f=d[0],g=d[1];0>a+f&&(f-=f+a),0>b+g&&(g-=g+b),F<e+g&&(g+=F-(e+g)),E<c+f&&(f+=E-(c+f)),a+=f,c+=f,b+=g,e+=g}function l(a){var b=m();switch(a){case"ne":return[
b.x2,b.y];case"nw":return[b.x,b.y];case"se":return[b.x2,b.y2];case"sw":return[b.x,b.y2]}}function m(){if(!d.aspectRatio)return p();var f=d.aspectRatio,g=d.minSize[0]/T,h=d.maxSize[0]/T,i=d.maxSize[1]/U,j=c-a,k=e-b,l=Math.abs(j),m=Math.abs(k),n=l/m,r,s,t,u;return h===0&&(h=E*10),i===0&&(i=F*10),n<f?(s=e,t=m*f,r=j<0?a-t:t+a,r<0?(r=0,u=Math.abs((r-a)/f),s=k<0?b-u:u+b):r>E&&(r=E,u=Math.abs((r-a)/f),s=k<0?b-u:u+b)):(r=c,u=l/f,s=k<0?b-u:b+u,s<0?(s=0,t=Math.abs((s-b)*f),r=j<0?a-t:t+a):s>F&&(s=F,t=Math.abs(s-b)*f,r=j<0?a-t:t+a)),r>a?(r-a<g?r=a+g:r-a>h&&(r=a+h),s>b?s=b+(r-a)/f:s=b-(r-a)/f):r<a&&(a-r<g?r=a-g:a-r>h&&(r=a-h),s>b?s=b+(a-r)/f:s=b-(a-r)/f),r<0?(a-=r,r=0):r>E&&(a-=r-E,r=E),s<0?(b-=s,s=0):s>F&&(b-=s-F,s=F),q(o(a,b,r,s))}function n(a){return a[0]<0&&(a[0]=0),a[1]<0&&(a[1]=0),a[0]>E&&(a[0]=E),a[1]>F&&(a[1]=F),[Math.round(a[0]),Math.round(a[1])]}function o(a,b,c,d){var e=a,f=c,g=b,h=d;return c<a&&(e=c,f=a),d<b&&(g=d,h=b),[e,g,f,h]}function p(){var d=c-a,f=e-b,g;return P&&Math.abs(d)>P&&(c=d>0?a+P:a-P),Q&&Math.abs
(f)>Q&&(e=f>0?b+Q:b-Q),S/U&&Math.abs(f)<S/U&&(e=f>0?b+S/U:b-S/U),R/T&&Math.abs(d)<R/T&&(c=d>0?a+R/T:a-R/T),a<0&&(c-=a,a-=a),b<0&&(e-=b,b-=b),c<0&&(a-=c,c-=c),e<0&&(b-=e,e-=e),c>E&&(g=c-E,a-=g,c-=g),e>F&&(g=e-F,b-=g,e-=g),a>E&&(g=a-F,e-=g,b-=g),b>F&&(g=b-F,e-=g,b-=g),q(o(a,b,c,e))}function q(a){return{x:a[0],y:a[1],x2:a[2],y2:a[3],w:a[2]-a[0],h:a[3]-a[1]}}var a=0,b=0,c=0,e=0,f,g;return{flipCoords:o,setPressed:h,setCurrent:i,getOffset:j,moveOffset:k,getCorner:l,getFixed:m}}(),ba=function(){function f(a,b){e.left.css({height:i(b)}),e.right.css({height:i(b)})}function g(){return h(_.getFixed())}function h(a){e.top.css({left:i(a.x),width:i(a.w),height:i(a.y)}),e.bottom.css({top:i(a.y2),left:i(a.x),width:i(a.w),height:i(F-a.y2)}),e.right.css({left:i(a.x2),width:i(E-a.x2)}),e.left.css({width:i(a.x)})}function j(){return a("<div />").css({position:"absolute",backgroundColor:d.shadeColor||d.bgColor}).appendTo(c)}function k(){b||(b=!0,c.insertBefore(D),g(),bb.setBgOpacity(1,0,1),H.hide(),l(d.shadeColor||d.bgColor,1),bb.
isAwake()?n(d.bgOpacity,1):n(1,1))}function l(a,b){bq(p(),a,b)}function m(){b&&(c.remove(),H.show(),b=!1,bb.isAwake()?bb.setBgOpacity(d.bgOpacity,1,1):(bb.setBgOpacity(1,1,1),bb.disableHandles()),bq(G,0,1))}function n(a,e){b&&(d.bgFade&&!e?c.animate({opacity:1-a},{queue:!1,duration:d.fadeTime}):c.css({opacity:1-a}))}function o(){d.shade?k():m(),bb.isAwake()&&n(d.bgOpacity)}function p(){return c.children()}var b=!1,c=a("<div />").css({position:"absolute",zIndex:240,opacity:0}),e={top:j(),left:j().height(F),right:j().height(F),bottom:j()};return{update:g,updateRaw:h,getShades:p,setBgColor:l,enable:k,disable:m,resize:f,refresh:o,opacity:n}}(),bb=function(){function k(b){var c=a("<div />").css({position:"absolute",opacity:d.borderOpacity}).addClass(j(b));return I.append(c),c}function l(b,c){var d=a("<div />").mousedown(s(b)).css({cursor:b+"-resize",position:"absolute",zIndex:c}).addClass("ord-"+b);return Z.support&&d.bind("touchstart.jcrop",Z.createDragger(b)),J.append(d),d}function m(a){var b=d.handleSize,e=l(a,c++
).css({opacity:d.handleOpacity}).addClass(j("handle"));return b&&e.width(b).height(b),e}function n(a){return l(a,c++).addClass("jcrop-dragbar")}function o(a){var b;for(b=0;b<a.length;b++)g[a[b]]=n(a[b])}function p(a){var b,c;for(c=0;c<a.length;c++){switch(a[c]){case"n":b="hline";break;case"s":b="hline bottom";break;case"e":b="vline right";break;case"w":b="vline"}e[a[c]]=k(b)}}function q(a){var b;for(b=0;b<a.length;b++)f[a[b]]=m(a[b])}function r(a,b){d.shade||H.css({top:i(-b),left:i(-a)}),K.css({top:i(b),left:i(a)})}function t(a,b){K.width(Math.round(a)).height(Math.round(b))}function v(){var a=_.getFixed();_.setPressed([a.x,a.y]),_.setCurrent([a.x2,a.y2]),w()}function w(a){if(b)return x(a)}function x(a){var c=_.getFixed();t(c.w,c.h),r(c.x,c.y),d.shade&&ba.updateRaw(c),b||A(),a?d.onSelect.call(bs,u(c)):d.onChange.call(bs,u(c))}function z(a,c,e){if(!b&&!c)return;d.bgFade&&!e?D.animate({opacity:a},{queue:!1,duration:d.fadeTime}):D.css("opacity",a)}function A(){K.show(),d.shade?ba.opacity(O):z(O,!0),b=!0}function B
(){F(),K.hide(),d.shade?ba.opacity(1):z(1),b=!1,d.onRelease.call(bs)}function C(){h&&J.show()}function E(){h=!0;if(d.allowResize)return J.show(),!0}function F(){h=!1,J.hide()}function G(a){a?(X=!0,F()):(X=!1,E())}function L(){G(!1),v()}var b,c=370,e={},f={},g={},h=!1;d.dragEdges&&a.isArray(d.createDragbars)&&o(d.createDragbars),a.isArray(d.createHandles)&&q(d.createHandles),d.drawBorders&&a.isArray(d.createBorders)&&p(d.createBorders),a(document).bind("touchstart.jcrop-ios",function(b){a(b.currentTarget).hasClass("jcrop-tracker")&&b.stopPropagation()});var M=y().mousedown(s("move")).css({cursor:"move",position:"absolute",zIndex:360});return Z.support&&M.bind("touchstart.jcrop",Z.createDragger("move")),I.append(M),F(),{updateVisible:w,update:x,release:B,refresh:v,isAwake:function(){return b},setCursor:function(a){M.css("cursor",a)},enableHandles:E,enableOnly:function(){h=!0},showHandles:C,disableHandles:F,animMode:G,setBgOpacity:z,done:L}}(),bc=function(){function f(b){M.css({zIndex:450}),b?a(document).bind("touchmove.jcrop"
,k).bind("touchend.jcrop",l):e&&a(document).bind("mousemove.jcrop",h).bind("mouseup.jcrop",i)}function g(){M.css({zIndex:290}),a(document).unbind(".jcrop")}function h(a){return b(m(a)),!1}function i(a){return a.preventDefault(),a.stopPropagation(),W&&(W=!1,c(m(a)),bb.isAwake()&&d.onSelect.call(bs,u(_.getFixed())),g(),b=function(){},c=function(){}),!1}function j(a,d,e){return W=!0,b=a,c=d,f(e),!1}function k(a){return b(m(Z.cfilter(a))),!1}function l(a){return i(Z.cfilter(a))}function n(a){M.css("cursor",a)}var b=function(){},c=function(){},e=d.trackDocument;return e||M.mousemove(h).mouseup(i).mouseout(i),D.before(M),{activateHandlers:j,setCursor:n}}(),bd=function(){function e(){d.keySupport&&(b.show(),b.focus())}function f(a){b.hide()}function g(a,b,c){d.allowMove&&(_.moveOffset([b,c]),bb.updateVisible(!0)),a.preventDefault(),a.stopPropagation()}function i(a){if(a.ctrlKey||a.metaKey)return!0;Y=a.shiftKey?!0:!1;var b=Y?10:1;switch(a.keyCode){case 37:g(a,-b,0);break;case 39:g(a,b,0);break;case 38:g(a,0,-b);break;
case 40:g(a,0,b);break;case 27:d.allowSelect&&bb.release();break;case 9:return!0}return!1}var b=a('<input type="radio" />').css({position:"fixed",left:"-120px",width:"12px"}).addClass("jcrop-keymgr"),c=a("<div />").css({position:"absolute",overflow:"hidden"}).append(b);return d.keySupport&&(b.keydown(i).blur(f),h||!d.fixedSupport?(b.css({position:"absolute",left:"-20px"}),c.append(b).insertBefore(D)):b.insertBefore(D)),{watchKeys:e}}();Z.support&&M.bind("touchstart.jcrop",Z.newSelection),J.hide(),br(!0);var bs={setImage:bp,animateTo:bf,setSelect:bg,setOptions:bk,tellSelect:bi,tellScaled:bj,setClass:be,disable:bl,enable:bm,cancel:bn,release:bb.release,destroy:bo,focus:bd.watchKeys,getBounds:function(){return[E*T,F*U]},getWidgetSize:function(){return[E,F]},getScaleFactor:function(){return[T,U]},getOptions:function(){return d},ui:{holder:G,selection:K}};return g&&G.bind("selectstart",function(){return!1}),A.data("Jcrop",bs),bs},a.fn.Jcrop=function(b,c){var d;return this.each(function(){if(a(this).data("Jcrop")){if(
b==="api")return a(this).data("Jcrop");a(this).data("Jcrop").setOptions(b)}else this.tagName=="IMG"?a.Jcrop.Loader(this,function(){a(this).css({display:"block",visibility:"hidden"}),d=a.Jcrop(this,b),a.isFunction(c)&&c.call(d)}):(a(this).css({display:"block",visibility:"hidden"}),d=a.Jcrop(this,b),a.isFunction(c)&&c.call(d))}),this},a.Jcrop.Loader=function(b,c,d){function g(){f.complete?(e.unbind(".jcloader"),a.isFunction(c)&&c.call(f)):window.setTimeout(g,50)}var e=a(b),f=e[0];e.bind("load.jcloader",g).bind("error.jcloader",function(b){e.unbind(".jcloader"),a.isFunction(d)&&d.call(f)}),f.complete&&a.isFunction(c)&&(e.unbind(".jcloader"),c.call(f))},a.Jcrop.defaults={allowSelect:!0,allowMove:!0,allowResize:!0,trackDocument:!0,baseClass:"jcrop",addClass:null,bgColor:"black",bgOpacity:.6,bgFade:!1,borderOpacity:.4,handleOpacity:.5,handleSize:null,aspectRatio:0,keySupport:!0,createHandles:["n","s","e","w","nw","ne","se","sw"],createDragbars:["n","s","e","w"],createBorders:["n","s","e","w"],drawBorders:!0,dragEdges
:!0,fixedSupport:!0,touchSupport:null,shade:null,boxWidth:0,boxHeight:0,boundary:2,fadeTime:400,animationDelay:20,swingSpeed:3,minSelect:[0,0],maxSize:[0,0],minSize:[0,0],onChange:function(){},onSelect:function(){},onDblClick:function(){},onRelease:function(){}}})(jQuery);


});