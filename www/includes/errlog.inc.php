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
     * @param array  $origina (Optional) Origin details from where a log message was triggered from.
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
     * @return array The origin details including file, function, and line.
     */
    private function getOrigin()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
		// 1=self::getOrigin() | 2=self::debug()/self::warn()/... | 3=self::__construct()

        $origin = [
            'function' => '',
            'file' => '',
            'line' => 0
        ];
		$debuggerMethods = ['debug', 'info', 'warn', 'write'];

        if (isset($backtrace[1])) {
            if (isset($backtrace[1]['function'])) {
				/** Only log the Function name, if it's not from this Debugger Class */
				if (isset($backtrace[1]['function']) && !in_array($backtrace[1]['function'], $debuggerMethods)) {
					$origin['function'] = $backtrace[1]['function'];
				}
				/** When the Function is a Class Constructor, then log its Class Name instead */
				elseif ($backtrace[1]['function'] === '__construct' && isset($backtrace[1]['class']) ) {
					/** However, when it's the Debugger's Class Name, then fall back to use the Origin File reference */
					if ($backtrace[1]['class'] !== __CLASS__) {
						$origin['function'] = $backtrace[1]['class'];
					} else {
						$origin['function'] = basename($backtrace[1]['file']);
					}
				}
            }
            elseif (isset($backtrace[1]['file'])) {
                $origin['function'] = basename($backtrace[1]['file']);
            }
			if (isset($backtrace[1]['file'])) {
                $origin['file'] = basename($backtrace[1]['file']);
            }
            if (isset($backtrace[1]['line'])) {
                $origin['line'] = $backtrace[1]['line'];
            }
        }

        return $origin;
    }
}
