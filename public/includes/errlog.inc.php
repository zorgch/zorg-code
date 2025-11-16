<?php
/** ERROR-HANDLER SETTINGS */
if (!isset($errlog_settings))
{
	$errlog_settings = array(
		'display' => array(
			'fatal' => true,
			'error' => true,
			'warning' => true,
			'unknown' => false),  // schaltet das wenn möglich nicht ein - z.b. auf der startseite gibt das alle 6500 (!) errors aus
			'errlog' => array(
				'fatal' => true,
				'error' => true,
				'warning' => true,
				'unknown' => false)  // schaltet das wenn möglich nicht ein - z.b. auf der startseite speichert das alle 6500 (!) errors im file (das gibt ca. 1MB)
			);
		}

if (!defined('FATAL')) define('FATAL', E_USER_ERROR);
if (!defined('ERROR')) define('ERROR', E_USER_WARNING);
if (!defined('WARNING')) define('WARNING', E_USER_NOTICE);

//error_reporting(FATAL | ERROR | WARNING);
//set_error_handler('zorgErrorHandler');

function zorgErrorHandler ($errno, $errstr, $errfile, $errline)
{
	global $errlog_settings;

	switch ($errno) {
		case FATAL:
			$prefix = "FATAL Error";
			$errtype = 'fatal';
		break;
		case ERROR:
			$prefix = "Error";
			$errtype = 'error';
		break;
		case WARNING:
			$prefix = "Warning";
			$errtype = 'warning';
		break;
		default:
			$prefix = "Unkown error type";
			$errtype = 'unknown';
		break;
	}

	$time = date("Y-m-d H:i:s");
	$errstr = str_replace(array("\r", "\n"), '', $errstr); // Remove all line breaks from $errstr
	$str = "[$time] [$prefix<$errno>] $errstr ($errfile : $errline)\n";

	if ($errlog_settings['display'][$errtype]) {
		echo $str . '<br />';
	}
	if ($errlog_settings['errlog'][$errtype]) {
		//$filename = date("Y-m-d") . '.log';
		//error_log($str, 3, ERRORLOG_DIR.$filename);
		error_log($str, 3, ERRORLOG_FILE);
	}

	if ($errno == FATAL) exit(1);
}

/**
 * zorg Code Debugger & Error Logger
 *
 * Helps debugging when developing locally & verbosing to the error_log().
 * I am annoyed by constant messages spamming the Logoutput, so
 * this should help to focus better on parts of the code that
 * currently are being worked on (and not distract with tons of
 * other messages in the log).
 *
 * @example zorgDebugger::log()->debug('Required SQL-Query update: <%s> in %s:%d', [$funktion, $file, $line], 'DEPRECATED');
 * @example zorgDebugger::log()->error('The provided ID "%d" is invalid!', [$tplID]);
 *
 * @version 1.0
 * @since 1.0 `26.12.2024` `IneX` Class added
 */
class zorgDebugger
{
	/**
     * @var bool $isDevelopmentEnvironment Indicates if the current environment is a development environment.
     * @var object $instance Stores a Singleton instance of this Class
     */
    public $isDevelopmentEnvironment;
	private static $instance = null;

	/**
     * Constructor for Errorlog.
     * Initializes the class and sets the development environment status.
	 * @uses DEVELOPMENT
     */
    public function __construct()
    {
        $this->isDevelopmentEnvironment = defined('DEVELOPMENT') && DEVELOPMENT;
		$this->debug('%s', [$this->isDevelopmentEnvironment ? 'Development Environment' : 'Non-Dev Environment']);
		$this->debug('SITE_HOSTNAME: %s', [SITE_HOSTNAME]);
    }

	/**
     * Gets a Singleton instance of the zorgDebugger class.
	 *
	 * This allows to call zorgDebugger::log()->... WITHOUT instantiating the Class manually,
	 * and WITHOUT including it or using something like "global $errlog;".
     *
     * @return zorgDebugger The singleton instance of the Errorlog class.
     */
	public static function log(): zorgDebugger
    {
        if (self::$instance === null) {
            self::$instance = new zorgDebugger();
        }

        return self::$instance;
    }

