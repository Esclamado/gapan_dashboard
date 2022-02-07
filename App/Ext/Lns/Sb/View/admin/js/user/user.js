define(['jquery','jqueryui','chosen','lns/sb/admin/js/address'], function($,jqueryui,chosen,a) {
    return function(){
        var f = new a();
        /* e.ckEditor.initEditor('#ckEditor');   */ 
        $(".chosen-select").chosen(); 
         
        /* for address */
        var code = $('#inputRegion').find(':selected').val();
        f.address.getState(code).then(
            function(){
                var thiscode = $('#inputState').find(':selected').data('code');
                f.address.getCity(thiscode);
            }
        );
        $('#inputRegion').on('change', function(){
			var code = $(this).find(':selected').val();
			f.address.getState(code);
        });
        $('#inputState').on('change', function(){
            var thiscode = $(this).find(':selected').data('code');
			f.address.getCity(thiscode);
		});
		$('#inputCity').on('change', function(){
			var postal = $(this).find(':selected').attr('data-postal');
			if(postal === undefined){
				postal = '';
            }
			document.getElementById('inputPostalCode').value = postal;
		});
    }
});