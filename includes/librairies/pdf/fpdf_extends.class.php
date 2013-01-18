<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

DEFINE('EUR', chr(128)); // Sigle �
DEFINE('USD', '$'); // Sigle $

// DEFINITION CLASSE export_pdf
// Classe permettant l'export d'une facture au format pdf, h�rite de la classe FPDF
class wpshop_export_pdf extends wpshop_FPDF
{
	// CONSTRUCTEUR
	function eoinvoice_export_pdf()
	{
		// Appel du constructeur parent avant toute red�finition
		parent::wpshop_FPDF();
	}

	// Cr�ation r�cursive de dossiers
	function make_recursiv_dir($path, $rights = 0777) {
		if (!@is_dir($path)) {
			$folder_path = array($path);
		}
		else {
			return;
		}

		while(!@is_dir(dirname(end($folder_path))) && dirname(end($folder_path)) != '/' && dirname(end($folder_path)) != '.' && dirname(end($folder_path)) != '') {
			array_push($folder_path, dirname(end($folder_path)));
		}

		while($parent_folder_path = array_pop($folder_path)) {
			if(!@mkdir($parent_folder_path, $rights)) {
				user_error("Can't create folder \"$parent_folder_path\".\n");
			}
		}
	}
	function invoice_export($order_id) {

		$current_user_id = get_current_user_id();
		$order = get_post_meta($order_id, '_order_postmeta', true);

		if($order['customer_id']==$current_user_id OR is_admin()) {

			if(in_array($order['order_status'], array('completed', 'shipped'))) {

				$invoice_dir = WP_CONTENT_DIR . "/uploads/wpshop_invoices/";
				$filename = $this->get_invoice_filename($order_id);
				$invoice_url = $invoice_dir.$filename;

				// If the invoice has not been already generated
				if(!file_exists($invoice_url)) {

					$invoice_ref = $order['order_invoice_ref'];

					// Currency management
					$currency = $order['order_currency'];
					if($currency == 'EUR')$currency = EUR;
					else $currency = wpshop_tools::wpshop_get_sigle($currency);

					// On d�finit un alias pour le nombre de pages total
					$this->AliasNbPages();
					// On ajoute une page au document
					$this->AddPage();
					// On lui applique une police
					$this->SetFont('Arial','',10);
					// Coordonn�es magasin
					$this->store_head($order_id);
					// Coordonn�es client
					$this->client_head($order_id);
					// Date de facturation et r�f�rence facture
					$refdate = $this->invoice_refdate($order_id, $invoice_ref);
					// Tableau des lignes de facture
					$this->rows($order_id, $currency);
					// Ligne de total
					$this->total($order_id, $currency);

					// On affiche le rib du magasin
					//$this->rib($store_selected);

					// On mentionnes les informations obigatoires en bas de page
					$this->pre_footer($order_id);
					// On cr�e le dossier si celui ci n'existe pas
					$this->make_recursiv_dir($invoice_dir);
					// On enregistre
					$path = $invoice_url;
					$this->Output($path, "F");
					// On force le t�l�chargement de la facture
					$Fichier_a_telecharger = $refdate.".pdf";
					$this->forceDownload($Fichier_a_telecharger, $path, filesize($path));
				}
				else $this->forceDownload($filename, $invoice_url, filesize($invoice_url));
			}
			else echo __('The payment regarding the invoice you requested isn\'t completed','wpshop');
		}
		else echo __('You don\'t have the rights to access this invoice.','wpshop');
	}

	/** Force le t�l�chargement d'un fichier */
	function forceDownload($nom, $path, $poids) {
		/*header('Content-Type: application/pdf');
		header('Content-Length: '. $poids);
		header('Content-disposition: attachment; filename='. $nom);
		header('Pragma: no-cache');
		header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		header('Expires: 0');
		ob_clean();
		flush();*/
		wpshop_tools::wpshop_safe_redirect(str_replace(WP_CONTENT_DIR, WP_CONTENT_URL, $path));
		//readfile($path);
		exit();
	}

