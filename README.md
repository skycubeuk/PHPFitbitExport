# PHP Fitbit Export

Fitbit allows users who register a personal app to get intraday time series data (time-series data with to the minute resolution). This code will enable you to export this data to JSON files. It's written in PHP and tested on PHP 7.2.19.

### Disclaimer

PHP Fitbit Export is a quick project based on the [PHPMoves](https://github.com/zabouth/PHPMoves) library I made many years ago. I made PHP Fitbit Export because I lost all the data I had in Movies when the service shut down. PHP Fitbit Export is literarily something I cobbled together in an afternoon. 

* It is not a mature project.
* It is not secure 
* It is not well implemented. 
* It will most likely never be updated again.

### Prerequisites

* PHP >= 7.0.0 with cURL support
* Webserver running HTTPS open to the internet (for OAuth callbacks) . 

### Basic Setup
* Install a web server with PHP and https support (draw the rest of the f**king owl).
* Copy the files in the www directory to the webserver make sure it is accessible via the internet over https.
* Go to https://dev.fitbit.com/apps/new and register a new APP set the OAuth 2.0 Application Type to Personal and the Callback URL to the file c.php on your webserver. e.g. https://example.com/c.php
* Make a note of your OAuth 2.0 Client ID and Client Secret.
* Edit the config.php file on your web server setting the OAuth 2.0 Client ID, Client Secret and callback URL.
* Open a web browser and go to register.php on your web server. eg https://example.com/register.php
* Click on the Register link and login with your Fitbit account. When asked gran the APP all permissions.  
* If everything is set up correctly you will be redirected back to the callback URL you configured earlier.
* The page will be blank; this is expected behaviour.
* There will be a new file called token.json on your webserver.
* Move the token.json file into the root directory of this project **Do not leave it on the webserver. If anyone gets access to this file, they will have access to all your Fitbit data.**
* Once you have the token.json the webserver is no longer needed.
* Edit the config.php file in the root directory of this project adding your Client ID, Client Secret, callback URL and the date you want to start the backup. 
* Run backup.php via the command line your files will be exported to the backup folder. Fitbit allows 150 API calls per hour; there are 11 calls per day of data, a full year worth of data will take just over 26 hours 46 min to back up.


## Contributing

Please fork the project. I only check for pull requests on the 29<sup>th</sup> of February . 

## Authors

* **Graeme Dyas** - *all the work* - [zabouth](https://github.com/zabouth)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details