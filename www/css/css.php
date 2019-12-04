<?php
/**
 * zorg CSS stylesheet with Day and Night layout
 *
 * @package zorg\Layout
 * @version 1.0
 * @since 1.0 <inex> 10.09.2019 file copied from /css/day.css & /css/night.css, uses ?sidebar=true trigger for page layout
 */
header('Content-Type: text/css');
$sidebarOn = ($_GET['sidebar'] == 'true' ? true : false);
$layout = (!empty($_GET['layout']) ? $_GET['layout'] : 'day');
//if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> $sidebarOn: %s', __FILE__, __LINE__, ($sidebarOn ? 'true' : 'false')));
?>
@charset "UTF-8";

/** CSS Vars (default & fallback: "day" layout) */
:root{
	--color-font-primary: <?= ($layout === 'night' ? 'rgba(255,255,255,0.7)' : 'rgba(0,0,0,0.8)'); ?>;
	--color-font-secondary: <?= ($layout === 'night' ? 'rgba(203,186,121,0.65)' : 'rgba(204,204,204,1)'); ?>;
	--color-font-input-disabled: <?= ($layout === 'night' ? 'gray' : 'gray'); ?>;
	--color-font-input: <?= ($layout === 'night' ? 'rgba(255,255,255,0.75)' : 'rgba(56, 57, 61,0.9)'); ?>;
	--color-link-primary: <?= ($layout === 'night' ? 'rgba(204,187,123,1)' : 'rgba(52,69,134,1)'); ?>;
	--color-link-navigation: <?= ($layout === 'night' ? '#cbba79' : '#344586'); ?>;
	--color-link-navigation-hover: <?= ($layout === 'night' ? '#42300A' : '#9dafd5'); ?>;
	--color-icon-primary: <?= ($layout === 'night' ? 'rgba(255,255,255,0.65)' : 'rgba(0,0,0,0.7)'); ?>;
	--background-color-base: <?= ($layout === 'night' ? 'rgba(3,12,22,1)' : 'rgba(246, 249, 254, 1)'); ?>;
	--background-color-main: <?= ($layout === 'night' ? 'linear-gradient(0deg, rgba(10,35,66,1) 0%, rgba(3,12,22,1) 20%)' : 'rgba(250,250,250,1)'); ?>;
	--background-color-behind: <?= ($layout === 'night' ? 'linear-gradient(0deg, rgba(3,12,22,1) 80%, rgba(10,35,66,1) 100%);' : 'rgba(245,245,245,1)'); ?>;
	--background-color-unobtrusive: <?= ($layout === 'night' ? '#242424' : '#ddd'); ?>;
	--background-color-input: <?= ($layout === 'night' ? 'rgba(3,12,22,0.75)' : 'rgba(246, 249, 254, 0.75)'); ?>;
	--background-color-input-button-hover: <?= ($layout === 'night' ? 'rgba(203, 186, 121, 0.5);' : 'rgba(246, 249, 254, 0.75)'); ?>;
	--background-color-navigation: <?= ($layout === 'night' ? '#42300a' : '#bdcff5'); ?>;
	--background-color-navigation-hover: <?= ($layout === 'night' ? '#62502a' : '#9dafd5'); ?>;
	--background-image-body: <?= ($layout === 'night' ? 'url(/images/background/night.png) repeat-x, radial-gradient(circle, rgba(7,19,44,1), rgba(10,35,66,1)) fixed no-repeat' : 'rgba(7,19,44,1)'); ?>;
	--filter-invert: <?= ($layout === 'night' ? 'invert(0.75)' : 'none'); ?>;
	--font-family-body: -apple-system-body, BlinkMacSystemFont, 'Helvetica Neue', Helvetica, Verdana, Arial, sans-serif;
	--font-family-headline: -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Helvetica, 'Segoe UI', Verdana, Arial, sans-serif;
	--font-family-logo: 'Segoe UI', sans-serif;
	--font-family-footer: 'Iosevka Web', sans-serif;
	--outline-table: <?= ($layout === 'night' ? '1px solid rgba(204,187,123,0.3)' : '1px solid rgba(204,204,204,0.3)'); ?>;
	--outline-input: <?= ($layout === 'night' ? '1px solid rgba(203,186,121,0.65)' : '1px solid rgba(204,204,204,1)'); ?>;
	--outline-input-focus: <?= ($layout === 'night' ? '1px solid rgba(255,255,255,0.85)' : '1px solid rgba(52,69,134,1)'); ?>;
	--border-input: <?= ($layout === 'night' ? 'none' : '1px solid rgba(255,255,255,0.75)'); ?>;
	--border-input-invisible: <?= ($layout === 'night' ? '1px solid rgba(0,0,0,0)' : 'none'); ?>;
	--border-input-focus: <?= ($layout === 'night' ? 'inset thin rgba(255,255,255,0.65)' : 'none'); ?>;
	--border-input-button-hover: <?= ($layout === 'night' ? '1px solid rgba(255,255,255,0.85)' : 'inset thin rgba(52,69,134,0.5)'); ?>;
	--border-input-disabled: <?= ($layout === 'night' ? 'gray' : 'gray'); ?>;
	--border-navigation: <?= ($layout === 'night' ? '#cbba79' : 'rgba(255,255,255,0.75)'); ?>;
	--border-title: <?= ($layout === 'night' ? '#cbba79' : '#ccc'); ?>;
	--shadow-input-focus: <?= ($layout === 'night' ? '0 0 1px 1px rgba(255,255,255,0.85)' : '0 0 1px 1px rgba(52,69,134,0.5)'); ?>;
	--shadow-input-focus-moz_mac: 0 0 0 3px -moz-mac-focusring;
}

