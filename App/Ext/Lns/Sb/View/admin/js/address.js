define("lns/sb/admin/js/address",['jquery','jqueryui'], function($,jqueryui) {
    return function(){
		this.address = {
			getState : function(val) {
				var current_state = $('#current_province').val();
				return $.ajax({
					type: "POST",
					url: "http://sb.dv/admin_6cz6/users/address/getstate",
					data: {region_id: val, current: current_state},
					dataType: 'json',
					success: function(data){
                        $("#inputState").html(data.option);
                        
                        $("#inputState").trigger("chosen:updated");
                        $("#inputState").trigger("liszt:updated");

						document.getElementById("inputState").disabled = false;
						return true;
					},
					error: function(data) {                         
						console.log(data);
					}
				});
			},
			getCity : function(code){
				var current_city = $('#current_municipal').val();
				$.ajax({
					type: "POST",
					url: "http://sb.dv/admin_6cz6/users/address/getcity",
					data: {state_id : code, current: current_city},
					dataType: 'json',
					success: function(data){
						$("#inputCity").html(data);
						document.getElementById("inputCity").disabled = false;
						$("#inputCity").trigger("chosen:updated");
						$("#inputCity").trigger("liszt:updated");
						
						
					},
					error: function(data) {                         
						console.log(data);
					}
				});
			}
		}
	}
});
