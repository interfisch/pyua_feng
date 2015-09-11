    
    /*JQuery Main Var define*/
    var $j = jQuery.noConflict(); 

   /*
        Description: Diese Funktion ersetzt werte innerhalb einer Class oder ID
        Variablen:
            targetID   -    Je nach Einstellung die Class oder ID welche eingelesen wird
            type       -    1 = ID | 2 = Class
            pos        -    Bei ID egal welcher Wert, bei Class kommt hier die
                            Position der Class rein, als das wievielte Vorkommen der
                            Class Berücksichtigt werden soll
            replVar    -    Dieser Wert soll ersetzt werden
            replVarTo  -    Dieser Wert soll eingesetzt werden
    */
    function ReplaceStars(targetID, type, pos, replVar, replVarTo){
        if(type === 1){
            var test        =   document.getElementById(targetID).innerHTML
            var repl        =   test;
        }else{
            var test        =   document.getElementsByClassName(targetID)
            var repl        =   test[pos].innerHTML;  
        }
        
        /*var replVar     =   "<span class=\"required\">*</span>";
        var replVarTo   =   "<span class=\"required\"></span>";*/
        var anz         =  repl.indexOf(replVar);
        for (var i = 0; i < anz; i++) {
            repl     =  repl.replace(replVar, replVarTo);
        }
        
        if(type === 1){
            document.getElementById(targetID).innerHTML = repl;
        }else{
            var runElem     =   document.getElementsByClassName(targetID)
            test[pos].innerHTML = repl; 
        }        
    }

    $j(document).ready(function(){
        /* NUR INDEX - STARTSEITE */
        if($j("body").hasClass('cms-index-index')){
            /* Weiterlesen für den Abwrackprämien post ersetzen!! */
            ReplaceStars("news-block", 2, 0, "<a href=\"http://www.pyua.de/index.php/news/hol-dir-jetzt-die-abwrackpraemie\">", "<a href=\"http://www.pyua.de/index.php/catalog/category/view/s/abwrackpraemie/id/78/\">")
        }

        /* NUR CHECKOUT */
        if($j("body").hasClass('checkout-onepage-index')){
            /* Rechnungsadresse im Checkout ersetzen */
            $j(document).mouseover(function(){
                if($j(".checkout-onepage-index #opc-billing #checkout-step-billing").is(":visible") 
                || $j(".checkout-onepage-index #opc-shipping #checkout-step-shipping").is(":visible")){            
                    $j(".checkout-onepage-index #opc-billing").addClass("active");
                } else{            
                    $j(".checkout-onepage-index #opc-billing").removeClass("active");
                }
            });
        }
        
        /* LINKS AUF ALLEN SEITEN DEAKTIVIEREN */
        $j("#header-nav #nav .nav-primary li.level0.nav-1 a.level0").click(function(e){e.preventDefault();});
        $j("#header-nav #nav .nav-primary li.level0.nav-1 li.level1.nav-1-1 a.level1").click(function(e){e.preventDefault();});
        $j("#header-nav #nav .nav-primary li.level0.nav-1 li.level1.nav-1-2 a.level1").click(function(e){e.preventDefault();});
        $j("#header-nav #nav .nav-primary li.level0.nav-1 li.level1.nav-1-3 a.level1").click(function(e){e.preventDefault();});
        $j("#leftmenu #nav .nav-primary li.level0.nav-1 a.level0").click(function(e){e.preventDefault();});
        $j("#leftmenu #nav .nav-primary li.level0.nav-1 li.level1.nav-1-1 a.level1").click(function(e){e.preventDefault();});
        $j("#leftmenu #nav .nav-primary li.level0.nav-1 li.level1.nav-1-2 a.level1").click(function(e){e.preventDefault();});
        $j("#leftmenu #nav .nav-primary li.level0.nav-1 li.level1.nav-1-3 a.level1").click(function(e){e.preventDefault();});
        
        $j("#header-nav #nav .nav-primary li.level0.nav-2 a.level0").click(function(e){e.preventDefault();});
        $j("#header-nav #nav .nav-primary li.level0.nav-2 li.level1.nav-2-1 a.level1").click(function(e){e.preventDefault();});
        $j("#header-nav #nav .nav-primary li.level0.nav-2 li.level1.nav-2-2 a.level1").click(function(e){e.preventDefault();});
        $j("#header-nav #nav .nav-primary li.level0.nav-2 li.level1.nav-2-3 a.level1").click(function(e){e.preventDefault();});        
        $j("#leftmenu #nav .nav-primary li.level0.nav-2 a.level0").click(function(e){e.preventDefault();});
        $j("#leftmenu .nav-primary li.level0.nav-2 li.level1.nav-2-1 a.level1").click(function(e){e.preventDefault();});
        $j("#leftmenu .nav-primary li.level0.nav-2 li.level1.nav-2-2 a.level1").click(function(e){e.preventDefault();});
        $j("#leftmenu .nav-primary li.level0.nav-2 li.level1.nav-2-3 a.level1").click(function(e){e.preventDefault();});

        $j('.nicetry.item').each(function(){
            var orderId = $j(this).attr('data-order');
            var new_swatch_orderId = $j(this).find('.swatch-category-container').attr('id')+'-'+orderId;
            $j(this).find('.swatch-category-container').attr('id', new_swatch_orderId)
            var new_img_orderId = $j(this).find('.swatch-category').attr('id')+'-'+orderId;
            $j(this).find('.swatch-category').attr('id', new_img_orderId);
            $j(this).find('.swatch-category').each(function(){
                var attr_onclick = $j(this).attr('onclick');
                var onlick_str = attr_onclick.split(",");
                var onlick_str_id = onlick_str[1].substring(1, onlick_str[1].length-1);
                var onlick_str_new_id = "'"+onlick_str_id + "-" + orderId + "'";
                var new_str_onclick = onlick_str[0]+","+onlick_str_new_id+","+onlick_str[2]+","+onlick_str[3];
                $j(this).attr('onclick', new_str_onclick);
            });

        });
    });