	/**
     * Logs a debug message.
	 * Only log debug if in a development environment;
	 * and only if either no debug scope was defined - or the debug scope matches the origin.
     *
     * @param string $message The message format string.
     * @param array  $params  The parameters to be inserted into the message format string.
     * @param string $customLoglevel Overwrite the [DEBUG] loglevel using a custom value. E.g. DEPRECATED
     */
    public function debug($message, $params = [], $customLoglevel='DEBUG')
    {
		/** Determines if a message should be logged based on the origin. */
        if ($this->isDevelopmentEnvironment) {
			$origin = $this->getOrigin();

			if (empty(ERRORLOG_DEBUG_SCOPE) ||
				(in_array($origin['function'], ERRORLOG_DEBUG_SCOPE) ||
				in_array(basename($origin['file']), ERRORLOG_DEBUG_SCOPE)))
				{
            		$this->write($customLoglevel, $message, $params, $origin);
        	}
        }
    }

	/**
     * Logs an info message.
     *
     * @param string $message The message format string.
     * @param array  $params  The parameters to be inserted into the message format string.
     */
    public function info($message, $params = [])
    {
        $this->write('INFO', $message, $params, $this->getOrigin());
    }

	/**
     * Logs an error message.
     *
     * @param string $message The message format string.
     * @param array  $params  The parameters to be inserted into the message format string.
     */
    public function error($message, $params = [])
    {
        $this->write('ERROR', $message, $params, $this->getOrigin());
    }

	/**
     * Logs a warning message.
     *
     * @param string $message The message format string.
     * @param array  $params  The parameters to be inserted into the message format string.
     */
    public function warn($message, $params = [])
    {
        $this->write('WARNING', $message, $params, $this->getOrigin());
    }

	/**
     * Handles the logging of a message.
	 * Example: [LOGLEVEL] <FUNCTION:LINE> PARSED-MESSAGE
     *
     * @param string $level   The log level (e.g. DEBUG, INFO, ERROR).
	 * @param string $message The message format string.
	 * @param array  $params  The parameters to be inserted into the message format string.
     * @param array  $origin (Optional) Origin details from where a log message was triggered from.
     */
    private function write($level, $message, $params, $origin = [])
    {
		$logOrigin = (!empty($origin['function']) ? $origin['function'] : (!empty($origin['file']) ? $origin['file'] : ''));
		$logLine = (!empty($origin['line']) ? ':'.$origin['line'] : '');
		$formattedMessage = vsprintf($message, $params);
		$logMessage = sprintf('[%s] <%s%s> %s', $level, $logOrigin, $logLine, $formattedMessage);
		error_log($logMessage);
    }

	/**
     * Retrieves the origin details of the log message.
	 *
	 * @version 1.1
	 * @since 1.0 `IneX` method added
	 * @since 1.1 `06.12.2024` `IneX` fixes PHP Notices for undefined offset and array offset of type null
     *
     * @return array The origin details including file, function, and line.
     */
    private function getOrigin()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
        $debuggerMethods = ['debug', 'info', 'warn', 'write'];
        $step = 2;

        $origin = [
            'function' => 'nofunc',
            'file' => 'nofile',
            'line' => 0
        ];

        // Check if $step exists in backtrace before accessing
        if (isset($backtrace[$step])) {
            // Function
            if (isset($backtrace[$step]['function']) && !in_array($backtrace[$step]['function'], $debuggerMethods)) {
                if ($backtrace[$step]['function'] === '__construct' && isset($backtrace[$step]['class'])) {
                    if ($backtrace[$step]['class'] !== __CLASS__) {
                        $origin['function'] = $backtrace[$step]['class'];
                    } elseif ($backtrace[$step]['class'] === __CLASS__ && isset($backtrace[$step+1]['class'])) {
                        $origin['function'] = $backtrace[$step+1]['class'];
                    } else {
                        $origin['function'] = basename($backtrace[$step]['file']);
                    }
                } elseif (isset($backtrace[$step]['function'])) {
                    $origin['function'] = $backtrace[$step]['function'];
                }
            }
            // File and Line
            if (isset($backtrace[$step]['file'])) {
                $origin['file'] = basename($backtrace[$step]['file']);
                $origin['line'] = isset($backtrace[$step]['line']) ? $backtrace[$step]['line'] : 0;
            }
        }

        return $origin;
    }
}
