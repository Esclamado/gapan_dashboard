define("lns/sb/admin/js/jquery.validator",['jquery','lns/sb/admin/js/lib/jquery.validate'], function($,validator) {
    return function(){
        this.init = function(){
            $.validator.setDefaults({
                highlight: function(element) {
                    $(element)
                    .closest('.form-group input')
                    .addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element)
                    .closest('.form-group input')
                    .removeClass('is-invalid')
                    .addClass('is-valid');
                }
            });
        }
    }
});