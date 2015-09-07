<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class HeaderOutput{
    function HeaderHack($HEADER, $URL){
       
        /*FOLLOW / NOFOLLOW */
        switch($URL){
            case 'www.pyua.de/index.php/checkout/cart/': 
                $HEADER = str_replace("INDEX,FOLLOW", "NOINDEX,FOLLOW", $HEADER);
            break;
            case 'www.pyua.de/index.php/customer/account/forgotpassword/': 
                $HEADER = str_replace("INDEX,FOLLOW", "NOINDEX,FOLLOW", $HEADER);
            break;
            default: 
            break;
        }
        /*FOLLOW / NOFOLLOW ENDE*/
        
        /* CANONICALS EINBINDEN */
        switch($URL){
            case 'www.pyua.de/': 
            case 'www.pyua.de/index.php': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/\"> \n".$HEADER;
            break;
            case 'www.pyua.de/passformen-groessen': 
            case 'www.pyua.de/index.php/passformen-groessen': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/kundenservice/passformen-groessen\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/catalog/category/view/s/abwrackpraemie/id/78/': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/about-pyua/abwrackpraemie\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/women?p=1': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/women\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/women/produkte': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/women\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/women/produkte?p=1': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/women\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/women/produkte?p=2': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/women\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/women/produkte?p=3': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/women\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/agb': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/agb\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/datenschutz': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/datenschutz\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/customer/account/create/': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/customer/account/create/\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/customer/account/login/': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/customer/account/login/\"> \n".$HEADER;
            break;        
            case 'www.pyua.de/index.php/women/ubersicht-sale/produktubersicht': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/women\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/men/ubersicht-sale/produktubersicht': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/men\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/news/category/news-startseite': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/news\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/women/funktionale-kategorien': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/women\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/men/funktionale-kategorien': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/men\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/kundenservice/kundenservice-kontakt': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/kundenservice\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/friends/fachhaendler': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/friends\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/women/ubersicht-sale': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/women\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/men/ubersicht-sale': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/men\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/men/produkte': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/men\"> \n".$HEADER;
            break;
            case 'www.pyua.de/index.php/women/produkte': 
                $HEADER = "<link rel=\"canonical\" href=\"http://www.pyua.de/index.php/women\"> \n".$HEADER;
            break;
            default: 
            break;
        }
        /*CANONICALS ENDE*/
        
        /*START::WEITERLEITUNG*/
        switch($URL){
            case 'www.pyua.de/index.php/about-pyua':
                header('HTTP/1.1 301 Moved Permanently');
                header("Location: http://www.pyua.de/index.php/about-pyua/ecorrect-philosophy"); 
                header("Connection: close"); 
                exit();
            break;
            default:
            break;
        }
        /*ENDE::WEITERLEITUNG*/
        return $HEADER;
    }
}

