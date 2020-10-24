# API Keys
Unter dem `/keys/`-Verzeichnis sind diverse API Keys verschiedener Services abgelegt, welche das zorg Backend braucht um Daten zu aggregieren oder an externe APIs zu pushen.

* [Google API](#google-api)
  + [reCaptcha](#recaptcha)
  + [Google API](#google-api-1)
* [NASA API](#nasa-api)
* [Telegram Bot API](#telegram-bot-api)
* [Twitter API](#twitter-api)

## Google API

### reCaptcha
Das [Google reCaptcha](https://www.google.com/recaptcha/intro/v3.html) wird in der User Registration benötigt, um Bots am Erstellen von Fake Usern abzuhalten.
```
<?php
return
[
	'DEVELOPMENT' =>
		[
			 'key'			=> 'myGoogleReCaptchaDevPublicKey'
			,'secret'		=> 'myGoogleReCaptchaDevKeySecret'
		]
];

```


### Google API
z.B. für [Google Maps API](https://developers.google.com/maps/documentation) Abfragen

#### Example
```
<?php
return 'myTopSecretGoogleApiKey';
```


## NASA API
Die [NASA API](https://api.nasa.gov) wird benötigt um folgende Daten zu aggregieren:
* [APOD](https://api.nasa.gov/#apod) - Astronomy Picture of the Day
* Spaceweather APIs:
  + [Near Earth Objects](https://api.nasa.gov/#NeoWS)
  + [Space Weather Database DONKI](https://api.nasa.gov/#DONKI)

### Example
#### APOD
```
<?php
return 'DEMO_KEY';
```

#### Demo
* Request: [https://api.nasa.gov/planetary/apod?api_key=DEMO_KEY]
* Result
```
{"date":"2020-04-18","explanation":"It was just another day on aerosol Earth. For August 23, 2018, the identification and distribution of aerosols in the Earth's atmosphere is shown in this dramatic, planet-wide digital visualization. Produced in real time, the Goddard Earth Observing System Forward Processing (GEOS FP) model relies on a combination of Earth-observing satellite and ground-based data to calculate the presence of types of aerosols, tiny solid particles and liquid droplets, as they circulate above the entire planet. This August 23rd model shows black carbon particles in red from combustion processes, like smoke from the fires in the United States and Canada, spreading across large stretches of North America and Africa. Sea salt aerosols are in blue, swirling above threatening typhoons near South Korea and Japan, and the hurricane looming near Hawaii. Dust shown in purple hues is blowing over African and Asian deserts. The location of cities and towns can be found from the concentrations of lights based on satellite image data of the Earth at night.   Celebrate: Earth Day at Home","hdurl":"https://apod.nasa.gov/apod/image/2004/atmosphere_geo5_2018235_eq2400.jpg","media_type":"image","service_version":"v1","title":"Just Another Day on Aerosol Earth","url":"https://apod.nasa.gov/apod/image/2004/atmosphere_geo5_2018235_eq1200.jpg"}
```


## Telegram Bot API
Danke der [Telegram Bot API](https://core.telegram.org/bots) - und einem custom Bot - lassen sich diverse Messages an Telegram User oder Gruppen pushen.

### Example
Beispielfile `telegramexample_bot.php` ist im Ordner inkludiert.

```
<?php
/**
 * Telegram Bot Configs - Example
 * for 'telegramexample_bot'
 */
$botconfigs = [
				 'api_key' 			=> '' // as provided by @BotFather
				,'my_secret'		=> '' // (string) A secret password required to authorise access to the webhook.
				,'valid_ips' 		=> [ // (array) When using `validate_request`, also allow these IPs.
									        //'1.2.3.4',         // single
									        //'192.168.1.0/24',  // CIDR
									        //'10/8',            // CIDR (short)
									        //'5.6.*',           // wildcard
									        //'1.1.1.1-2.2.2.2', // range
									        '*' // Any
									    ]
				,'admins'			=> [] // (array) An array of user ids that have admin access to your bot (must be integers).
				,'ssl_certificate' 	=> __DIR__ . '/server.crt' // (string) Path to a self-signed certificate (if necessary).
				,'logging_dirroot'	=> [ __DIR__ . '/data/errlog/telegramexample_bot' ] // (array) Paths where the log files should be put.
				,'files_dirroot'	=> [ __DIR__ . '/data/files/telegram/telegramexample_bot' ] // (array) List of configurable paths.
			];

if (!defined('TELEGRAM_API_URI')) define('TELEGRAM_API_URI', 'https://api.telegram.org/bot' . $botconfigs['api_key']);
if (!defined('TELEGRAM_GROUPCHAT_ID')) define('TELEGRAM_GROUPCHAT_ID', ''); // Telegram-Group Chat-ID to post generic messages to
```


## Twitter API
Mit der [Twitter API](https://developer.twitter.com/en/docs/tweets/post-and-engage/overview) lassen sich Daten mittels eine [Twitter App ID](https://developer.twitter.com/en/apps) an einen Twitter-Account pushen oder aggregieren. z.B. Für das Absetzen von neuen Tweets.

### Example
```
<?php
return
[
	'DEVELOPMENT' =>
		[
			 'key'			=> 'myTwitterAPIdevPublicKey'
			,'secret'		=> 'mySuperSecretTwitterAPIdevKeySecret'
			,'token'		=> 'myVeryFancyTwitterAPIdevToken'
			,'tokensecret'	=> 'myVeryFancyYetSecureTwitterAPIdevTokenSecret'
			,'callback_url' => ''
		]
];
```
