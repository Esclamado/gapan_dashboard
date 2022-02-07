define("lns/sb/admin/js/htmleditor",['jquery','jqueryui','bootstrap','ckeditor'], function($,jqueryui,bootstrap,ClassicEditor) {
    return function(){
        this.ckEditor = {
            initEditor : function(_input){
                ClassicEditor
                .create( document.querySelector(_input) )
                .then( editor => {
                    console.log( editor );
                } )
                .catch( error => {
                    console.error( error );
                } );
            }
        }
    }
});