<?php
class Config
{
    //Set client_id provided when you registered your Fitbit App
    public static $client_id = "";
    //Set client_secret provided when you registered your Fitbit App
    public static $client_secret = "";
    //Set your redirect url. For this demo it would be http://your-url/c.php
    public static $redirect_url = "";
    //Set this to the date you strated loggig data on your fitbit format yyyy-mm-dd
    public static $start_date = "";
    //Set this to where you want your logs saved to.
    public static $log = "./log/export.log";
}
