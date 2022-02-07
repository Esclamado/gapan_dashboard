define(['jquery','jqueryui','bootstrap','datatables.net'], function($,jqueryui,bootstrap,DataTable) {
/*define(['jquery','jqueryui','bootstrap','datatables',''], function($,jqueryui,bootstrap,DataTable) {*/
    return function(){
        this.var = {
            processing: null,
            serverSide: null,
            ordering: null,
            ajax: null,
            start: '0',
            limit: null,
            table: null,
            scrollX: false
        };

        this.setVar = function(key, val){
            this.var[key] = val;
            return this;
        };

        this.getVar = function(_type, _default){
            var b = this.var[_type];
            if(b.length > 0){
                return b;
            } else {
                return _default;
            }
        }

        this.init = function(){
            a = this;

            var tableSettings = {
                "processing" : a.getVar('processing', true),
                "serverSide" : a.getVar('serverSide', true),
                "ordering": a.getVar('ordering', true),
                "ajax": {
                    "url": a.getVar('ajax', '')
                },
                "pageLength": a.getVar('limit', '10'),
                'displayStart': a.getVar('start', '0'),
                "scrollX": a.getVar('scrollX', true),
            };

            var table = $('#myTable').DataTable(tableSettings);
            a.setVar('table', table);
        }

        this.filter = function(arg){
            var new_url = a.getVar('ajax', '')+'?'+arg;
            this.var['table'].ajax.url(new_url).load().draw();
        }

        /*this.ajaxComplete = function(data){
            var url = new URL(data.url);
            var c = url.searchParams.get('start');
            console.log(c);
        }*/
    }
});