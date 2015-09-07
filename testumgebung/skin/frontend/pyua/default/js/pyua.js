/* Hier wird nun das Problem mit den doppelten * geloest */
    function ReplaceStars(targetID, replVar, replVarTo){
        var test        =   document.getElementById(targetID).innerHTML
        var repl        =   test;
        /*var replVar     =   "<span class=\"required\">*</span>";
        var replVarTo   =   "<span class=\"required\"></span>";*/
        var anz         =  repl.indexOf(replVar);
        for (var i = 0; i < anz; i++) {
            repl     =  repl.replace(replVar, replVarTo);
        }
        document.getElementById(targetID).innerHTML = repl;
    }