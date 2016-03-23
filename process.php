<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 23.03.16
 * Time: 15:35
 */

$errors         = array();      // array to hold validation errors
$data           = array();      // array to pass back data

// validate the variables ======================================================
// if any of these variables don't exist, add an error to our $errors array

if (empty($_POST['benutzer']))
    $errors['benutzer'] = 'benutzer is required.';

if (empty($_POST['passwort']))
    $errors['passwort'] = 'passwort is required.';

// return a response ===========================================================

// if there are any errors in our errors array, return a success boolean of false
if (!empty($errors)) {

    // if there are items in our errors array, return those errors
    $data['success'] = false;
    $data['errors']  = $errors;
} else if($_POST['benutzer'] == "pyuab2b" || $_POST['passwort'] == "pyuab2b"){
    // if there are no errors process our form, then return a message

    // DO ALL YOUR FORM PROCESSING HERE
    // THIS CAN BE WHATEVER YOU WANT TO DO (LOGIN, SAVE, UPDATE, WHATEVER)

    // show a message of success and provide a true success variable
    $data['success'] = true;
    $data['message'] = 'Success!';

    $cookie_name = "loginsuccessed";
    setcookie("cookie_name", $cookie_name, time()+3600);
}else{
    // if there are no errors process our form, then return a message

    // DO ALL YOUR FORM PROCESSING HERE
    // THIS CAN BE WHATEVER YOU WANT TO DO (LOGIN, SAVE, UPDATE, WHATEVER)

    // show a message of success and provide a true success variable
    $data['success'] = false;
    $data['errors'] = $errors;
}

// return all our data to an AJAX call
echo json_encode($data);