html { font-size: calc(1em + 1vw); }

body {
	background: var(--background-image-body, rgba(1,1,1,1));
	background-size: cover;
	color: var(--color-font-primary, rgba(241,241,241,1));
	font-family: var(--font-family-body, Helvetica, Verdana, Arial, sans-serif);
	font-smoothing: auto;
	-webkit-font-smoothing: antialiased;
	-moz-osx-font-smoothing: auto;
	line-height: 1.4;
	display: grid;
	height: 100vh;
	margin: 0;
	padding: 0;
}

/**
 * Responsive CSS Grid
 */
.zorghead {
	display: grid;
	grid-column-gap: 0px;
	grid-row-gap: 0px;
}
@supports (grid-area: auto) {
	.zorghead {
		grid-area: header;
	}
		.zorghead > .logo {
			grid-area: logo;
		}
		.zorghead > .announcements {
			grid-area: announcements;
			align-self: center;
		}
		.zorghead > .service {
			grid-area: service;
			align-self: start;
			justify-self: end;
		}
		.zorghead > .onlineuser {
			grid-area: onlineuser;
			align-self: center;
		}
		.zorghead > .notifications {
			grid-area: notifications;
			align-self: center;
			justify-self: start;
		}
		.zorghead > .infos {
			grid-area: infos;
		}
	.navigation {
		grid-area: nav;
	}
	.main-content {
		grid-area: main;
	}
	.sidebar {
		grid-area: sidebar;
	}
	.footer {
		grid-area: footer;
	}
}

/* Desktops, Laptops: Screen = B/w 1025px to 1280px */
@media (min-width: 768px) {
	body {
		margin: 0 18vw 0 18vw;
		grid-template-columns: 2fr 1fr;
		grid-template-rows: minmax(min-content, 190px) minmax(min-content, 120px) minmax(min-content, max-content) 1fr;
		grid-template-areas:
			"header header"
			"nav nav"
			"main <?= ($sidebarOn === true ? 'sidebar' : 'main'); ?>"
			"footer footer";
	}
	.zorghead {
		grid-template-columns: 1fr 2fr 1fr;
		grid-template-rows: 1fr 1fr 1fr;
		grid-template-areas:
			"logo announcements service"
			"infos announcements service"
			"notifications notifications onlineuser";
		padding: .2rem 1rem .2rem 1rem;
	}
		header > .service { font-size: 0.6rem; }
		header > .service label.user::before { content: "\01F464"; }
		header > .service label.password::before { content: "\01F510"; }
		header > .announcements { justify-self: center; }
		header > .infos {
			align-self: start;
			font-size: 0.4rem;
		}
		header > .infos .solarstate .event { margin-right: 5px; }
	.main-content { padding: .5rem .5rem .5rem 1.25rem; }
	.sidebar { padding: .5rem 1rem .5rem 1rem; }
	.footer { padding: .5rem 1rem 1rem 1rem; }
	.footer > .shadow { margin: 0 -1rem 0 -1rem; } /** Compensate .footer{padding-left & -right} */
}

