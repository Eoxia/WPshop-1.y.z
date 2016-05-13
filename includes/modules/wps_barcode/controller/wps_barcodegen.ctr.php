<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Generate barcodes with informations
 *
 * @author Christophe DALOZ - DE LOS RIOS - Eoxia dev team <dev@eoxia.com>
 * @version 1.0
*/
class wps_barcodegen {

	private $originalCode = '';
	private $motif = '';
	private $binCode = '';
	private $file = NULL;
	private $log = '';

	private $fixed = '';
	private $country = '';
	private $countryCode = '';
	private $enterprise = '';
	private $ref = '';

	private $y = 180;

	private $setA = array();
	private $setB = array();
	private $setC = array();
	private $ean13 = array();
	private $other_ean13 = array();
	private $config = array();

	public function __construct() {
	$this->setA = array( 0 => '0001101',	1 => '0011001',	2 => '0010011',
		3 => '0111101',	4 => '0100011',	5 => '0110001',	6 => '0101111',
		7 => '0111011',	8 => '0110111',	9 => '0001011'	);

	$this->setB = array( 0 => '0100111', 1 => '0110011', 2 => '0011011',
		3 => '0100001', 4 => '0011101', 5 => '0111001', 6 => '0000101',
		7 => '0010001', 8 => '0001001', 9 => '0010111' );

	$this->setC = array( 0 => '1110010', 1 => '1100110', 2 => '1101100',
		3 => '1000010', 4 => '1011100', 5 => '1001110', 6 => '1010000',
		7 => '1000100', 8 => '1001000', 9 => '1110100' );

	//$this->file = fopen(/logs/ean13.log', 'a');
	}

	public function __destruct() {
		//fclose($this->file);
	}

	/**
	 * Write line of log
	 * @param string $string Line writing
	*/
	public function writeLog($string) {
		$this->log .= $string;
	}

	/**
	 * get log information
	 * @return string Log
	 */
	public function getLog() {
		return $this->log;
	}

	/**
	 * Set generate code for barcode has been generate
	 * @param string $code Number code of barcode
	*/
	public function setGenerateCode($code) {
		$code = $this->checksum( substr($code, 0, 12) );
		$this->originalCode = $code;
		$this->motifCalc($code);
		$this->bin(substr($code, 1));
	}

	/**
	 * Calculate checksum
	 * @param string $code Number code of barcode
	 * @return string Number code with checksum
	 */
	public static function checksum($code) {
		$leftRnd = str_split( substr($code, 0, 7) );
		$rightRnd = str_split( substr($code, 7) );

		/*Generate checksum*/
		$leftSum = ($leftRnd[1]+$leftRnd[3]+$leftRnd[5]+$rightRnd[0]+$rightRnd[2]+$rightRnd[4])*3;
		$rightSum = $leftRnd[0]+$leftRnd[2]+$leftRnd[4]+$leftRnd[6]+$rightRnd[1]+$rightRnd[3];
		$sum = $leftSum+$rightSum;

		$checksum = (ceil($sum/10)*10)-$sum;
		$rightRnd[5] = strval($checksum);
		$gencode = implode($leftRnd);
		$gencode .= implode($rightRnd);

		return $gencode;
	}

	/**
	 * Set country or special code for generate barcode
	 * @param string $code Number code of country or special code
	 */
	public function setCountryCode($code) {
		$this->countryCode = $code;
		$this->fixed .= 'country';
	}

	/**
	 * Set country letters code for generate barcode
	 * @param string $code Two (or Three for Norway country) letters
	 */
	public function setCountry($code) {
		$this->country = $code;

		if ( $this->fixed !== '' ) {
				$this->fixed .= ';country';
		}
		else {
			$this->fixed .= 'country';
		}
	}

	/**
	 * Set enterprise code for generate barcode
	 * @param string $code Normally 4 digits, its depend of your organism of regulation
	 */
	public function setEnterpriseCode($code) {
		$this->enterprise = $code;

		if ( $this->fixed !== '' ) {
			$this->fixed .= ';enterprise';
		}
		else {
			$this->fixed .= 'enterprise';
		}
	}

