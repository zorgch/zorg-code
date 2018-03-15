<?php
/**
 * Include Telegram Bot Configs
 */
if (!defined('TELEGRAM_BOT')) define('TELEGRAM_BOT', 'zbarbaraharris_bot', true);
if ( file_exists(__DIR__.'/../../'.TELEGRAM_BOT.'.php') ) require_once( __DIR__ . '/../../' . TELEGRAM_BOT.'.php' );
