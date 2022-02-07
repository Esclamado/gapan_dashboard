define(['jquery'], function($){

	var _iu = new function(){
		this.placeholders = [];
		this.files = {};
		this.inputNames = [];
		this.draganddropcaption = '<p class="uploader-caption-1">Drag a photo here</p>';
		this.url = '';

		this.setUrl = function(url){
			this.url = url;
			return this;
		};

		this.setPlaceholder = function(placeholder){
			this.placeholders.push(placeholder);
			return this;
		};

		this.setInputName = function(inputName){
			this.inputNames.push(inputName);
			return this;
		};

		this.render = function(){
			var a = this;
			var _ph = this.placeholders;

			var dropzone = '';
			for (index = 0; index < _ph.length; ++index) {
				var ph = $(_ph[index]);

				ph.addClass('iu iu_' + index);

				dropzone = '<div id="canvas_container_ui_'+index+'"></div><div class="lns-image-controls clearfix"><div class="canvas_file_picker active" data-index="'+index+'"><div class="canvas_file_picker_icon"><i class="fa fa-cloud-upload"></i></div><div class="canvas_file_picker_caption">' + this.draganddropcaption + '</div><input id="imgUpload_iu_'+index+'" style="display: none;" type="file" data-filecount="'+index+'" name="files[]"></div>';

				ph.html(dropzone);
			}

			$(".canvas_file_picker").on('dragover', function(e) { 
				e.preventDefault(); 
				e.stopPropagation();
				$(this).addClass('dragover');
			});
			$(".canvas_file_picker").on('dragleave', function(e) { 
				$(this).removeClass('dragover');
			});
			
			$(".canvas_file_picker").on("drop", function(e) { 
				e.preventDefault(); 
				e.stopPropagation(); 
				a.loadImage(e, this);
				$(this).removeClass('dragover');
			});
		};

		this.loadImage = function(e, el){
			var a = this;
			var files = e.originalEvent.dataTransfer.files;

			this.files['file_' + el.dataset.index] = files[0];
		    
		    for( var i = 0; i < files.length; i++ ) {
		        var reader = new FileReader();
		        reader.onload = function(_e){
		            a.displayResult(_e, el);
		        };
		        reader.readAsDataURL(files[i]);
		    }
		};

		this.displayResult = function(e, el){
			var index = el.dataset.index;
			var canvas = $('#canvas_container_ui_' + index);

			var html = '<div class="image-placeholder">';
	            html = '<img src="'+e.target.result+'" style="max-width: 150px;" />';
			html += '</div>';
			canvas.html(html);
		};

		this.startUpload = function(){
			var a = this;
			var formData = new FormData();

			for (var i = 0; i < this.inputNames.length; i++) {
				formData.append(this.inputNames[i], this.files['file_' + i]);
			}
			formData.append('isAjax', '1');

			$.ajax({
				url: a.url,
				type: "POST",
				data: formData,
				contentType: false,
				cache: false,
				processData: false,
				beforeSend: function(){
					$('#loader').removeClass('fadeOut');
					$('#loader').addClass('fadeIn');
				},
				success: function(responseData){
					
				},
				error: function(data){
					
				},
				complete: function(data) {
					location.reload();
					$('#loader').addClass('fadeOut');
					$('#loader').removeClass('fadeIn');
				}
			});
		}
	}

	return _iu;
});