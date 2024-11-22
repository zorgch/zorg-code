HTML5 Notification
==================

During a project for [Nedap Healtcare](http://www.nedap-healthcare.com/) I wanted to use the Web Notification API.<br/>
So I decided that I would write a jQuery plugin that could be used to use the Web Notification.<br />

HTML5 Notification uses the [W3C Web Notification API](http://www.w3.org/TR/notifications/)<br />
At the moment FireFox and Safari uses the current version of the W3C Notification.<br />
Chrome still uses an older version on webkitNotification.<br />
Internet Explorer is still not supporting Notification. (Will implement this when available)<br />

Link for more information about browser support [click here](http://caniuse.com/notifications).

How to use the plugin
---------------------

initializing is really easy
	html5Notification.init();

But if you want some configuration options you can use the current configuration list below:

	The default settings

	{
		display_message: true,

		message: {
			supported_browser: 'Your browser does support the Notification API.',
			notsupported_browser: 'Your browser does not support the Notification API.',
			permission_denied: 'You have denied access to display notifications.',
			permission_button: 'Grant permission to display notifications',
		},

		field: {
			container: $('body'),
			browser_support: $('<div id="message" /></div>'),
			button: $('<button />')
		},
	}

A little explanation about what everything does.

	display_message: true OR false, On false no notification will be displayed.

	message: {
		supported_browser: Message for a supported browser of the Notification API,
		notsupported_browser: Message for a not supported browser of the Notification API.,
		permission_denied: Message if the user denied the use of the Notifications API,
		permission_button: Message that will be on the button to request permission
	},

	field: {
		container: Can be any jquery object,
		browser_support: Can also be any jquery object,
		button: the button where the user has to click on
	},


Creating a message
------------------
Short type

	html5Notification.create_message('Short version')

With some extra options

	html5Notification.create_message({
		title: 'The Longer version',
		body: 'A cool message with Nedap\'s logo',
		icon: 'http://www.studiokluif.nl/sites/default/files/Nedap_huisstijl_asterisk.jpg'
	});

Still working on
----------------

* Internet Explorer compatibility

This is my first plugin for jQuery. So if you have suggestions what I could fix or improve please<br />
notify me about that!