	/**
	 * Set product reference
	 * @param string $code 5 digits reprend product reference
	 */
	public function setRef($code) {
		$this->ref = $code;
	}

	/**
	 * get binary code of barcode
	 * @return binary code
	 */
	public function getBinCode() {
		return $this->binCode;
	}

	/**
	 * Read INI configuration for country generate code
	*/
	private function readGencodeIni() {
		$ini = parse_ini_file('assets/config/gencode.ini', true);

		if ( array_key_exists("EAN13", $ini) ) {
			$pos = array_search( "EAN13", array_keys($ini) );
			$this->ean13 = array_slice($ini["EAN13"], 0);
		}

		if ( array_key_exists("OTHEREAN13", $ini) ) {
			$pos = array_search( "OTHEREAN13" , array_keys($ini) );
			$this->other_ean13 = array_slice($ini["OTHEREAN13"], 0);
		}
	}

	/**
	 * Read INI general configuration
	*/
	private function readConfigIni() {
		$ini = parse_ini_file('assets/config/config.ini', true);

		if ( array_key_exists("general", $ini) ) {
			$pos = array_search( "general", array_keys($ini) );

			$this->enterprise = $ini["general"]["enteprise"];
			$this->country = $ini["general"]["country"];
			$this->fixed = $ini["general"]["fixed"];
			$this->countryCode = $ini["general"]["countryCode"];
		}
	}

	/**
	 * Cut and parse string for generate code
	 * @param string $ctry Country number code
	 * @return array String exploding with explode PHP function and parsing
	*/
	private function explodingString($ctry) {
		global $count, $gen, $leftRnd;

		$exploding = explode(";", $ctry);
		$count = count($exploding);

		/*If minimum two informations*/
		if ($count > 1) {
			for ($i=0; $i<$count ; $i++) {
				$pos = strpos($exploding[$i], ":");
				$gen[$i]["start"] = substr($exploding[$i], 0, $pos);

				if ( intval( substr($exploding[$i], $pos) ) < 1 ) {
					$gen[$i]["end"] = substr($exploding[$i], $pos+1);
				}
				else {
					$gen[$i]["end"] = substr($exploding[$i], $pos);
				}

				if ($gen[$i]["start"] === '' and $gen[$i]["end"] !== '') {
					$gen[$i]["start"] = $gen[$i]["end"];
				}
			}

			$select = mt_rand( 0, count($gen)-1 );
			$g1 = strval($gen[$select]["start"]);
			$g2 = strval($gen[$select]["end"]);

			$rand = mt_rand( $g1, $g2 );

				if ( $rand >= 10 and $rand < 100 ) {
					$strNum = '0'.intval($rand);
				}
				else if ( $rand < 10 ) {
					$strNum = '00'.intval($rand);
				}
				else {
					$strNum = intval($rand);
				}
		}
		else {
			$pos = strpos($exploding[0], ":");
			if ($pos > 0) {
				$gen[0]["start"] = substr($exploding[0], 0, $pos);
				$gen[0]["end"] = substr($exploding[0], $pos+1);

				$select = mt_rand( intval($gen[0]["start"]),
				intval($gen[0]["end"]) );

				$strNum = strval($select);
				$len = strlen($strNum);
				if ( $len < 3 ) {
					switch ($len) {
						case 1:
							$strNum = '00'.$strNum;
							break;

						case 2:
							$strNum = '0'.$strNum;
							break;
					}
				}
			}
			else {
				$strNum = strval($exploding[0]);
			}
		}
				$leftRnd[0] = substr( $strNum, 0, 1);
				$leftRnd[1] = substr( $strNum, 1, 1);
				$leftRnd[2] = substr( $strNum, 2, 1);
		return $gen;
	}

