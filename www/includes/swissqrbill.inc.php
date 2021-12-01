<?php
/**
 * Swiss QR-Bill Integration
 *
 * Integriert mit der Vendor Library sprain/swiss-qr-bill
 * und ermöglicht die Ausgabe von QR-Codes die zum Scannen
 * bei Schweizer Banken / Banktransfers kompatibel sind.
 * Vordefinierte Angaben für die Bezahlung von jährlichen
 * zorg Verein Mitgliederbeiträgen.
 *
 * @author		IneX
 * @package		zorg\Verein
 */

/**
 * Load the Swiss QR Bill library
 *
 * @include TELEGRAM_BOT.php Include Telegram Bot Configs
 */
use Sprain\SwissQrBill as QrBill;

if ( file_exists(COMPOSER_AUTOLOAD) ) require_once COMPOSER_AUTOLOAD;


/**
 * zorg Swiss QR Bill Class
 *
 * In dieser Klasse befinden sich alle Funktionen zum Erstellen und Ausgeben einer Swiss QR Bill QR-Code für den zorg Verein
 *
 * @author		IneX
 * @package		zorg\Verein
 * @version		1.0
 * @since		1.0 `01.12.2021` `IneX` Initial integration
 */
class zorgSwissQRBill
{
	/**
	 * Class constants
	 *
	 * @const STORE_QRCODEIMAGES_DIR Path to directory where generated QR-Code image files (png, svg) can be stored.
	 */
	const STORE_QRCODEIMAGES_DIR = PHP_IMAGES_DIR . 'swissqrbill';

