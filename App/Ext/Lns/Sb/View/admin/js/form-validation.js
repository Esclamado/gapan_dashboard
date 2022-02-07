!function(){"use strict";window.addEventListener("load",function(){
    var e=document.getElementById("needs-validation");
    e.addEventListener(
        "submit",
        function(t){
            !1===e.checkValidity()&&(t.preventDefault(),
            t.stopPropagation()),
            e.classList.add("was-validated")
        },!1)
},!1)}()