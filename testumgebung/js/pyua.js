/*Activate JQUERY*/
var $j = jQuery.noConflict(); 


$j(document).ready(function(){    
    /*
     * Description:     Dieser Abschnitt bearbeitet den Active Status im Checkout.   
     */
    $j(document).mouseover(function(){
        if($j(".checkout-onepage-index #opc-billing #checkout-step-billing").is(":visible") 
        || $j(".checkout-onepage-index #opc-shipping #checkout-step-shipping").is(":visible")){

            $j(".checkout-onepage-index #opc-billing").addClass("active");
        } else{

            $j(".checkout-onepage-index #opc-billing").removeClass("active");
        }
    });
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

/*
 * Description:     Diese Funktion generiert die Begriffserklärung in einem Produkt
 * Variablen:       t1 = Table1 class 
 *                  t2 = Table2 class 
 *                  t3 = Table3 class
 */
// Suchworte generieren
var SWord   =   [];
function SgenerateDescription(t1, t2, t3){
    var Table1 = $j(t1 + " div").html();
    var Table2 = $j(t2 + " div").html();
    var Table3 = $j(t3 + " div").html();

    if(Table1 !== "" && Table2 !== ""){
        // Wort suchen
        var c = 0;
        for(key = 0; key < SWord[0].length; key++) {
            if(Table1 !== ""){
                // gefunden in Table 1
                Table1 = Table1.replace(""+SWord[0][key]+"", "<span class=\"sword p"+key+" t1\"><strong>"+SWord[0][key]+"</strong></span>");
            }            
            if(Table2 !== ""){
                // gefunden in Table 2
                Table2 = Table2.replace(""+SWord[0][key]+"", "<span class=\"sword p"+key+" t2\"><strong>"+SWord[0][key]+"</strong></span>");
            }            
            if(Table3 !== ""){
                // gefunden in Table 3
                Table3 = Table3.replace(""+SWord[0][key]+"", "<span class=\"sword p"+key+" t3\"><strong>"+SWord[0][key]+"</strong></span>");                                        
            }
        }
        Table1 = Table1 + "<div class=\"t1_SText\">";
        Table2 = Table2 + "<div class=\"t2_SText\">";
        Table3 = Table3 + "<div class=\"t3_SText\">";

        /* Table 1 */ $j(t1 + " div").html(Table1);
        /* Table 2 */ $j(t2 + " div").html(Table2);
        /* Table 3 */ $j(t3 + " div").html(Table3);
    }
    
    
    /*
     * Description:     bei Hover über ein sword class span wird die Funktion SshowDescription ausgeführt
     */
    $j('span.sword').mouseover(function(){
        SshowDescription(this);
    }).mouseout(function(){
        $j('.t1_SText').fadeOut().html("");
        $j('.t2_SText').fadeOut().html("");
        $j('.t3_SText').fadeOut().html("");
    });
      
}
/*
 *  Description: Generiert das Array mit den jeweiligen Begriffen und Texten
 *  Variablen:   Filter - 0 = Es wird nichts gefiltert
 *                        1 = Klammern werden im Begriff herausgefiltert
 */
function SgenerateArray(Filter, t1, t2, t3){
    var Teil1   = [];
    var Teil2   = [];   
    
    var request = $j.ajax({
        type:       "POST",
        url:        "http://www.pyua.de/index.php/kundenservice/begriffserlaeuterungen",
        data:       "",
        dataType:   "html"
    });
    
    request.done(function(msg){        
        var Content = $j(".col-main", msg).html();
        var temp    = "";        
        $j('td h5', Content).each(function() {
            temp    = "";
            if($j(this).html() != "&nbsp;"){
                if(Filter === 1){
                    temp = $j(this).html();
                    temp = temp.substr(0, temp.indexOf(' ('));
                } else {
                    temp = $j(this).html();
                }
                Teil1.push(temp);
            }
        });
        $j('td p', Content).each(function() {
            temp    = "";
            if($j(this).html() != "&nbsp;"){
                Teil2.push($j(this).html());
            }
        });          
        SWord.push(Teil1);
        SWord.push(Teil2);
        SgenerateDescription(t1, t2, t3);
    }).fail(function(msg){  
        console.log(msg);
    });      
}
function SshowCSS(){
    var head    = document.head || document.getElementsByTagName('head')[0];
    var style   = document.createElement('style');
    var height  = $j("table.pyua_produkttabelle").height() + 31;
    
    var CSS = ""
    +".t1_SText,"
    +".t2_SText,"
    +".t3_SText {"
    +"    color:              #ffffff;"
    +"    background-color:   #0076b5;"
    +"    position:           absolute;"
    +"    padding:            15px;"
    +"    display:            none;"
    +"}"
    +".t1_SText{"
    +"    height:             "+height+"px;"
    +"    width:              256px;"
    +"    margin-left:        -16px;"
    +"    bottom:             21px;"
    +"    border-radius:      5px 0 0 5px;"
    +"}"
    +".t2_SText{"
    +"    height:             "+height+"px;"
    +"    width:              256px;"
    +"    margin-left:        -16px;"
    +"    bottom:             21px;"
    +"}"
    +".t3_SText{"
    +"    height:             "+height+"px;"
    +"    width:              256px;"
    +"    margin-left:        -16px;"
    +"    bottom:             21px;"
    +"    border-radius:      0 5px 5px 0;"
    +"}"
    +"";
    style.type = 'text/css';
    if (style.styleSheet){
      style.styleSheet.cssText = CSS;
    } else {
      style.appendChild(document.createTextNode(CSS));
    }
    head.appendChild(style);
}

function SshowDescription(dies){
    var Class = $j(dies).attr('class');
    Class = Class.split(" ", 3);
    Class[1] = Class[1].substr(1);
    
    var Target = "";
    switch(Class[2]){
        case 't1':
            Target = ".t2_SText";
        break;
        case 't2':
            Target = ".t3_SText";
        break;
        case 't3':
            Target = ".t2_SText";
        break;
    }
    Content = SWord[1][Class[1]];
    $j(Target).stop().html(Content);
    $j(Target).stop().fadeIn();
}