	/**
	 * Generate number code for EAN-13 standard
	 * @param string $country International country code
	 * @param string $code Type for number code generate (country, distributors...)
	 * @return string Number code generate
	*/
	private function ean13($country, $code, $ref) {
		global $count, $gen, $leftRnd;

		$count = 0;
		$countryViaConf = false;
		$enterpriseViaConf = false;

		$this->readGencodeIni();

		if ( $this->fixed === '' ) {
			$this->readConfigIni();
		}

		if ($this->fixed !== '') {
			$explodingConf = explode(";", $this->fixed);

			foreach ($explodingConf as $key => $value) {
				if ( strtolower($value) === "country" ) {
					$country = $this->country;
					$code = "country";
					$countryViaConf = true;
				}
				elseif ( strtolower($value) === "enterprise" ) {
					$enterpriseViaConf = true;
				}
			}
		}

		if ( $countryViaConf === true ) {
			if ( $this->countryCode !== '' )
			{
				$strNum = strval($this->countryCode);
				$leftRnd[0] = substr( $strNum, 0, 1);
				$leftRnd[1] = substr( $strNum, 1, 1);
				$leftRnd[2] = substr( $strNum, 2, 1);
			}
			else {

				if ( array_key_exists(strtoupper($country), $this->ean13) ) {
					$ctry = $this->ean13[strtoupper($country)];
				}

				$gen = $this->explodingString($ctry);
			}
		}
		else {
			/*Generation for country selection*/
			/*if ( !empty($country) && $code == 'country' ) {
				if ( array_key_exists(strtoupper($country), $this->ean13) ) {
					$ctry = $this->ean13[strtoupper($country)];
					$gen = $this->explodingString($ctry);
				}
			}
			else {
				if ($code == 'distrib' or $code == 'weight' or
					$code == 'interne' or $code == 'coupons' or
					$code == 'issn' or $code == 'isbn') {
					if ( !empty($this->other_ean13) ) {
						if ( array_key_exists($code, $this->other_ean13) ) {

							$gen = $this->explodingString($this->other_ean13[$code]);
						}
					}
				}
			}*/

			$this->countryGen($country, $code);

			/*Generate left section of gencode*/
			if ( !isset($leftRnd[2]) ) {
				$leftRnd[2] = mt_rand(0, 9);
			}
		}

		if ( $enterpriseViaConf === true ) {
			$strNum2 = strval($this->enterprise);
			$leftRnd[3] = substr( $strNum2, 0, 1);
			$leftRnd[4] = substr( $strNum2, 1, 1);
			$leftRnd[5] = substr( $strNum2, 2, 1);
			$leftRnd[6] = substr( $strNum2, 3, 1);
		}
		else {
			$leftRnd[3] = mt_rand(0, 9);
			$leftRnd[4] = mt_rand(0, 9);
			$leftRnd[5] = mt_rand(0, 9);
			$leftRnd[6] = mt_rand(0, 9);
		}

		/*Generate right section of gencode*/
		switch ( strlen($ref) ) {
			case 0:
				$rightRnd[0] = mt_rand(0, 9);
				$rightRnd[1] = mt_rand(0, 9);
				$rightRnd[2] = mt_rand(0, 9);
				$rightRnd[3] = mt_rand(0, 9);
				$rightRnd[4] = mt_rand(0, 9);
				break;

			case 1:
				$rightRnd[0] = 0;
				$rightRnd[1] = 0;
				$rightRnd[2] = 0;
				$rightRnd[3] = 0;
				$rightRnd[4] = $ref;
				break;

			case 2:
				$rightRnd[0] = 0;
				$rightRnd[1] = 0;
				$rightRnd[2] = 0;
				$rightRnd[3] = substr( $ref, 0, 1);
				$rightRnd[4] = substr( $ref, 1, 1);
				break;

			case 3:
				$rightRnd[0] = 0;
				$rightRnd[1] = 0;
				$rightRnd[2] = substr( $ref, 0, 1);
				$rightRnd[3] = substr( $ref, 1, 1);
				$rightRnd[4] = substr( $ref, 2, 1);
				break;

			case 4:
				$rightRnd[0] = 0;
				$rightRnd[1] = substr( $ref, 0, 1);
				$rightRnd[2] = substr( $ref, 1, 1);
				$rightRnd[3] = substr( $ref, 2, 1);
				$rightRnd[4] = substr( $ref, 3, 1);
				break;

			case 5:
				$rightRnd[0] = substr( $ref, 0, 1);
				$rightRnd[1] = substr( $ref, 1, 1);
				$rightRnd[2] = substr( $ref, 2, 1);
				$rightRnd[3] = substr( $ref, 3, 1);
				$rightRnd[4] = substr( $ref, 4, 1);
				break;

			default:
				$rightRnd[0] = mt_rand(0, 9);
				$rightRnd[1] = mt_rand(0, 9);
				$rightRnd[2] = mt_rand(0, 9);
				$rightRnd[3] = mt_rand(0, 9);
				$rightRnd[4] = mt_rand(0, 9);
				break;
		}

		$codeForChecksum = '';
		if ($this->originalCode === '') {
			foreach ($leftRnd as $key => $value) {
				$codeForChecksum .= $value;
			}

			foreach ($rightRnd as $key => $value) {
				$codeForChecksum .= $value;
			}
		}
		else {
			$codeForChecksum = $this->originalCode;
		}

		$gencode = $this->checksum($codeForChecksum);

		return $gencode;
	}

