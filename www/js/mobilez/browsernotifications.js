var backgroundActiveCheck = 60000/4; // Refresh period, interval is passed in Miliseconds
var newNotificationCheck = 10000; // Refresh period, interval is passed in Miliseconds
var debugMode = false; // ON = true | OFF = false

function notificationsRefresh(notify) {
		notify = typeof notify !== "undefined" ? notify : true; // if not otherwise specified, notify = true
		if (debugMode) console.log("notify: " + notify);
		if (debugMode) console.log("div.Fylout.FlyoutMenu: trigger .load()");
	    $("div.Flyout.FlyoutMenu").load("?p=/profile/notificationspopin&DeliveryType=VIEW");
	    if (debugMode) console.log("div.Fylout.FlyoutMenu: load() DONE");
		if (notify !== false) getNotificationMessageContent(); // Trigger notifications
	    if (debugMode) if (notify !== false) console.log("showNotification(): DONE - did you see it?");
}

function getNotificationMessageContent() {
	var title = $("div.MeMenu span.ToggleFlyout a:first-child").attr("title");
	if (debugMode) console.log("title: " + title);
	//OLD: $("div.Flyout.FlyoutMenu ul.PopList.Activities li.Item").each(function(index) {
	if (debugMode) $("div.Flyout.FlyoutMenu > ul.PopList.Activities > li.Item").find(".ItemContent.Activity").each(function(index) { console.log(index + ": " + $(this).text()) });
	if ($("div.Flyout.FlyoutMenu > ul.PopList.Activities > li.Item").find(".ItemContent.Activity").length > 1) {
			var body = "You have " + $("div.Flyout.FlyoutMenu > ul.PopList.Activities > li.Item").find(".ItemContent.Activity").length + " unread messages!"; // @TODO make translatable
			var url = "http://vanilla-dev.local/index.php?p=/profile/notifications"; // @TODO make dynamic
		} else {
			var body = $("div.Flyout.FlyoutMenu > ul.PopList.Activities > li.Item").children("div.ItemContent.Activity").text();
			var url = $("div.Flyout.FlyoutMenu > ul.PopList.Activities > li.Item").children("div.ItemContent.Activity a:nth-child(2)").attr("href");
		}
		if (debugMode) console.log("body: " + body);
		if (debugMode) console.log("url: " + url);
		if (typeof(body)!=="undefined") {
			if (debugMode) console.log("Firing html5Notification.create_message() function!");
			//$.html5Notification.create_message({
			//	title: title,
			//	body: body,
			//	url: url
			//});
			if (debugMode) gdn.informMessage(body, {"CssClass": "Dismissable"}); // Garden JS Message Infobox:
		} else {
			//called when there is an error
			console.log("Error: title=" + title + ", body=" + body + ", url=" + url);
		}
	//});
}

function checkForInformMessageDiv() {
	    if (debugMode) console.log("InformMessage: start checking");
		if ($("div.InformMessages > div.InformWrapper > div.InformMessage").length) {
			if (debugMode) console.log("InformMessage: FOUND (1 or more)");
			$("div.InformMessages > div.InformWrapper > div.InformMessage").each(function(index) {
				var title = $("div.InformMessages > div.InformWrapper > div.InformMessage").children("div.Title").text();
				title = (!$.trim(title)) ? "New Notification" : title; // @TODO make translatable
				//var body = $("div.InformMessages > div.InformWrapper > div.InformMessage).children("div.Excerpt").text();
				var body = $("div.InformMessages > div.InformWrapper > div.InformMessage").text();
				var url = $("div.InformMessages > div.InformWrapper > div.InformMessage a").attr("href");
				url = typeof url !== "undefined" ? url : "http://vanilla-dev.local/index.php?p=/profile/notifications"; // @TODO make dynamic
				if (debugMode) console.log("title: " + title);
				if (debugMode) console.log("body: " + body);
				if (debugMode) console.log("url: " + url);
				$.html5Notification.create_message({
					title: title,
					body: body,
					url: url
				});
				if (debugMode) console.log("showNotification(): DONE - did you see it?");
			});
		} else {
			if (debugMode) console.log("InformMessage: NOT found!");
		}
};

$(document).ready(function(){
	// Refresh the site periodically, if no focus is set on the current page
	var windowFocus = false;
	var haltNotificationRefresh = false;
	$(window).focus(function() {
	    windowFocus = true;
	    haltNotificationRefresh = false;
	    if (debugMode) console.log("windowFocus: true");
	})
	.blur(function() {
	    windowFocus = false;
	    if (debugMode) console.log("windowFocus: false");
	});
	// Set up a repeating job to periodically refresh the Notifications Popup
	setInterval(function() { if (windowFocus === false && haltNotificationRefresh === false) { if (debugMode) console.log("setInterval triggered"); notificationsRefresh(); haltNotificationRefresh = true; } }, backgroundActiveCheck);
	// Setup a repeating job to periodically check for the InformMessage-Div
	setInterval(function() { checkForInformMessageDiv(); }, newNotificationCheck);
	// Initially load the notifications menu once, without triggering notifications
	notificationsRefresh(false);
});

// Initialize the html5Notification Plugin with custom options
// FIXME Disabled due to "Undefined $.html5Notification.init"
/* $.html5Notification.init({
	display_message: true,
	message: {
		supported_browser: "Your browser does support the Notification API.",
		notsupported_browser: "Your browser does not support the Notification API.",
		permission_denied: "You have denied access to display notifications.",
		permission_button: "Grant permission to display notifications."
	},
	field: {
		container: $("body").find("#Content"),
		browser_support: $('<div class="DismissMessage AlertMessage" />')
	}
}); */
if (debugMode) console.log("container: " + $("#Content").length);
