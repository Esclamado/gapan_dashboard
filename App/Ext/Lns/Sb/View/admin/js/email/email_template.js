define(['jquery','jqueryui','chosen','lns/sb/admin/js/htmleditor'], function($,jqueryui,chosen,h) {
    return function(){
        var e = new h();
        e.ckEditor.initEditor('#ckEditor');   
        $(".chosen-select").chosen(); 
    }
});