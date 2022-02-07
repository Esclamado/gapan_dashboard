define(['jquery'], function($){

	var confirm = new function(){
		this.el;

		this.showModal = function(el){
			this.el = el;

			$('#ModalLongTitle').text(this.el.dataset.title);
			$('#confirmQuestion').html(this.el.dataset.question);
			$('#confirmButton').text(this.el.dataset.buttontext);
			$('#confirm_id').val(this.el.dataset.id);
			$('#confirm-form-action').attr('action', this.el.dataset.action);
		}

	}

	$( document ).ajaxComplete(function() {
		$('.delete-button').on('click', function(e){
			e.preventDefault();
			confirm.showModal(this);
		});
	});

	$('.no-ajax.delete-button').on('click', function(e){
		e.preventDefault();
		confirm.showModal(this);
	});
});