	/**
	 * Explode country code for prepare at generate code
	 * @param string $country international code for designate country (cf. gencode.ini)
	 * @param string $code Type of code (country, distrib, weight, interne, coupons, issn or isbn)
	 */
	public function countryGen($country, $code) {
		/*Generation for country selection*/
		if ( !empty($country) && $code == 'country' ) {
			if ( array_key_exists(strtoupper($country), $this->ean13) ) {
				$ctry = $this->ean13[strtoupper($country)];
				$gen = $this->explodingString($ctry);
			}
		}
		else {
			if ($code == 'distrib' or $code == 'weight' or
					$code == 'interne' or $code == 'coupons' or
					$code == 'issn' or $code == 'isbn') {
				if ( !empty($this->other_ean13) ) {
					if ( array_key_exists($code, $this->other_ean13) ) {

						$gen = $this->explodingString($this->other_ean13[$code]);
					}
				}
			}
		}
	}

	/**
	 * Generate bincode
	 * @param string $code Number code generated by normalization function
	*/
	public function bin($code) {
		if ($this->binCode === '') {
			$binCode = '';
			/*Generate left bincode*/
			for ($i=0; $i<6 ; $i++) {
				if( substr($this->motif, $i, 1) === 'A' ) {
					$binCode .= $this->setA[substr($code, $i, 1)];
				}
				else if ( substr($this->motif, $i, 1) === 'B' ) {
					$binCode .= $this->setB[substr($code, $i, 1)];
				}
			}

			/*Generate right bincode*/
			for ($i=6; $i<12 ; $i++) {
				$binCode .= $this->setC[substr($code, $i, 1)];
			}
		}

		$this->writeLog( sprintf( __("Bincode generate: %s <br />", 'wps_barcode'),
				'<b>'.$binCode.'</b>') );

		$this->binCode = $binCode;
	}

	/**
	 * Calcul motif for generate barcode
	 * @param string $code Number code generate
	*/
	private function motifCalc($code) {
		$start = substr($code, 0, 1);
		switch ($start) {
			case '0':
				$this->motif = 'AAAAAA';
				break;

			case '1':
				$this->motif = 'AABABB';
				break;

			case '2':
				$this->motif = 'AABBAB';
				break;

			case '3':
				$this->motif = 'AABBBA';
				break;

			case '4':
				$this->motif = 'ABAABB';
				break;

			case '5':
				$this->motif = 'ABBAAB';
				break;

			case '6':
				$this->motif = 'ABBBAA';
				break;

			case '7':
				$this->motif = 'ABABAB';
				break;

			case '8':
				$this->motif = 'ABABBA';
				break;

			case '9':
				$this->motif = 'ABBABA';
				break;
		}

		$this->writeLog( sprintf( __("Motif: %s<br />", 'wps_barcode'),
				$this->motif) );
	}
}

?>