/* Mobile Smartphones (Portrait): Screen = B/w 320px to 479px */
@media (max-width: 767px) {
	body {
		grid-template-columns: auto;
		grid-template-rows: minmax(min-content, 100px) minmax(min-content, max-content) minmax(min-content, max-content) auto 1fr;
		grid-template-areas:
			"header"
			"nav"
			"main"
			<?= ($sidebarOn === true ? '"sidebar"' : null); ?>
			"footer";
	}
	.zorghead {
		grid-template-columns: 1fr 1fr;
		grid-template-areas:
			"logo service"
			"announcements service"
			"onlineuser service"
			"notifications infos";
		padding: .2rem .5rem .2rem .5rem;
	}
		header > .service {
			font-size: 0.5rem;
		}
		header > .announcements {
			justify-self: start;
		}
		header > .infos {
			font-size: 0.5rem;
			align-self: end;
			justify-self: end;
		}
		header > .infos .solarstate .event {
			margin-right: 5px;
		}
	.navigation {
			display: flex;
		}
		.navigation div.menu {
			flex-grow: 1;
			border-top-color: var(--border-navigation, #ccc);
			border-bottom: none
		}
	    div.menu > a {
		    display: block;
		    border-left: none;
		    border-right: none;
		}
	    div.menu > a.left, div.menu > a.right {
		    display: none;
		}
	.main-content { padding: .2rem .5rem 0 .5rem; }
	.main-content > img { max-width: 100%; }
	.sidebar { padding: .5rem .5rem .5rem .5rem; }
	.footer { padding: .2rem .2rem .5rem .5rem; }
	.footer > .shadow { margin: 0 -0.2rem 0 -0.5rem; } /** Compensate .footer{padding-left & -right} */

	.hide-mobile { display: none; }
}
/** END: Responsive CSS Grid */

/**
 * HTML5 Structure Styling
 */
/** Old table-layout compatibility */
table {
	width: 100%;
	border: none;
	border-collapse: collapse;
	padding: 0;
}

.zorghead, .navigation { background: var(--background-color-base, rgba(1,1,1,1)); }
.navigation {
	font-size: 0.5rem;
	text-align: center;
	padding-left: 0;
	padding-right: 0;
}

.main-content, .sidebar { background: var(--background-color-main, rgba(1,1,1,1)); }
.main-content { font-size: 0.5rem; }
.sidebar { font-size: 0.5rem; }

.footer, .tpl-footer {
	font-family: var(--font-family-footer, sans-serif);
	font-size: 0.5rem;
	letter-spacing: 0.1em;
	background: var(--background-color-behind, rgba(1,1,1,1));
	border-top: <?= ($layout === 'night' ? 'none' : 'solid 1px #ccc'); ?>;
}
.tpl-footer {
	font-size: 1em;
	padding: .5em 0 .2em 0;
}
/** END: HTML5 Structure Styling */

/**
 * Icons
 */
i.emoji {
	font-style: normal;
	font-size: 0;
	padding: 0 2px 0 2px;
	vertical-align: middle;
	display: inline-block;
	width: 1.2rem;
}
/** Emoji */
i.user::before { content: "\01F464"; }
i.password::before { content: "\01F510"; }
i.event::before { content: "\01F5D3"; }
/** SVG */
i.day::before {
	content: url('data:image/svg+xml;utf8, <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 125" x="0px" y="0px" style="fill: <?= ($layout === 'night' ? 'rgba(240,196,32,0.6)' : 'rgba(227,103,0,0.65)'); ?>;"><path d="M64.17,63a16,16,0,0,0-28-6.25A12.5,12.5,0,0,0,18,65.17a11.49,11.49,0,0,0,1.23,22.91H60.93a1.34,1.34,0,0,0,.35,0A12.83,12.83,0,0,0,64.17,63Zm-3.31,22.1-.21,0h-41l-.23,0-.23,0a8.49,8.49,0,0,1-.06-17,1.54,1.54,0,0,0,1.63-1.36,9.49,9.49,0,0,1,15-6.82,1.49,1.49,0,0,0,1.14.26,1.52,1.52,0,0,0,1-.65,13,13,0,0,1,23.58,4.88,1.5,1.5,0,0,0,1.15,1.2,9.84,9.84,0,0,1-1.7,19.43Z"/><path d="M85.06,48.59A12.93,12.93,0,0,0,62.61,43.5a10.16,10.16,0,0,0-12,2,1.5,1.5,0,0,0,2.18,2.07,7.16,7.16,0,0,1,9.37-.9,1.51,1.51,0,0,0,2.12-.39,9.93,9.93,0,0,1,18.06,3.73,1.5,1.5,0,0,0,1.15,1.2A7.46,7.46,0,0,1,82.17,66L82,66H77.84a1.5,1.5,0,0,0,0,3h4.4a1.65,1.65,0,0,0,.32,0,10.46,10.46,0,0,0,2.5-20.38Z"/><path d="M29.58,51.44a1.48,1.48,0,0,0,1.25.68,1.53,1.53,0,0,0,.82-.24,1.51,1.51,0,0,0,.44-2.08,12.54,12.54,0,0,1,8.59-19.24A12.56,12.56,0,0,1,54,37.81a1.5,1.5,0,1,0,2.73-1.23A15.54,15.54,0,1,0,29.58,51.44Z"/><path d="M17.19,26.19l7.55,5.54a1.54,1.54,0,0,0,.88.29,1.5,1.5,0,0,0,.89-2.71L19,23.77a1.5,1.5,0,0,0-1.77,2.42Z"/><path d="M12.26,46.45h.1l9.34-.62a1.5,1.5,0,1,0-.2-3l-9.34.61a1.5,1.5,0,0,0,.1,3Z"/><path d="M61.85,36a1.49,1.49,0,0,0,.61-.13L71,32.07a1.5,1.5,0,1,0-1.21-2.74L61.25,33.1a1.5,1.5,0,0,0,.6,2.87Z"/><path d="M51.23,25.42a1.47,1.47,0,0,0,.67.16,1.5,1.5,0,0,0,1.34-.84l4.14-8.39A1.5,1.5,0,0,0,54.69,15l-4.14,8.4A1.49,1.49,0,0,0,51.23,25.42Z"/><path d="M36.13,22.87A1.5,1.5,0,0,0,37.59,24,1.49,1.49,0,0,0,38,24a1.5,1.5,0,0,0,1.1-1.81l-2.23-9.09a1.5,1.5,0,0,0-2.92.71Z"/></svg>'); /*url(/images/icons/day.svg)*/ }
i.night::before {
	content: url('data:image/svg+xml;utf8, <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 512 640" x="0px" y="0px" style="fill: <?= ($layout === 'night' ? 'rgba(240,196,32,0.6)' : 'rgba(227,103,0,0.65)'); ?>;"><path d="M208.482,181.648a7,7,0,0,0-7.583-1.316A147.006,147.006,0,0,0,112.48,315.191c0,81.056,65.944,147,147,147a147.444,147.444,0,0,0,138.262-96.958,7,7,0,0,0-9.378-8.8,131.911,131.911,0,0,1-53,10.987c-73.337,0-133-59.663-133-133a132.586,132.586,0,0,1,7.911-45.286A7,7,0,0,0,208.482,181.648ZM335.36,381.421a146.375,146.375,0,0,0,42.918-6.359,133.489,133.489,0,0,1-118.8,73.129c-73.336,0-133-59.663-133-133a133.016,133.016,0,0,1,65.851-114.84,147.148,147.148,0,0,0-3.971,34.07C188.36,315.477,254.3,381.421,335.36,381.421Z"/><path d="M336.291,89.831l-33.445-5.309L287.424,53.678a7,7,0,0,0-12.522,0L259.48,84.522l-33.444,5.309a7,7,0,0,0-3.8,11.917l24.38,23.852-5.826,33.368a7,7,0,0,0,10.156,7.4l30.215-15.9,30.215,15.9a7,7,0,0,0,10.156-7.4L315.708,125.6l24.381-23.852a7,7,0,0,0-3.8-11.917Zm-33.008,28.338a7,7,0,0,0-2,6.208l4.01,22.966-20.869-10.985a7,7,0,0,0-6.521,0l-20.869,10.985,4.01-22.966a7,7,0,0,0-2-6.208l-17.064-16.693,23.25-3.691A7,7,0,0,0,270.393,94l10.77-21.542L291.934,94a7,7,0,0,0,5.163,3.782l23.25,3.691Z"/><path d="M479.587,166.426l-21.079-3.347-9.729-19.459a7,7,0,0,0-12.522,0l-9.73,19.459-21.078,3.347a7,7,0,0,0-3.8,11.917L417,193.36l-3.667,21a7,7,0,0,0,10.156,7.4l19.027-10.015,19.026,10.015a7,7,0,0,0,10.156-7.4l-3.667-21,15.352-15.017a7,7,0,0,0-3.8-11.917Zm-23.978,19.5a7,7,0,0,0-2,6.208l1.851,10.6-9.681-5.094a6.994,6.994,0,0,0-6.52,0l-9.681,5.094,1.851-10.6a7,7,0,0,0-2-6.208l-8.034-7.86,10.883-1.728a7,7,0,0,0,5.164-3.782l5.078-10.157L447.6,172.56a7,7,0,0,0,5.163,3.782l10.884,1.728Z"/><path d="M355.894,247.643a7,7,0,0,0-5.572-4.789l-16.989-2.7-7.848-15.694a7,7,0,0,0-12.522,0l-7.847,15.694-16.989,2.7a7,7,0,0,0-3.8,11.917l12.366,12.1-2.953,16.912a7,7,0,0,0,10.156,7.4l15.326-8.067,15.327,8.067a7,7,0,0,0,10.156-7.4l-2.953-16.912,12.365-12.1A7,7,0,0,0,355.894,247.643Zm-26.565,11.795a7,7,0,0,0-2,6.208l1.136,6.509-5.981-3.147a7,7,0,0,0-6.52,0l-5.981,3.147,1.137-6.509a7,7,0,0,0-2-6.208l-5.048-4.939,6.794-1.079a7,7,0,0,0,5.164-3.782l3.195-6.392,3.2,6.392a7,7,0,0,0,5.163,3.782l6.795,1.079Z"/><path d="M73.51,179.879l21.523,11.327a7,7,0,0,0,10.156-7.4l-4.149-23.76,17.366-16.988a7,7,0,0,0-3.8-11.917l-23.837-3.785-11-22a7,7,0,0,0-12.521,0l-11,22-23.837,3.785a7,7,0,0,0-3.8,11.917l17.366,16.988-4.149,23.76a7,7,0,0,0,10.156,7.4ZM48.357,142.787,62,140.621a7,7,0,0,0,5.164-3.782l6.347-12.7,6.348,12.7a7,7,0,0,0,5.164,3.782l13.642,2.166-10.049,9.83a7,7,0,0,0-2,6.208l2.332,13.358-12.176-6.409a7,7,0,0,0-6.521,0l-12.176,6.409,2.332-13.358a7,7,0,0,0-2-6.208Z"/></svg>'); /*url(/images/icons/night.svg)*/ }
i.facebook::before {
	content: url('data:image/svg+xml;utf8, <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: <?= ($layout === 'night' ? 'rgba(204,187,123,1)' : 'rgba(52,69,134,1)'); ?>;"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>'); /*url(/images/icons/facebook-black.svg)*/ }
i.twitter::before {
	content: url('data:image/svg+xml;utf8, <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: <?= ($layout === 'night' ? 'rgba(204,187,123,1)' : 'rgba(52,69,134,1)'); ?>;"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>'); /*url(/images/icons/twitter-black.svg)*/ }
i.github::before {
	content: url('data:image/svg+xml;utf8, <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: <?= ($layout === 'night' ? 'rgba(204,187,123,1)' : 'rgba(52,69,134,1)'); ?>;"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>'); /*url(/images/icons/github-black.svg)*/ }

/**
 * General Styles
 */
h1, h2, h3, h4, h5, h6 {
	font-family: var(--font-family-headline, Arial);
	font-weight: 300;
	line-height: 1.1;
	margin-bottom: 0.3rem;
}
h1 { font-size: 1.2rem; }
h2 { font-size: 1.0rem; }
h3 { font-size: 0.8rem; }
h4 { font-size: 0.7rem; }
h5 { font-size: 0.5rem; }
h6 { font-size: 0.3rem; }
h1:first-of-type, h2:first-of-type { margin-block-start: 0.25em}
p {
	margin-block-start: 0;
	line-height: 1.1;
}
a, a:visited, a img {
	color: var(--color-link-primary, rgba(241,241,241,1));
	text-decoration: none;
}
a:hover { text-decoration: underline; }
a img:hover {
	text-decoration: none;
	box-shadow: 0 0 0 1px #344586;
}
a:active { color: #ccbc7a; }
a img:active {
	box-shadow: 0 0 0 1px #cbba79;
	-webkit-filter: opacity(.1);
	filter: opacity(0.75);
}
pre a, pre a:visited { color: #fcba04; }
code { word-wrap: break-word; }
pre {
	color: #f8f8f2;
	background-color: #23241f;
	overflow: visible;
	white-space: pre-wrap;
	margin-bottom: 5px 0;
	padding: 5px 10px;
	border-radius: 3px;
}
blockquote {
	font: 0.7rem/0.8rem normal;
	margin: 10px 0 20px 10px;
	padding-left: 15px;
	border-left: 3px solid #CBBA79;
}
.center { text-align: center; }
.align-to-text {
	width: 100%;
	height: 100%;
	vertical-align: text-top;
}
.tiny { font-size: 0.35rem; }
.small { font-size: 0.5rem; }
.title {
	height: 20px;
	font-weight: bold;
}
.uppercase { text-transform: uppercase; }
.lowercase { text-transform: lowercase; }
.light { font-weight: lighter; }
.strong { font-weight: bold; }
.primary { background-color: #88b351 !important; color: #fff !important; }
.secondary { background-color: #344586 !important; color: #fff !important; }
.alternate { background-color: #cbba79 !important; }
.disabled { color: #aaa; }
.danger { background-color: #ff4700 !important; }
.warn { color: #ff9800; }
.info { color: #2196f3; }
.success { color: #4caf50; }
.border { outline: var(--outline-table, '1px solid #ccc'); }
.bottom_border { border-bottom: 1px solid var(--border-title, #ccc); }

/** Animations */
	.blink { -webkit-animation:colorchange 1s infinite alternate;
			 animation:colorchange 1s infinite alternate; }
		@-webkit-keyframes colorchange {
			0% { color: inherit; }
			100% { color: #8ed47c; }
		}
		@keyframes colorchange {
			0% { color: inherit; }
			100% { color: #8ed47c; }
		}
/** END: General Styles */

/**
 * Header
 */
header > .logo {
	display: block;
	font-size: 0.85rem;
	font-family: var(--font-family-logo, sans-serif);
	font-weight: 600;
}
header > .announcements {
	display: inline-block;
	font-size: 0.5rem;
	margin-top: 5px;
	margin-bottom: 5px;
	vertical-align: middle;
}
header > .announcements span.event::before {
	content: "\01F5D3";
	font-size: 0.8em;
}
header > .announcements .event > a > .name { }
header > .announcements .event > a.join { }
header > .announcements .event > a.unjoin { color: #cbba79; }
header > .service { text-align: right; }
header > .service h5 {
	margin-top: 0;
	margin-bottom: 0.5em;
}
header > .service .countryflag {
	margin-left: 5px;
	height: 0.85em;
}
header > .service form fieldset { margin-top: 0; }
header > .service form .login-input {
	display: flex;
	white-space: nowrap;
	align-items: flex-start;
	margin: 0;
	padding: 0;
}
header > .service form .login-input label {
	flex: 1;
	margin: 0 0 0 10px;
	padding: 0 2px 0 2px;
	font-size: inherit;
}
header > .service form .login-input a {
	flex: 1;
	font-size: 0.8em;
}
header > .service form .login-input input[type=submit] {
	flex: 1;
}
header > .onlineuser {
	font-size: 0.4rem;
}
header > .onlineuser:not(:empty)::before {
	content: "Online: ";
	font-weight: bold;
}
header > .onlineuser > * {
	white-space: nowrap;
}
header > .notifications {
	font-size: 0.4rem;
	/*margin-top: 5px;*/
}
header > .notifications ul { padding: 0; }
header > .notifications ul li {
	display: inline;
	white-space: nowrap;
	list-style-type: none;
}
header > .notifications ul li + li:not(:empty)::before {
	content: "\a0|\a0";
	color: gray;
}
header > .infos {
	margin-top: 5px;
}
header > .infos .solarstate .event,
header > .infos .solarstate .countryflag {
	height: 1.2em;
	vertical-align: text-top;
}
/** END: Header */

/** Footer */
footer > .shadow {
	border: none;
	box-shadow: <?= ($layout === 'night' ? '0 0px 1px 1px rgba(3,12,22,0.5)' : 'none'); ?>;
}
footer section, .tpl-footer section {
	display: flex;
	justify-content: center;
	align-items: center;
	margin-top: 1em;
}
footer section.flex-one-column, .tpl-footer section {
	flex: 1;
	text-align: center;
}
footer section.flex-two-column {
	flex-flow: nowrap row;
}
footer section > div.icon {
	flex: 1;
	max-width: 2rem;
	margin-right: 0.5rem;
	font-size: 0;
}
/*footer section > div.icon > * {
	width: 100%;
	object-fit: contain;
	filter: drop-shadow(-1px -1px 0 rgba(0,0,0,.25));
}*/
footer section > div.icon > svg, footer section > div.icon > svg * path { fill: var(--color-font-primary, #777); }
/*footer section > div.icon > object { filter: invert(1); } => make svg white */
footer section > div.data {
	flex: 2;
}
footer ul, footer li, .tpl-footer ul, .tpl-footer li {
	list-style-type: none;
	margin: 0;
	padding: 0;
}
footer ul, .tpl-footer ul {
	padding-bottom: 0.25em;
}
footer ul li, .tpl-footer ul li {
	display: inline;
}
footer ul li + li::before, .tpl-footer ul li + li::before {
	content: "\a0|\a0";
	color: gray;
}
footer #swisstime { font-size: 0.7rem; }
/** END: Footer */

/** Navigation */
/** END: Navigation */

/**
 * Elements styles
 */
/** Alert message boxes */
.alert {
	  padding: 10px;
	  margin-bottom: 15px;
	  background-color: #f44336; /** Red */
	  color: white;
	  cursor: pointer;
	  opacity: 0.85;
	  -webkit-transition: opacity 0.3s;
	  -moz-transition: opacity 0.3s;
	  -o-transition: opacity 0.3s;
	  -ms-transition: opacity 0.3s;
	  transition: opacity 0.3s;
}
.alert.success { background-color: #4caf50 } /**  Green */
.alert.info { background-color: #2196F3; } /** Blue */
.alert.warn { background-color: #ff9800; } /**  Orange */
.alert a { color: #3f3047; } /** Links in Alert message boxes */
/** Alert message box close button */
.closebtn {
	  margin-left: 15px;
	  color: white;
	  font-weight: bold;
	  float: right;
	  font-size: 22px;
	  line-height: 10px;
	  cursor: pointer;
	  -webkit-transition: 0.3s;
	  -moz-transition: 0.3s;
	  -o-transition: 0.3s;
	  -ms-transition: 0.3s;
	  transition: 0.3s;
}
.closebtn:hover {color: black;}

/** Activities */
div.zorg-activities-list { }
div.zorg-activity {
	position:relative;
	width:100%;
	height:90px;
	display:block;
}
div.activity-left {
	position:relative;
	width:150px;
	height:150px;
	float:left;
	display:block;
}
div.activity-right {
	position:relative;
	width:100%;
	height:150px;
	float:right;
	clear:right;
	display:block;
}
div.activity-content {
	position:relative;
	width:100%;
	height:100%;
	display:inline;
}
div.activity-footer {
	position:relative;
	width:100%;
	height:25px;
}
/** END: Activities */

/** Commenting */
table.forum { width:100%; }
td.forum {
	word-wrap: break-word;
	margin: 0;
	padding: 0;
}
td.forum.comment > h1:first-of-type,
td.forum.comment > h2:first-of-type,
td.forum.comment > h3:first-of-type,
td.forum.comment > h4:first-of-type { margin-block-start: .5em; }
td.forum img {
	max-width: 100%;
}
td.forum.comment { padding: 0 .5em 0 .5em; }
td.forum.comment.meta { padding-top: .2em; }
td.forum.comment.meta.left { padding-left: .5em; }
td.forum.comment.meta.right { padding-right: .5em; }
.threading {
	border: none;
	vertical-align: top;
	min-height: 16px;
	min-width: 16px;
	background-repeat: no-repeat;
}
a.threading, span.threading {
	display: inline-block;
	background-color: #f2f2f2;
	margin-left: -1px;
}
a.threading.switch:hover { box-shadow: 0 0 0 1px #344586; }
a.threading.switch.expand { background: url('/images/forum/<?= $layout ?>/plus.gif') no-repeat; }
a.threading.switch.collapse { background: url('/images/forum/<?= $layout ?>/minus.gif') no-repeat; }
span.threading.split { background: url('/images/forum/<?= $layout ?>/split.gif'); }
td.threading.collapsed { background: url('/images/forum/<?= $layout ?>/minus.gif'); }
td.threading.space { background: url('/images/forum/<?= $layout ?>/space.gif') repeat-y; }
td.threading.vertline { background: url('/images/forum/<?= $layout ?>/vertline.gif') repeat-y; }
td.threading.end { background: url('/images/forum/<?= $layout ?>/end.gif') no-repeat; }
input.replybutton { margin: 0; }

/** Quill */
#form-container {
	background: var(--background-color-main, #000);
	margin-top: 10px;
}
.commenting {
	height: 100%;
	bottom: 0;
}
#schickenaaab {
	margin-left: 15px;
	bottom: 0;
	right: 0;
}
.ql-htmleditor {
	position: absolute;
	top: 0;
	bottom: 0;
	right: 0;
	left: 0;
	border: none;
}
.ql-container.ql-snow {
	background: var(--background-color-base, #444);
	border: 0px !important;
	margin: 5px 0 15px 0;
	font-size: 1em;
}
.ql-toolbar.ql-snow {
	background: var(--background-color-main, #000);
	border-top: 0 !important;
	border-right: 0 !important;
	border-left: 0 !important;
	border-bottom: 0 !important;
}
.ql-snow .ql-picker { color: var(--color-font-input, #444) !important; }
.ql-snow .ql-picker-options { background-color: var(--background-color-base, #444) !important; }
.ql-snow .ql-stroke { stroke: var(--color-font-input, #444) !important; }
.ql-snow.ql-toolbar button, .ql-snow .ql-toolbar button { color: var(--color-font-input, #444) !important; }
.ql-showHtml { color: var(--color-font-input, #444); font-size: 0.6em; }
.ql-showHtml:after { content: "html" }
.ql-memberOnly { color: var(--color-font-input, #444); }
.ql-memberOnly:after { content: "[z]" }
.ql-editor h1, .ql-editor h2, .ql-editor h3, .ql-editor h4, .ql-editor h5, .ql-editor h6 {
	font-family: var(--font-family-body, '-apple-system, BlinkMacSystemFont, "Segoe UI", Arial') !important;
	font-weight: 300 !important;
	line-height: 1.1 !important;
	margin-bottom: 0.3rem !important;
}
.ql-editor h1 { font-size: 1.2rem !important; }
.ql-editor h2 { font-size: 1.0rem !important; }
.ql-editor h3 { font-size: 0.8rem !important; }
.ql-editor h4 { font-size: 0.7rem !important; }
.ql-editor h5 { font-size: 0.5rem !important; }
.ql-editor h6 { font-size: 0.3rem !important; }
.ql-editor h1:first-of-type, .ql-editor h2:first-of-type { margin-block-start: 0.25em !important; }
.ql-editor p {
	margin-bottom: 20px !important;
	margin-block-start: 0 !important;
	line-height: 1.1 !important;
}
.ql-editor blockquote {
	font: 0.7rem/0.8rem normal !important;
	font-style: italic !important;
	margin: 10px 0 20px 10px !important;
	padding-left: 15px !important;
	border-left: 3px solid #cbba79 !important;
}
.ql-editor p a, .ql-editor p a:active { color: var(--color-link-primary, #444); }
.ql-editor p a:hover { text-decoration: underline }
.tribute-container ul {
	background: #42300A !important;
	border: 1px solid #62502A !important;
}
.tribute-container li.highlight, .tribute-container li:hover { background: #62502A !important; }
.ql-editor code, .ql-snow.ql-editor pre {
	color: #f8f8f2 !important;
	padding: 5px 10px !important;
	background-color: #23241f !important;
}
a.mention, a.mention:visited {
	color: #344586 !important;
	text-decoration: none;
}
/** END: Commenting */

/** Content tables */
table thead, table.header {
	background: var(--background-color-main, 'none');
	margin: 0px;
}

table.shadedcells tr { background-color: var(--background-color-behind, 'none'); border-bottom: var(--outline-table, '1px solid #ccc'); }
/*table.shadedcells tr:nth-child(even) { background-color: #eee; }
table.shadedcells tr:nth-child(odd) { background-color: #ccc; }*/

table.border tr td {
	padding: 3px 6px;
}

table.dreid {
	background-color: #E5E5E5; /*EDF2F2*/
	
	border-bottom-style: solid;
	border-bottom-color: #CCCCCC; /*DDD*/
	border-bottom-width: 1px;	

	border-left-style: solid;
	border-left-color: #FFF;
	border-left-width: 1px;

	border-right-style: solid;
	border-right-color: #CCCCCC; /*DDD*/
	border-right-width: 1px;	
	
	border-top-style: solid;
	border-top-color: #FFF;
	border-top-width: 1px;
}

td.addletd {
border-style:solid;
border-color: #CCCCCC;
border-width:1px;
font-size: 22px;
	 text-align: center;
}

/** Shoot the Lamber */
table.stl { }
table.stl tr td del, table.stl tr td del .profilepic {
	filter: grayscale(100%);
	-webkit-filter: grayscale(100%);
	text-decoration: line-through;
	color: #666;
}
/** END: Tables */


/** Menu navigation */
div.menu {
	font-size: 0.9em;
	background-color: var(--background-color-navigation, #bdcff5);
	border-bottom: solid 1px var(--border-navigation, #ccc);
	border-top: solid 1px var(--border-navigation, #ccc);
	letter-spacing: 1px;
	padding-bottom: 1px;
	padding-top: 1px;
}

div.menu a {
	text-decoration: none;
	color: var(--color-link-navigation, #333);
	border-left: 1px solid var(--border-navigation, #ccc);
	border-right: 1px solid var(--border-navigation, #ccc);
	padding-left: 15px;
	padding-right: 15px;
}

div.menu a:hover {
	color: var(--color-link-navigation, #9dafd5);
	background: var(--background-color-navigation-hover, #9dafd5);
}

div.menu a:active {
	text-decoration: underline;
}

div.menu a.left {
	border-left-style: none;
	border-right-style: none;
	padding-left: 0px;
	padding-right: 1px;
}

div.menu a.right {
	border-left-style: none;
	border-right-style: none;
	padding-left: 1px;
	padding-right: 0px;
}	
/** END: Menu navigation */

/**
 * Form elements
 */
fieldset {
	border: none;
	margin: 10px 0 10px 0;
	padding: 0;
}

label {
	font-size: 1.2em;
	margin-bottom: 10px;
}

input, textarea, select, button {
	color: var(--color-font-input, #fff);
	background: var(--background-color-input, #BDCFF5);
	font-size: 0.8em;
	margin: 3px 10px 10px 0;
}

input[type=text], input[type=password], input[type=search], input[type=number], textarea, input.text {
	padding: 5px 10px;
	border: none;
	outline: var(--outline-input, 'solid 1px #ccc');
}

input:focus, textarea:focus, select:focus, option:focus {
	border: none;
	outline: var(--outline-input-focus, 'solid 1px #ccc');
	outline-offset: 0px; /** Prevent default user agent styling behaviour */
}

textarea {
	overflow: auto;/** Remove the default vertical textarea scrollbar in IE 10+ */
	resize: vertical;
}

input[type=checkbox], input[type=radio] {
	border: var(--border-input, 'none');
	outline: none; /** Revert input:focus */
    vertical-align: middle; /** Adjust positioning of checkbox & radio boxes */
    transform: scale(1.2); /** Adjust positioning of checkbox & radio boxes */
    filter: var(--filter-invert, 'none');
}

button, input[type=button], input[type=submit], .button, dialog button {
	border: var(--outline-input, 'solid 1px #ccc');
	border-radius: 5px;
	padding: 5px 10px;
	background: var(--background-color-input, '#fff');
}
.button, dialog button {
	font-size: 0.4rem;
}

button:hover, input[type=button]:hover, input[type=submit]:hover, .button:hover, dialog button:hover {
	border: var(--border-input-button-hover, 'none');
	background: var(--background-color-input-button-hover, '#ccc');
}

button:disabled, button[aria-disabled=true], .button:disabled, .button:disabled:hover {
	border-style: outset;
	background-color: var(--border-input-disabled, graytext);;
}

select {
	display: inline-block;
	box-sizing: border-box;
	border: var(--outline-input, 'solid 1px #ccc');
	box-shadow: 0 1px 0 1px rgba(0,0,0,.04);
	-moz-appearance: none;
	-webkit-appearance: none;
	appearance: none;
	padding: 5px 25px 5px 10px;
	background-color: var(--background-color-input, #ccc);
	background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23888%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'),
	  linear-gradient(to bottom, var(--background-color-input, #ccc) 0%,var(--background-color-input, #ccc) 100%);
	background-repeat: no-repeat, repeat;
	background-position: right .7em top 50%, 0 0;
	background-size: .65em auto, 100%;
	outline: none;
}
select[multiple] { background-image: none; }
select::-ms-expand { display: none; }
select:hover {
	border: var(--border-input-button-hover, 'none');
	outline: none;
}
select:focus {
	border: var(--border-input-button-hover, 'none');
	box-shadow: var(--shadow-input-focus-moz_mac, '0 0 0 3px -moz-mac-focusring');
	box-shadow: var(--shadow-input-focus, '0 0 1px 3px rgba(59, 153, 252, 0.7)');
	color: var(--color-font-input, '#ccc');
	outline: none;
}
select option { font-weight:normal; }
select:disabled, select[aria-disabled=true] {
	color: var(--border-input-disabled, graytext);
	background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22graytext%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'),
	  linear-gradient(to bottom, var(--color-font-input-disabled, graytext) 0%,var(--color-font-input-disabled, graytext) 100%);
}
select:disabled:hover, select[aria-disabled=true] { border-color: var(--border-input-disabled, graytext); }

input[name=score] {
	
}
/** END: Form elements */


/** HTML5.2 Modial Dialog */
dialog {
	padding: 0;
	border: 0;
	border-radius: 0.6rem;
	color: inherit;
	box-shadow: 0 0 1em black;
}

/** native backdrop */
dialog::-webkit-backdrop {
	background-color: rgba(0, 0, 0, 0.4);
}
dialog::backdrop {
	background-color: rgba(0, 0, 0, 0.4);
}

/** polyfill backdrop */
dialog + .backdrop {
	position: fixed;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	background-color: rgba(0, 0, 0, 0.8);
}

dialog[open] {
	-webkit-animation: slide-up 0.4s ease-out;
	        animation: slide-up 0.4s ease-out;
}


/** The following styles are for older browsers when using the polyfill. These aren’t necessary for Chrome/Firefox. */
dialog {
	display: none;
	position: absolute;
	margin: 0 auto;
	/** should center it, but not working in Safari */
	max-width: 80vw;
	color: inherit;
	background-color: white;
}
dialog[open] { display: block; }
/** prettying things up a bit */
.close {
	position: absolute;
	top: 0.2em;
	right: 0.2em;
	padding: 0.3em;
	line-height: 0.6;
	color: red;
	background-color: transparent;
	border: 0;
}
.modal-header,
.modal-body,
.modal-footer { padding: 1em; }
.modal-header {
	margin: 0;
	padding-bottom: 0.6em;
	padding-right: 2.5em;
	background-color: #BDCFF5;
	border-top-left-radius: 0.6rem;
	border-top-right-radius: 0.6rem;
}
.modal-footer { border: 0; }
.modal-footer button:last-child { float:right; }

/** Profile Pics - Circle Style */
div.profilepic {
	display: inline-block;
	text-align: center;
	padding: 4px;
}
div.profilepic img {
	width: 65px;
	height: 65px;
	background-size: cover;
	border: 0;
	border-radius: 50%;
	-webkit-border-radius: 50%;
}
div.profilepic a img:hover {
	text-decoration: none;
	box-shadow: 0 0 0 1px #344586;
}