	/**
	 * Generate QR-Code
	 *
	 * Method validates data-input and generates a Swiss QR Bill code (returned as function result).
	 * The result is a base64-encoded PNG image string. E.g.:
	 * 		data:image/png;base64,iVBOR…zX9QM11Gmd5OyAAAAAElFTkSuQmCC
	 *
	 * @author	IneX
	 * @version	1.0
	 * @since	1.0 `01.12.2021` `IneX` Method added
	 *
	 * @uses Sprain\SwissQrBill
	 * @uses ZORG_VEREIN_NAME, ZORG_VEREIN_STRASSE, ZORG_VEREIN_PLZ, ZORG_VEREIN_ORT, ZORG_VEREIN_LAND_ISO2, ZORG_VEREIN_KONTO_IBAN, ZORG_VEREIN_KONTO_IBAN_QRBILL, ZORG_VEREIN_KONTO_BESRID
	 * @param	integer|null	$userId				(Optional) If specific to a zorg User: his/her ID (will be used as internal ID in combination with a BESR-ID). Otherwise null.
	 * @param	string|null		$paymentDescription	(Optional) Human-readable info about what the bill is for. E.g. "Gefälligkeiten"
	 * @param	float|null		$paymentValue		(Optional) CHF-Amount as float number (e.g. 23.17) with the amount that shall be invoiced. Null if no fixed amount shall be used.
	 * @global	object			$user			Globales Class-Object mit den User-Methoden & Variablen
	 * @return	string							Returns a string containing a base64-encoded PNG image.
	 */
	public function generateQRCode($userId=null, $paymentDescription=null, $paymentValue=null)
	{
		global $user;

		/** Validate Params */
		$paymentByUser = (!empty($userId) ? filter_var($userId, FILTER_VALIDATE_INT, ['flags' => FILTER_NULL_ON_FAILURE]) : null);
		$paymentAmount = (!empty($paymentValue) ? filter_var($paymentValue, FILTER_VALIDATE_FLOAT, ['flags' => FILTER_NULL_ON_FAILURE]) : null);

		// Create a new instance of QrBill, containing default headers with fixed values
		$qrBill = QrBill\QrBill::create();

		/**
		 * Zahlungsempfänger (zorg Verein)
		 */
		// Add creditor information (who will receive the payment and to which bank account?)
		$qrBill->setCreditor(
			QrBill\DataGroup\Element\CombinedAddress::create(
				ZORG_VEREIN_NAME,
				ZORG_VEREIN_STRASSE,
				sprintf('%d %s', ZORG_VEREIN_PLZ, ZORG_VEREIN_ORT),
				ZORG_VEREIN_LAND_ISO2
			));
		$qrBill->setCreditorInformation(
			QrBill\DataGroup\Element\CreditorInformation::create(
				(null !== ZORG_VEREIN_KONTO_BESRID && null !== ZORG_VEREIN_KONTO_IBAN_QRBILL ? ZORG_VEREIN_KONTO_IBAN_QRBILL : ZORG_VEREIN_KONTO_IBAN) // Use a classic IBAN. QR-IBANs will only be valid in combination with Payment Reference (BESR-ID + internal ID).
			));

		/**
		 * Rechnungsbetrag
		 */
		// Add payment amount information. Note: the currency must be defined.
		$finalPaymentAmount = ($paymentAmount > 0 ? $paymentAmount : 0.00);
		if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Payment Amount: %f', __METHOD__, __LINE__, $finalPaymentAmount));

		$qrBill->setPaymentAmountInformation(
			QrBill\DataGroup\Element\PaymentAmountInformation::create(
				(null !== ZORG_VEREIN_KONTO_CURRENCY ? ZORG_VEREIN_KONTO_CURRENCY : 'CHF'),
				$finalPaymentAmount
			));

		/**
		 * Zahlungsreferenz / Identifizierung
		 */
		// Add payment reference
		if (empty(ZORG_VEREIN_KONTO_BESRID))
		{
			// Explicitly define that no reference number will be used by setting TYPE_NON.
			$qrBill->setPaymentReference(
				QrBill\DataGroup\Element\PaymentReference::create(
					QrBill\DataGroup\Element\PaymentReference::TYPE_NON
				));

		} else {
			// This is what you will need to identify incoming payments.
			$referenceNumber = QrBill\Reference\QrPaymentReferenceGenerator::generate(
					ZORG_VEREIN_KONTO_BESRID,  // You receive this number from your bank (BESR-ID). Unless your bank is PostFinance, in that case use NULL.
					(!empty($paymentByUser) ? $paymentByUser : time()) // A number to match the payment with your internal data, e.g. an invoice number
				);
			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Reference Number: %s', __METHOD__, __LINE__, $referenceNumber));

			$qrBill->setPaymentReference(
				QrBill\DataGroup\Element\PaymentReference::create(
					QrBill\DataGroup\Element\PaymentReference::TYPE_QR,
					$referenceNumber
				));
		}

		/**
		 * Rechnungsinformationen
		 */
		if (!empty($paymentDescription) || !empty($paymentByUser))
		{
			$additionalInfotext = (!empty($paymentByUser) ? $user->id2user($paymentByUser) : null).
								   (!empty($paymentByUser) && !empty($paymentDescription) ? ' / ' : null).
								   (!empty($paymentDescription) ? $paymentDescription : null);

			if (DEVELOPMENT === true) error_log(sprintf('[DEBUG] <%s:%d> Additional Infotext: %s', __METHOD__, __LINE__, $additionalInfotext));
			// Optionally, add some human-readable information about what the bill is for.
			$qrBill->setAdditionalInformation(
				QrBill\DataGroup\Element\AdditionalInformation::create(
					$additionalInfotext
				)
			);
		}

		/**
		 * QR-Code ausgeben
		 */
		// Time to output something!
		try {
			/* // Write the QR code image files...
			$qrBill->getQrCode()->writeFile(__DIR__ . '/qr.png');
			$qrBill->getQrCode()->writeFile(__DIR__ . '/qr.svg');*/

			return $qrBill->getQrCode()->writeDataUri();
		}
		catch (Exception $e) {
			foreach($qrBill->getViolations() as $violation) {
				print $violation->getMessage()."\n";
			}
			exit;
		}
	}
}
