    
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

    /*
     * Description:     Diese Funktion generiert die Begriffserklärung in einem Produkt
     * Variablen:       t1 = Table1 class
     *                  t2 = Table2 class
     *                  t3 = Table3 class
     */
    // Suchworte generieren
    var SWord   =   [];
    function SgenerateDescription(t2, t3){
        var Table2 = $j(t2 + " div").html();
        var Table3 = $j(t3 + " div").html();

        if(Table2 !== ""){
            // Wort suchen
            var c = 0;
            for(key = 0; key < SWord[0].length; key++) {
                if(Table2 !== ""){
                    // gefunden in Table 2
                    Table2 = Table2.replace(""+SWord[0][key]+"", "<span class=\"sword p"+key+" t2\"><strong>"+SWord[0][key]+"</strong></span>");
                }
                if(Table3 !== ""){
                    // gefunden in Table 3
                    Table3 = Table3.replace(""+SWord[0][key]+"", "<span class=\"sword p"+key+" t3\"><strong>"+SWord[0][key]+"</strong></span>");
                }
            }
            Table2 = Table2 + "<div class=\"t2_SText\">";
            Table3 = Table3 + "<div class=\"t3_SText\">";

            /* Table 2 */ $j(t2 + " div").html(Table2);
            /* Table 3 */ $j(t3 + " div").html(Table3);
        }


        /*
         * Description:     bei Hover über ein sword class span wird die Funktion SshowDescription ausgeführt
         */
        $j('span.sword').mouseover(function(){
            var windowWidth = $j(window).width();
            if(windowWidth >= 990) {
                SshowDescription(this);
                $j(this).delay(600).queue(function (next) {
                    $j(this).addClass("active");
                    next();
                });
            }
        }).mouseout(function(){
            var windowWidth = $j(window).width();
            if(windowWidth >= 990) {
                $j('.t1_SText').fadeOut().html("");
                $j('.t2_SText').fadeOut().html("");
                $j('.t3_SText').fadeOut().html("");
                $j(this).removeClass("active");
            }
        });

        $j('span.sword').click(function(){
            var windowWidth = $j(window).width();
            if(windowWidth < 990) {
                $j('.t1_SText').fadeOut().html("");
                $j('.t2_SText').fadeOut().html("");
                $j('.t3_SText').fadeOut().html("");
                $j(".pyua_produkttabelle span").removeClass("active");
                SshowDescription(this);
                $j(this).queue(function (next) {
                    $j(this).addClass("active");
                    next();
                });
            }
        })
    }
    /*
     *  Description: Generiert das Array mit den jeweiligen Begriffen und Texten
     *  Variablen:   Filter - 0 = Es wird nichts gefiltert
     *                        1 = Klammern werden im Begriff herausgefiltert
     */
    function SgenerateArray(Filter, t2, t3){
        var Teil1   = [];
        var Teil2   = [];
        var Teil3   = [];

        var request = $j.ajax({
            type:       "POST",
            url:        "http://localhost/pyua/index.php/kundenservice/begriffserlaeuterungen",
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
            $j('td img', Content).each(function() {
                temp    = "";
                if($j(this).html() != "&nbsp;"){
                    Teil3.push($j(this));
                }
            });
            SWord.push(Teil1);
            SWord.push(Teil2);
            SWord.push(Teil3);
            SgenerateDescription(t2, t3);
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
            +"    width:              382px;"
            +"    margin-left:        -16px;"
            +"    bottom:             21px;"
            +"    border-radius:      5px 0 0 5px;"
            +"}"
            +".t2_SText{"
            +"    height:             "+height+"px;"
            +"    width:              382px;"
            +"    margin-left:        -16px;"
            +"    bottom:             21px;"
            +"    border-radius:      5px 0 0 5px;"
            +"}"
            +".t3_SText{"
            +"    height:             "+height+"px;"
            +"    width:              382px;"
            +"    margin-left:        -16px;"
            +"    bottom:             21px;"
            +"    border-radius:      0 5px 5px 0;"
            +"}"
            +".t1_SText .img,"
            +".t2_SText .img,"
            +".t3_SText .img{"
            +"    float:              left;"
            +"    width:              100px;"
            +"    min-height:         90px;"
            +"    margin-bottom:      10px;"
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
        Content = SWord[2][Class[1]];
        $j(Target).stop().html( "<span class='img'></span>" );
        $j(Target).stop().find('.img').html(Content);
        $j(Target).stop().append("<span class='text'>"+SWord[1][Class[1]]+"</span>");
        var windowWidth = $j(window).width();
        if(windowWidth < 990) {
            $j(Target).stop().append('<span class="sword-close">×</span>');
            $j('.sword-close').click(function() {
                $j('.t1_SText').fadeOut().html("");
                $j('.t2_SText').fadeOut().html("");
                $j('.t3_SText').fadeOut().html("");
                $j(".pyua_produkttabelle span").removeClass("active");
            });
        }
        $j(Target).stop().fadeIn(300);
    }

    function Quickinfo(){
        if($j('.quick-info-detail').html()) {
            var quick_text = $j('.quick-info-detail').html();
            var lines = quick_text.split('<br>');
            var output = '';
            $j.each(lines, function (key, line) {
                var parts = line.split(':');
                output += '<div style="float:left; width: 125px;">' + parts[0] + ':' + '</div><div>' + parts[1] + '</div>';
            });
            $j('.quick-info-detail').html(output);
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

        var url_name = ["/impressum","/datenschutz","/agb","kundenservice/kundenservice-kontakt","/about-pyua","/friends","/kundenservice"];
        var url_current = $j(location).attr('href');
        $j.each( url_name, function( i, val ) {
            if(url_current.indexOf(val) >= 0){
                $j('.overlay').css("display","none");
            }
        });
        $j(".overlay-newsletter").on("click", function( e ) {
            e.preventDefault();
            $j("body, html").animate({
                scrollTop: $j('.footer-container').offset().top
            }, 600);
            $j( "#newsletter" ).focus();
        });
        $j(".overlay-newsletter-close").on("click", function( e ) {
            e.preventDefault();
            $j(".startseite-overlay").fadeOut(300);
        });

        $j('body').click(function(evt){
            if((evt.target.id != "newsletter-startseite") && (evt.target.id != "newsletter-startseite-subscribe"))
            {
                $j(".startseite-overlay").fadeOut(300);
            }

        });

        if($j(".success-msg li span").html() == "Bestätigungs-Anfrage wurde gesendet."){
            $j(".startseite-overlay").fadeOut(300);
        }
        if($j(".error-msg li span").html() == 'Der Gutscheincode "SCHAURIG-SCHOEN-ZU-HALLOWEEN" ist nicht auf bereits reduzierte Produkte anwendbar.'){
            $j(this).html('Der Gutscheincode "SCHAURIG-SCHOEN-ZU-HALLOWEEN" ist nur in Kombination von Skijacke + Spark/Spark-Y oder Skijacke+Glow/Glow-Y+T-Shirt anwendbar.');
        }
        if($j(".success-msg li span").html() == 'Der Gutscheincode "abwrack_skijacke" wurde eingelöst. ACHTUNG: Der Code ist nicht auf bereits reduzierte Produkte bzw. Aktions-Artikel anwendbar.'){
            $j(this).html('Der Gutscheincode "abwrack_skijacke" wurde eingelöst. ACHTUNG: Es ist technisch bedingt nur ein Code pro Bestellung einlösbar. Bitte für das Einlösen weiterer Codes einfach eine neue Bestellung aufgeben.');
        }
        if($j(".success-msg li span").html() == 'Der Gutscheincode "abwrack_skihose" wurde eingelöst. ACHTUNG: Der Code ist nicht auf bereits reduzierte Produkte bzw. Aktions-Artikel anwendbar.'){
            $j(this).html('Der Gutscheincode "abwrack_skihose" wurde eingelöst. ACHTUNG: Es ist technisch bedingt nur ein Code pro Bestellung einlösbar. Bitte für das Einlösen weiterer Codes einfach eine neue Bestellung aufgeben.');
        }
        if($j('body').attr("class").indexOf("categorypath-men") >= 0)
        {
            $j(".catalog-category-view .page-title.category-title").css({"right":"105px","left":"inherit"});
        }
        if($j('body').attr("class").indexOf("category-saison-opening") >= 0)
        {
            $j(".catalog-category-view .page-title.category-title").css("display","none");
        }
        if($j('body').attr("class").indexOf("category-halloween") >= 0)
        {
            $j(".catalog-category-view .page-title.category-title").css("display","none");
        }
        if($j('body').attr("class").indexOf("category-1advent") >= 0)
        {
            $j(".catalog-category-view .page-title.category-title").css("display","none");
        }
        if($j('body').attr("class").indexOf("category-2advent") >= 0)
        {
            $j(".catalog-category-view .page-title.category-title").css("display","none");
        }
        if($j('body').attr("class").indexOf("category-adventszeit") >= 0)
        {
            $j(".catalog-category-view .page-title.category-title").css("display","none");
        }
        if($j('body').attr("class").indexOf("category-b2b") >= 0)
        {
            $j(".catalog-category-view .page-title.category-title").css("display","none");
        }
        $j( "li.nicetry" ).each(function( index ) {
            if($j(this).find(".product-info .product-name a").html() == "Backyard-Y"){
                if($j(this).attr("data-order") != 4) {
                    $j(this).find(".neu-box").css("display", "none");
                }
            }
        });
        $j('#advent1-form').submit(function(){
            $j.post($j(this).attr('action'), $j(this).serialize(), function(response){
            },'json');
            if(isEmail($j("#form_EMAIL").val())) {
                $j('.catalog-category-view .landing .success-info').css("display", "block");
                $j('.catalog-category-view .landing .error-info').css("display", "none");
                $j('#advent1-form').css("display","none");
                $j('.h1-hidden').css("display","none");
                $j('.p-hidden').css("display","none");
            }else{
                $j('.catalog-category-view .landing .success-info').css("display", "none");
                $j('.catalog-category-view .landing .error-info').css("display", "block");
            }
            return false;
        });
        function isEmail(email) {
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return regex.test(email);
        }
        var windowWidth = $j(window).width();
        if(windowWidth <= 770) {
            $j('li.level0').click(function () {
                if ($j(this).hasClass('menu-active')) {
                    $j(this).find(">ul").css("display", "block");
                } else {
                    $j(this).find(">ul").css("display", "none");
                }
            });
            $j('li.level1').click(function () {
                if ($j(this).hasClass('menu-active')) {
                    $j(this).find(">ul").css("display", "block");
                } else {
                    $j(this).find(">ul").css("display", "none");
                }
            });
        }
        if($j(".success-msg li span").html() == 'Vielen Dank, dass Du unseren Newsletter abonniert hast. Bitte bestätige Deine Emailadresse über den Link, den wir Dir geschickt haben.'){
            var text = "<!-- Facebook Pixel Code -->"+"<br/>"
                +"<script>"+"<br/>"
                +"!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod? "+"<br/>"
                +"n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n; "+"<br/>"
                +"n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0; "+"<br/>"
                +"t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, "+"<br/>"
                +"document,'script','//connect.facebook.net/en_US/fbevents.js');"+"<br/>"
                +"fbq('init', '342542455870036');"+"<br/>"
                +"fbq('track', 'CompleteRegistration');</script>"+"<br/>"
                +"<noscript>"+"<br/>"
                +'<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=342542455870036&ev=PageView&noscript=1" />'+"<br/>"
                +"</noscript>"+"<br/>"
                +"<!-- End Facebook Pixel Code -->";
            $j('head').append(text);
        }

        var formB2B = '<form id="b2b_form" action=""><input id="b2b_benutzer" type="text" name="benutzer" placeholder="Benutzername"><input id="b2b_pass" type="password" name="passwort" placeholder="Passwort"><input id="b2b_submit" value="Bestätigen" type="submit"></form>'

        function readCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for(var i=0;i < ca.length;i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
            }
            return null;
        }
        var cookie_form= readCookie('cookie_name');

        if (window.location.href.indexOf("b2b") > -1) {
            if(cookie_form != "loginsuccessed"){
                $j("#download-area").css("display","none");
                window.location.href = "http://localhost/pyua";
            }
        }

        $j("#header-nav #nav .nav-primary > li a").on("click", function(event){
            if($j(this).html() == "B2B"){
                if(cookie_form == "loginsuccessed"){

                }else{
                    $j("#header-nav").append(formB2B);
                    // process the form
                    $j('#b2b_form').submit(function(event) {
                        var formData = {
                            'benutzer'              : $j('input[name=benutzer]').val(),
                            'passwort'             : $j('input[name=passwort]').val()
                        };

                        $j.ajax({
                            type        : 'POST', // define the type of HTTP verb we want to use (POST for our form)
                            url         : '../process.php', // the url where we want to POST
                            data        : formData, // our data object
                            dataType    : 'json' // what type of data do we expect back from the server
                        })
                            // using the done promise callback
                            .done(function(data) {
                                if (!data.success) {
                                    alert('Bitte geben Sie Ihre Benutzername oder Passwort ein!'); // for now we'll just alert the user
                                }else{
                                    $j("#b2b_form").remove();
                                    window.location.href = "http://localhost/pyua/b2b";
                                }
                            });
                        event.preventDefault();
                    });
                    event.preventDefault();
                }
            }
        });
    });