	// En-t�te magasin
	function store_head($order_id) {

		$company = get_option('wpshop_company_info', array());
		$emails = get_option('wpshop_emails', array());

		//Positionnement
		$this->SetY(55);$this->SetX(12);
		// Cadre client destinataire
		$this->rect(10, 52, 80, 40);

		$xsize = 80;

		if(!empty($company) && !empty($emails)) {
			// Infos
			$store_name = utf8_decode(utf8_encode($company['company_name']));
			$store_email = !empty($emails['contact_email']) ? $emails['contact_email'] : null;

			// Infos
			$store_street_adress = utf8_decode(utf8_encode($company['company_name']));
			$store_city = utf8_decode(utf8_encode($company['company_city']));
			$store_postcode = utf8_decode(utf8_encode($company['company_postcode']));
			//$store_state = utf8_decode('store state');
			$store_country = utf8_decode(utf8_encode($company['company_country']));

			// Gras pour le titre
			$this->SetFont('','B',10);
			$this->Cell($xsize,5,$store_name,0,1,'L'); $this->SetX(12);
			// Police normale pour le reste
			$this->SetFont('','',9);
			$this->Cell($xsize,4,$store_street_adress,0,1,'L'); $this->SetX(12);
			if (!empty($store_suburb)){$this->Cell(80,4,$store_suburb,0,1,'L');} $this->SetX(12);
			$this->Cell($xsize,4,$store_postcode . ' ' . $store_city,0,1,'L'); $this->SetX(12);
			//if ($store_state != ''){$this->Cell(80,4,$store_state,0,1,'L');} $this->SetX(12);
			$this->Cell($xsize,4,$store_country,0,1,'L'); $this->SetX(12);
			$this->Cell($xsize,4,$store_email,0,1,'L'); $this->SetX(12);
		}
		else {
			$this->Cell($xsize,5,_('Nc','wpshop'),0,1,'L');
		}
	}

	// En-t�te client
	function client_head($order_id) {
		$customer_data = get_post_meta($order_id, '_order_info', true);
		$customer_data = $customer_data['billing'];

		// FPDF ne d�codant pas l'UTF-8, on le fait via PHP
		$customer_firstname = utf8_decode(utf8_encode($customer_data['first_name']));
		$customer_lastname = utf8_decode(utf8_encode($customer_data['last_name']));
		$customer_company = utf8_decode(utf8_encode($customer_data['company']));
		$customer_street_adress = utf8_decode(utf8_encode($customer_data['address']));
		$customer_city = utf8_decode(utf8_encode($customer_data['city']));
		$customer_postcode = utf8_decode(utf8_encode($customer_data['postcode']));
		$customer_state = utf8_decode(utf8_encode($customer_data['state']));
		$customer_country = utf8_decode(utf8_encode($customer_data['country']));

		$customer_tva_intra = utf8_decode(utf8_encode($customer_data['company_tva_intra']));

		$xsize = 80;

		//Positionnement
		$this->SetY(55);
		$this->SetX(102);
		// Cadre client destinataire
		$this->rect(100, 52, 100, 40);
		// Et on �cris
		// On r�gle la police d'�criture
		// gras pour le titre
		$this->SetFont('','B',10);
		$this->Cell($xsize,5,$customer_lastname.' '.$customer_firstname.(!empty($customer_company)?', '.$customer_company:null),0,1,'L'); $this->SetX(102);
		// Police normale pour le reste
		$this->SetFont('','',9);
		$this->Cell($xsize,4,$customer_street_adress,0,1,'L'); $this->SetX(102);
		if (!empty($customer_suburb)){$this->Cell($xsize,4,$customer_suburb,0,1,'L');} $this->SetX(102);
		$this->Cell($xsize,4,$customer_postcode . ' ' . $customer_city,0,1,'L'); $this->SetX(102);
		if ($customer_state != ''){$this->Cell($xsize,4,$customer_state,0,1,'L');} $this->SetX(102);
		$this->Cell($xsize,4,$customer_country . ' ',0,1,'L'); $this->SetX(102);
		if ($customer_tva_intra != ''){$this->Cell($xsize,4,__('TVA Intracommunautaire','wpshop').' : '.$customer_tva_intra,0,1,'L');}
	}

	// R�f�rence et date de facturation
	function invoice_refdate($order_id, $invoice_ref)
	{
		$order = get_post_meta($order_id, '_order_postmeta', true);
		// On r�cup�re la r�f�rence
		//$invoice_ref = 'FA'.date('ym').'-0001';
		// On r�cup�re la date de facturation
		$invoice_add_date = substr($order['order_date'],0,10);
		// On r�cup�re la date d'�ch�ance
		//$invoice_max_date = '';

		// Positionnement
		$this->SetY(25); $this->SetX(135); $this->SetFont('','B',14);
		$this->Cell(50, 5, utf8_decode(__( 'Ref. : ', 'wpshop' )) . $invoice_ref,0,1,'L');
		// Positionnement
		$this->SetX(135); $this->SetFont('','',9);
		$this->Cell(50, 4, utf8_decode(__( 'Billing date : ', 'wpshop' )) . $invoice_add_date,0,1,'L');

		$this->SetX(135); $this->SetFont('','',9);
		$this->Cell(50, 4, utf8_decode(__( 'Payment method : ', 'wpshop' )) . utf8_decode(wpshop_payment::get_payment_method($order_id)), 0, 1, 'L');

		$this->SetX(135); $this->SetFont('','',9);
		$this->Cell(50, 4, utf8_decode(__( 'Transaction id : ', 'wpshop' )) . wpshop_payment::get_payment_transaction_number($order_id), 0, 1, 'L');

		//$this->SetX(135);
		//$this->Cell(50, 4, utf8_decode(__( 'Date d\'�ch�ance : ', 'wpshop' )) . $invoice_max_date,0,1,'L');

		return $invoice_ref.'_'.$invoice_add_date;
	}

