<?php
/**
 * Migration Script to modify various columns in existing `user` table
 *
 * @author IneX
 * @package zorg\Scripts
 * @version 1.0
 * @since 1.0 <inex> 05.12.2019 Script added
 */

/**
 * Cleanup user db-table column `telegram_chat_id` values '0' => NULL
 *
 * @version 1.0
 * @since 1.0 <inex> 05.12.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
 */
function db_table_user_update($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	/** SQL-Query */
	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Modify `user` table column `telegram_chat_id` */
		$modify_table_query = 'UPDATE `user` SET `telegram_chat_id` = NULL WHERE `telegram_chat_id` = 0';
		$table_modified = $db->query($modify_table_query, __FILE__, __LINE__, __FUNCTION__);

	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> UPDATE `user` SET `telegram_chat_id`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($table_modified !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> UPDATE `user` SET `telegram_chat_id`: SUCCESS', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[ERROR] <%s:%d> UPDATE `user` SET `telegram_chat_id`: FAILED', __FUNCTION__, __LINE__));

		/** Execution time (function) */
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);

		return false;
	}
}

/**
 * Remove column `email_notification` from existing `user` table in zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 05.12.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
 */
function db_table_user_drop_column_emailnotification($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Remove `user` table column 'email_notification' */
		$dropTableColumnQuery = 'ALTER TABLE `user` DROP COLUMN `email_notification`';
		$dropTableColumnResult = $db->query($dropTableColumnQuery, __FILE__, __LINE__, __FUNCTION__);
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `user` DROP COLUMN `email_notification`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($dropTableColumnResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `email_notification`: SUCCESS', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `email_notification`: ERROR', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}

/**
 * Remove column `icq` from existing `user` table in zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 05.12.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
 */
function db_table_user_drop_column_icq($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Remove `user` table column 'icq' */
		$dropTableColumnQuery = 'ALTER TABLE `user` DROP COLUMN `icq`';
		$dropTableColumnResult = $db->query($dropTableColumnQuery, __FILE__, __LINE__, __FUNCTION__);
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `user` DROP COLUMN `icq`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($dropTableColumnResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `icq`: SUCCESS', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `icq`: ERROR', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}

/**
 * Remove column `street` from existing `user` table in zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 05.12.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
 */
function db_table_user_drop_column_street($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Remove `user` table column 'street' */
		$dropTableColumnQuery = 'ALTER TABLE `user` DROP COLUMN `street`';
		$dropTableColumnResult = $db->query($dropTableColumnQuery, __FILE__, __LINE__, __FUNCTION__);
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `user` DROP COLUMN `street`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($dropTableColumnResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `street`: SUCCESS', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `street`: ERROR', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}

/**
 * Remove column `zip` from existing `user` table in zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 05.12.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
 */
function db_table_user_drop_column_zip($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Remove `user` table column 'zip' */
		$dropTableColumnQuery = 'ALTER TABLE `user` DROP COLUMN `zip`';
		$dropTableColumnResult = $db->query($dropTableColumnQuery, __FILE__, __LINE__, __FUNCTION__);
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `user` DROP COLUMN `zip`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($dropTableColumnResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `zip`: SUCCESS', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `zip`: ERROR', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}

/**
 * Remove column `city` from existing `user` table in zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 05.12.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
 */
function db_table_user_drop_column_city($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Remove `user` table column 'city' */
		$dropTableColumnQuery = 'ALTER TABLE `user` DROP COLUMN `city`';
		$dropTableColumnResult = $db->query($dropTableColumnQuery, __FILE__, __LINE__, __FUNCTION__);
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `user` DROP COLUMN `city`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($dropTableColumnResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `city`: SUCCESS', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `city`: ERROR', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}

/**
 * Remove column `phone_home` from existing `user` table in zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 05.12.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
 */
function db_table_user_drop_column_phonehome($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Remove `user` table column 'phone_home' */
		$dropTableColumnQuery = 'ALTER TABLE `user` DROP COLUMN `phone_home`';
		$dropTableColumnResult = $db->query($dropTableColumnQuery, __FILE__, __LINE__, __FUNCTION__);
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `user` DROP COLUMN `phone_home`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($dropTableColumnResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `phone_home`: SUCCESS', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `phone_home`: ERROR', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}

/**
 * Remove column `phone_mobile` from existing `user` table in zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 05.12.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
 */
function db_table_user_drop_column_phonemobile($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Remove `user` table column 'phone_mobile' */
		$dropTableColumnQuery = 'ALTER TABLE `user` DROP COLUMN `phone_mobile`';
		$dropTableColumnResult = $db->query($dropTableColumnQuery, __FILE__, __LINE__, __FUNCTION__);
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `user` DROP COLUMN `phone_mobile`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($dropTableColumnResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `phone_mobile`: SUCCESS', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `phone_mobile`: ERROR', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}

/**
 * Remove column `phone_office` from existing `user` table in zorg DB
 *
 * @version 1.0
 * @since 1.0 <inex> 05.12.2019 Function added
 *
 * @param boolean $dryrun Disable to actually make the changes. Default: TRUE
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @return boolean Result of executing the function
 */
function db_table_user_drop_column_phoneoffice($dryrun=true)
{
	global $db;

	/** Start execution time measurement (function) */
	$startAll = microtime(true);

	if ($dryrun === false) // Only if Dry-run is --OFF--
	{
		/** Remove `user` table column 'phone_office' */
		$dropTableColumnQuery = 'ALTER TABLE `user` DROP COLUMN `phone_office`';
		$dropTableColumnResult = $db->query($dropTableColumnQuery, __FILE__, __LINE__, __FUNCTION__);
	} else { // Dry-run mode is --ON--
		error_log(sprintf('[INFO] <%s:%d> ALTER TABLE `user` DROP COLUMN `phone_office`: DRY-RUN EXIT', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}

	if ($dropTableColumnResult !== FALSE)
	{
		/** SUCCESSFUL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `phone_office`: SUCCESS', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return true;
	} else {
		/** CANCEL FUNCTION EXECUTION */
		error_log(sprintf('[INFO] <%s:%d> `user` DROP COLUMN `phone_office`: ERROR', __FUNCTION__, __LINE__));
		printf('[INFO] <%s:%d> Function execution completed within %g s'."\n", __FUNCTION__, __LINE__, microtime(true) - $startAll);
		return false;
	}
}
