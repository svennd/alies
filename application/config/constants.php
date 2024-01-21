<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') or define('SHOW_DEBUG_BACKTRACE', true);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  or define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') or define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   or define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  or define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           or define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     or define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       or define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  or define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   or define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              or define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            or define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       or define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        or define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          or define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         or define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   or define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  or define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') or define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     or define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       or define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      or define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      or define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

# custom defines
define('SYSTEM', -1);

# log / alert levels
define('FATAL', 1);
define('ERROR', 2);
define('WARN', 3);
define('INFO', 4);
define('DEBUG', 5);

# event status
define('STATUS_OPEN', 0); /* working bill */
define('STATUS_CLOSED', 1); /* products out of stock, price final */
define('STATUS_HISTORY', 2); /* added report now only for historical purpose */


## bill constants
# status
define('BILL_INVALID', -1);     # generic invalid
define('BILL_DRAFT', 0);        # when bill_id is created and assigned to owner
define('BILL_PENDING', 1);      # the bill is calculated
define('BILL_UNPAID', 2);       # ???
define('BILL_INCOMPLETE', 3);   # incomplete or no payment, but stock has been dropped
define('BILL_PAID', 4);         # fully payed bill, invoice_id generated
define('BILL_OVERDUE', 5);      # not paid and beyond due date -- not implemented
define('BILL_ONHOLD', 6);       # might be complete, but verficiation needed -- not implemented
define('BILL_HISTORICAL', 7);   # imported bills

# event / bill payed
define('PAYMENT_OPEN', 0); // init
define('NO_BILL', 0);
define('PAYMENT_UNPAID', 1); 		// calculated bill
define('PAYMENT_PARTIALLY', 2); 	// payed partially
define('PAYMENT_PAID', 3);			// fully payed
define('PAYMENT_NON_COLLECTABLE', 4); // can't be collected anymore
define('PAYMENT_PROCESSING', 5);	// not yet verified

# event w/o a bill
# for imported ones
define('EVENT_PAYMENT_IMPORT', -1);

# message state
define('MSG_UNREAD', 0);
define('MSG_READ', 1);

# animal types
define('DOG', 0);
define('CAT', 1);
define('HORSE', 2);
define('BIRD', 3);
define('OTHER', 4);
define('RABBIT', 5);

# biological genders
define('MALE', 0);
define('FEMALE', 1);
define('MALE_NEUTERED', 2);
define('FEMALE_NEUTERED', 3);

# stock states
define('INVALID', -1);
define('STOCK_CHECK', 0);
define('STOCK_IN_USE', 1);
define('STOCK_HISTORY', 2);
define('STOCK_ERROR', 3);
define('STOCK_MERGE', 4);

# report status
define('REPORT_INIT', 0);
define('REPORT_OPEN', 1);
define('REPORT_DONE', 2);
define('REPORT_FINAL', 2);

# event types
define('DISEASE', 0);
define('OPERATION', 1);
define('MEDICINE', 2);
define('LAB', 3);

## payment constants
# method
define('PAYMENT_CASH', 0);  # cash money
define('PAYMENT_CARD', 1);  # credit card or debit card
define('PAYMENT_TRANSFER', 2); # by bank transfer

# product or procedure
define('PROCEDURE', 0);
define('PRODUCT', 1);
define('PRODUCT_BARCODE', 2);

# procedure flags
define('FLAG_PET_DEAD', 1); # mark the pet as passed away
define('FLAG_NEUTERED', 2); # mark the pet as neutered

# pdf modes
define('PDF_DOWNLOAD', 1); # download file
define('PDF_STREAM', 2); # view file
define('PDF_FILE', 3); # store on filesystem

# gs1 code
define('GS1_CODE', 26);

# define structure message - limited to 10 useable chars
define('CLIENT_BILL', 0); # default attempts to add CLIENT_ID + BILL_ID 
define('CLIENT', 1); # client id only
define('CLIENT_3DIGIT_BILL', 2); # client id (up to 7) + 3 digit bill id