	function get_invoice_filename($order_id) {
		$order = get_post_meta($order_id, '_order_postmeta', true);
		$invoice_add_date = substr($order['order_date'],0,10);
		$order_invoice_ref = $order['order_invoice_ref'];
		return $order_invoice_ref.'_'.$invoice_add_date.'.pdf';
	}

	// Affiche le tableau des lignes de la facture
	function rows($order_id, $currency)
	{
		$title_ref = utf8_decode(__( 'Reference', 'wpshop' ));
		$title_name = utf8_decode(__( 'Designation', 'wpshop' ));
		$title_qty = utf8_decode(__( 'Qty', 'wpshop' ));
		$title_baseprice = utf8_decode(__( 'PU HT', 'wpshop' ));
		$title_discount = utf8_decode(__( 'Discount', 'wpshop' ));
		$title_tax = utf8_decode(__( 'TVA (Tax)', 'wpshop' ));
		$title_price = utf8_decode(__( 'Total ET', 'wpshop' ));

		// Titre des colonnes
		$header = array($title_ref,$title_name,$title_qty,$title_baseprice,$title_discount,$title_tax,$title_price);
		// Largeur des colonnes
		$w = array(26,75,10,15,15,30,20);
		// On r�cup�re les id des lignes de cette facture
		$order_data = get_post_meta($order_id, '_order_postmeta', true);
		$order_items = $order_data['order_items'];

		$this->setXY(10,100);
		for($i=0;$i<count($header);$i++) {
			$this->Cell($w[$i],5,$header[$i],1,0,'C');
		}
		$this->Ln();

		// Puis on affiche les lignes
		foreach($order_items as $o) {
			$this->row($o, $w, $currency);
		}
	}

	// Affiche un ligne de la facture
	function row($row, $dim_array, $currency) {

		// S�curit�
		$product_reference = !empty($row['item_ref']) ? $row['item_ref'] : 'Nc';
		$product_name = !empty($row['item_name']) ? $row['item_name'] : 'Nc';
		$qty_invoiced = !empty($row['item_qty']) ? $row['item_qty'] : 'Nc';
		$item_pu_ht = !empty($row['item_pu_ht']) ? $row['item_pu_ht'] : 'Nc';
		$discount_amount = !empty($row['discount_amount']) ? $row['discount_amount'] : 0;
		$item_tva_total_amount = !empty($row['item_tva_total_amount']) ? $row['item_tva_total_amount'] : 0;
		$tax_rate = !empty($row['item_tva_rate']) ? $row['item_tva_rate'] : 19.6;
		$total_ht = !empty($row['item_total_ht']) ? $row['item_total_ht'] : 'Nc';

		// On affiche les valeurs
		$this->Cell($dim_array[0],8,$product_reference,'LRB',0,'C');
		$this->Cell($dim_array[1],8,$product_name,'LRB',0,'L');
		$this->Cell($dim_array[2],8,$qty_invoiced,'LRB',0,'C');
		$this->Cell($dim_array[3],8,number_format($item_pu_ht,2,'.',' ').' '.$currency,'LRB',0,'C');
		$this->Cell($dim_array[4],8,number_format($discount_amount,2,'.',' ').' '.$currency,'LRB',0,'C');
		$this->Cell($dim_array[5],8,number_format($item_tva_total_amount,2,'.',' ').' '.$currency.' (' . round($tax_rate, 2) . '%)','LRB',0,'C');
		$this->Cell($dim_array[6],8,number_format($total_ht,2,'.',' ').' '.$currency,'LRB',0,'C');
		$this->Ln();
	}

