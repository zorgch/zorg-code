(function ($) {
    var html5Notification = {
        init: function (options) {
            this.df = $.Deferred();

            if (typeof html5Notification.initialized === 'undefined') {
                // Allow to override default options.
                this.buildDefaults();

                this.config = $.extend({}, this.config, options);

                // Check browser support
                if (this.check_browser_support()) {
                    // Supported, ask for permission
                    this.permissionHandler();
                }

                html5Notification.initialized = true;
            }

            return this.df.promise();

        },

        permissionHandler: function () {
            // Check if we have permission (setting output to variable)
            var checkPermission = this.check_permission();

            if (checkPermission === 'pending') {
                this.request_permission();

            } else if (checkPermission === false) {
                this.config.field.browser_support
                    .addClass("alert alert-error")
                    .text(this.config.message.permission_denied);
            }
        },

        // Default settings that can be changed by user.
        buildDefaults: function () {
            this.config = {
                display_message: true,

                message: {
                    supported_browser: 'Your browser does support the Notification API.',
                    notsupported_browser: 'Your browser does not support the Notification API.',
                    permission_denied: 'You have denied access to display notifications.',
                    permission_button: 'Click to trigger notification request in chrome'
                },

                field: {
                    container: $('body'),
                    browser_support: $('<div id="message" />'),
                    button: $('<button />').css({
                        'border': '1px solid #ccc',
                        'padding': '4px 12px',
                        'font-size': '14px',
                        'line-height': '25px',
                        'cursor': 'pointer',
                        'background-color': '#f5f5f5',
                        'background-image': '-webkit-gradient(linear,0 0,0 100%,from(#fff),to(#e6e6e6))',
                        'position': 'absolute',
                        'top': 0,
                        'left': 0,
                        'width': '100%',
                        'margin': 0,
                        'display': 'none',
                        'z-index': '10000000'
                    })
                }
            };
        },

        /**
         * Check if browser can support HTML5 Notifications
         */
        check_browser_support: function () {
            // This is a check specialy for Chromium if there is no Notification
            // then check for webkitNotifications else we return false means
            // your browser doesn't support both types.
            var supported = (Notification)
                ? true
                : ( (webkitNotifications) ? true : false );

            if (this.config.display_message === true) {
                var browser_support = this.config.field.browser_support.appendTo(this.config.field.container);
				console.log("browser_support: " + browser_support.length);
				console.log("field: " + this.config.field.length);
                if (supported === true) {
                    browser_support
                        .addClass("alert alert-success")
                        .text(this.config.message.supported_browser);
                } else {
                    browser_support
                        .addClass("alert alert-error")
                        .text(this.config.message.notsupported_browser);
                }
            }

            return supported;
        },

        /**
         * Checks to see if HTML5 Notifications has permission
         */
        check_permission: function () {
            // Check if you have permission already
            // Second part is for chromium browsers
            var permission = (Notification.permission)
                ? Notification.permission
                : webkitNotifications.checkPermission();


            switch (permission) {
                // We have permission to post notifications
                case 0:
                case 'granted':
                    this.df.resolve();
                    return true;
                    break;

                // We still need to ask for permission
                case 1:
                case 'default':
                    return 'pending';
                    break;

                // The user rejected the permissions to post notifications
                case 2:
                case 'denied':
                    return false;
                    break;
            }
        },

        /**
         * Request HTML5 Notifications permissions.
         * After that recall permissionHandler
         */
        request_permission: function () {
            var self = this;

            // Because chrome needs a handler this ugly fix is inside the request permission function
            // jQuery 1.9 no longer has the $.browser so this check looks for chrome in the browser agent.
            var browserAgent = navigator.userAgent.toString().toLowerCase();
            
			if(/chrom(e|ium)/.test(browserAgent)){

                Notification.requestPermission(function () {
                    self.permissionHandler();
                    self.df.resolve();
                });

            } else {

                this.config.field.button
                    .appendTo(this.config.field.container)
                    .text(this.config.message.permission_button)
                    .slideDown(300, 'linear')
                    .click(function () {
                        $(this).remove();

                        Notification.requestPermission(function () {
                            self.df.resolve();
                        });

                    });
            }
        },

        /**
         * All the default options can be inserted into the Notification Api.
         * For a complete list of options see the W3 Web Notifications url.
         * https://dvcs.w3.org/hg/notifications/raw-file/tip/Overview.html#api
         *
         * If the permission is not already granted, the init function will be called.
         * This will cause the current message to be losted.
         *
         */
        create_message: function (options) {
            // Do only a simple check to see if we have the right to show messages
            var permission = (Notification.permission)
                ? Notification.permission
                : webkitNotifications.checkPermission();

            if (permission === 0 || permission === 'granted') {
                if (typeof options === 'string') {
                    var title = options,
                        config = {};
                } else {
                    var title = options.title,
                        config = options;
                }

                new Notification(title, config);

            } else {
                var self = this;
                self.init()
                    .done(function () {
                        self.create_message(options);
                    });
            }
        }
    };

    $.html5Notification = html5Notification;
})(jQuery);