	function total($order_id, $currency) {

		/* Donn�es commande */
		$order = get_post_meta($order_id, '_order_postmeta', true);

		// D�calage
		$this->Ln();

		$this->Cell(105,10);
		$this->Cell(50,8,__('Total ET','wpshop'),1);
		$this->Cell(35,8,number_format($order['order_total_ht'],2,'.',' ') . ' ' . $currency,1,0,'C'); $this->Ln();

		foreach($order['order_tva'] as $k => $v) {
			$this->Cell(105,10);
			$this->Cell(50,8,__('Tax','wpshop').' '.$k.'%',1); $this->Cell(35,8,number_format($v,2,'.',' ') . ' ' . $currency,1,0,'C'); $this->Ln();
		}

		$this->Cell(105,10);
		$this->Cell(50,8,__('Shipping','wpshop'),1); $this->Cell(35,8,number_format($order['order_shipping_cost'],2,'.',' ') . ' ' . $currency,1,0,'C'); $this->Ln();

		if(!empty($order['order_grand_total_before_discount']) && $order['order_grand_total_before_discount'] != $order['order_grand_total']){

			$this->Cell(105,10);
			$this->Cell(50,8,utf8_decode(__('Total ATI before discount','wpshop')),1);
			$this->Cell(35,8,number_format($order['order_grand_total_before_discount'],2).' '.$currency,1,0,'C'); $this->Ln();

			$this->Cell(105,10);
			$this->Cell(50,8,__('Discount','wpshop'),1);
			$this->Cell(35,8,'-'.number_format($order['order_discount_amount_total_cart'],2).' '.$currency,1,0,'C'); $this->Ln();
		}

		$this->Cell(105,10);
		$this->Cell(50,8,__('Total ATI','wpshop'),1); $this->SetFont('','B',10); $this->Cell(35,8,number_format($order['order_grand_total'],2,'.',' ') . ' ' . $currency,1,0,'C'); $this->Ln();
	}

	function rib($store_number)
	{
		// On r�cup�re les infos du magasin
		/*$store_bic_array = $this->tools_object->eoinvoice_get_store_bic($store_number);

		// On trie
		$bank_code = $store_bic_array[0];
		$register_code = $store_bic_array[1];
		$account_number = $store_bic_array[2];
		$rib_key = $store_bic_array[3];
		$iban = $store_bic_array[4];
		$bic = $store_bic_array[5];

		// On affiche
		$this->SetFont('','B',10);
		$this->Ln(); $this->Ln();
		$this->Cell(40,8,utf8_decode(__('Indentit� bancaire', 'eoinvoice_trdom')));
		$this->SetFont('','',8); $this->Ln();
		$this->Cell(20,8,__('Code banque', 'eoinvoice_trdom'),'LRT',0,'C');
		$this->Cell(20,8,__('Code guichet', 'eoinvoice_trdom'),'LRT',0,'C');
		$this->Cell(20,8,utf8_decode(__('N� Compte', 'eoinvoice_trdom')),'LRT',0,'C');
		$this->Cell(20,8,utf8_decode(__('Cl� RIB', 'eoinvoice_trdom')),'LRT',0,'C');
		$this->Cell(40,8,__('IBAN', 'eoinvoice_trdom'),'LRT',0,'C');
		$this->Cell(25,8,__('BIC', 'eoinvoice_trdom'),'LRT',0,'C');
		$this->Ln();
		$this->Cell(20,8,$bank_code,1,0,'C');
		$this->Cell(20,8,$register_code,1,0,'C');
		$this->Cell(20,8,$account_number,1,0,'C');
		$this->Cell(20,8,$rib_key,1,0,'C');
		$this->Cell(40,8,$iban,1,0,'C');
		$this->Cell(25,8,$bic,1,0,'C');*/
	}

	function pre_footer($order_id)
	{
		// On r�cup�re les infos du magasin
		$store = get_option('wpshop_company_info', array());
		$store_name = $store['company_name'];
		$society_type = $store['company_legal_statut'];
		$society_capital = $store['company_capital'];
		$siret = $store['company_siret'];
		$tva_intra = $store['company_tva_intra'];
		$currency = wpshop_tools::wpshop_get_currency(true);

		$this->SetFont('','',10);
		$this->SetXY(10,-50);

		if(isset($store['company_member_of_a_approved_management_center']) && $store['company_member_of_a_approved_management_center']) {
			$this->MultiCell(190,4,utf8_decode(__('Member of an approved management center, accepting as such payments by check.', 'wpshop')),0,'L',FALSE);
			$this->Ln();
		}
		$this->MultiCell(190,4,utf8_decode(__('Law 83-629 of 07.12.83, Art. 8: "The administrative authorization does not confer any official character to the company or persons who benefit. It is in no way the responsibility of government."', 'wpshop')),0,'L',FALSE);
		$this->Ln();
		$this->MultiCell(190,4,utf8_decode($store_name.', '.$society_type.__(' capital of ', 'wpshop').$society_capital.' '.$currency.'. SIRET : '.$siret.'. TVA Intracommunautaire : '.$tva_intra),0,'L',FALSE);
	}

	//En-t�te
	function Header()
	{
		$this->SetFont('Arial','B',15);
		//D�calage � droite
		$this->Cell(70);
		//Titre
		$this->Cell(30,10,'FACTURE',0,0,'L');
	}

	//Pied de page
	function Footer()
	{
		//Positionnement � 1,5 cm du bas
		$this->SetY(-15);
		//Police Arial italique 8
		$this->SetFont('Arial','I',8);
		//Num�ro de page
		$this->Cell(0,10,$this->PageNo() . '/{nb}',0,0,'C');
	}
}
?>
