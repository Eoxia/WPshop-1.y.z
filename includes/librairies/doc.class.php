<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

class wpshop_doc{

	const prefix = 'wpshop';
	const doc_slug = 'wpshop_doc';

	/** Main function, display the page
	* @return void
	*/
	function mydoc() {
		global $wpdb;
		
		/* Traitement de la soumission */
		if(isset($_POST['submit_doc'])) {
			if(!empty($_POST['doc_page_name']) && !empty($_POST['doc_url']) && !empty($_POST['content'])) {
				if(!empty($_POST['doc_id']) && is_numeric($_POST['doc_id']) && $_POST['doc_id']>0) {	
					
					$result = $wpdb->update($wpdb->prefix.self::prefix.'__documentation', array(
						'doc_page_name' => $_POST['doc_page_name'], 
						'doc_html' => $_POST['content'],
						'doc_url' => substr(strstr($_POST['doc_url'], '='),1)
					), array('doc_id' => $_POST['doc_id']));
					
					if($result) {
						$_SESSION[self::doc_slug . '_result'] = array('updated', __('Documentation modifi&eacute;e avec succ&eacute;s', 'wpshop'));
						header('Location: ?page=' . self::doc_slug . ''); exit;
					}
					else $_SESSION[self::doc_slug . '_result'] = array('error', __('Impossible de modifier la documentation', 'wpshop'));
				}
				else {/*
					$queryMessage = 'INSERT INTO '.$wpdb->prefix.self::prefix.'__documentation(doc_page_name, doc_html, doc_url, doc_creation_date)
									VALUES ("' . $_POST['doc_page_name'] . '", "' . $_POST['content'] . '",  "' . substr(strstr($_POST['doc_url'], '='),1) . '", NOW())';					
					$result = $wpdb->query($queryMessage);*/
					
					$result = $wpdb->insert($wpdb->prefix.self::prefix.'__documentation', array(
						'doc_page_name' => $_POST['doc_page_name'], 
						'doc_html' => $_POST['content'],
						'doc_url' => substr(strstr($_POST['doc_url'], '='),1),
						'doc_creation_date' => date('Y-m-d H:i:s')
					));
					
					if($result) {
						$_SESSION[self::doc_slug . '_result'] = array('updated', __('Documentation ajout&eacute;e avec succ&eacute;s', 'wpshop'));
						header('Location: ?page=' . self::doc_slug . ''); exit;
					}
					else $_SESSION[self::doc_slug . '_result'] = array('error', __('Impossible d\'ajouter la documentation', 'wpshop'));
				}
			}
			else $_SESSION[self::doc_slug . '_result'] = array('error', __('Les champs <code>Nom de la page</code>, <code>URL de la page</code> et <code>Texte</code> sont obligatoires', 'wpshop'));
		}
		elseif(isset($_POST['delete_doc'])) {
			if(!empty($_POST['doc_id']) && is_numeric($_POST['doc_id']) && $_POST['doc_id']>0) {
				
				//$query = 'UPDATE '.$wpdb->prefix.self::prefix.'__documentation SET doc_active="deleted" WHERE doc_id='.$_POST['doc_id'];
				//$result = $wpdb->query($query);
				
				$result = $wpdb->update($wpdb->prefix.self::prefix.'__documentation', array(
					'doc_active' => 'deleted'
				), array(
					'doc_id' => $_POST['doc_id']
				));
				
				if($result) {
					$_SESSION[self::doc_slug . '_result'] = array('updated', __('Documentation supprim&eacute;e avec succ&eacute;s', 'wpshop'));
					header('Location: ?page=' . self::doc_slug . ''); exit;
				}
				else $_SESSION[self::doc_slug . '_result'] = array('error', __('Impossible de supprimer cette documentation', 'wpshop'));
			}
			else $_SESSION[self::doc_slug . '_result'] = array('error', __('Impossible de supprimer cette documentation', 'wpshop'));
		}
		
		echo '
<div class="wrap">
	<div id="icon-tools" class="icon32"><br /></div>';
	
	if(!empty($_SESSION[self::doc_slug . '_result'])) {
		echo '<div id="message" class="'.$_SESSION[self::doc_slug . '_result'][0].'">'.$_SESSION[self::doc_slug . '_result'][1].'</div>';
	}
	$_SESSION[self::doc_slug . '_result'] = array();
	
	echo '<form method="post" action="">';
	
		if(empty($_GET['action'])) {
			self::liste_doc();
		}
		elseif($_GET['action']=='edit') {
			self::edit_doc();
		}
		elseif($_GET['action']=='delete') {
			self::delete_doc();
		}
		
		echo '
		</form>
	</div>';
	}
	
	/** Complete list of the documentations
	* @return void
	*/
	function liste_doc() {
		global $wpdb;
		
		$query = 'SELECT * FROM '.$wpdb->prefix.self::prefix.'__documentation WHERE doc_active="active"';
		$data = $wpdb->get_results($query);
		
		echo '
			<h2>'.__('G&eacute;rer la documentation','wpshop').' 
				<a class="add-new-h2" href="?page=' . self::doc_slug . '&amp;action=edit&amp;d=0">'.__('Ajouter','wpshop').'</a>
			</h2>
			<p>'.__('Cette page vous permet d\'associer de la documentation &agrave; certaines pages de vos extensions.','wpshop').'</p>
	
			<table id="tableauUser" class="wp-list-table widefat fixed users">
				<thead>
					<tr>
						<th>'.__('Nom de la page','wpshop').'</th>
						<th>'.__('URL','wpshop').'</th>
						<th>'.__('Texte','wpshop').'</th>
						<th>'.__('Date de cr&eacute;ation','wpshop').'</th>
						<th>'.__('Action', 'wpshop').'</th>
					</tr>
				</thead>
				<tbody>';
			if(!empty($data)):
				foreach($data as $d):
					echo '
					<tr>
						<td>'.$d->doc_page_name.'</td>
						<td>'.$d->doc_url.'<br /><a href="'.site_url().'/wp-admin/admin.php?page='.$d->doc_url.'" target="_blank">'.__('Acc&eacute;der &agrave; ce lien','wpshop').'</a></td>
						<td class="truncatable_text">'.htmlspecialchars($d->doc_html).'</td>
						<td>'.mysql2date('d M Y, H:i:s', $d->doc_creation_date, true).'</td>
						<td>
							<a href="?page=' . self::doc_slug . '&amp;action=edit&amp;d='.$d->doc_id.'" class="edit">'.__('&Eacute;diter','wpshop').'</a>
							<a href="?page=' . self::doc_slug . '&amp;action=delete&amp;d='.$d->doc_id.'" class="delete">'.__('Supprimer','wpshop').'</a>
						</td>
					</tr>';
				endforeach;
			else:
				echo '
					<tr>
						<td colspan="5" style="padding:15px 18px;"><b>'.__('Aucune documentation g&eacute;r&eacute;e','wpshop').'</b></td>
					</tr>';
			endif;
			echo '
		</tbody>
	</table>';
	}

	/** Edit a documentation (page)
	* @return void
	*/
	function edit_doc() {
		global $wpdb;
		
		if(!empty($_GET['d']) && is_numeric($_GET['d']) && $_GET['d']>0) {
			$title = '&Eacute;diter une documentation';
			$submit_value = 'Enregistrer les modifications';
			$desc = 'Cette page vous permet d\'&eacute;diter une documentation.';
			$query = 'SELECT * FROM '.$wpdb->prefix.self::prefix.'__documentation WHERE doc_id='.$_GET['d'];
			$data = $wpdb->get_results($query);
			$doc_page_name = $data[0]->doc_page_name;
			$doc_html = $data[0]->doc_html;
			$doc_url = site_url().'/wp-admin/admin.php?page='.$data[0]->doc_url;
		}
		else{
			$title = 'Ajouter une documentation';
			$submit_value = 'Ajouter la documentation';
			$desc = 'Cette page vous permet d\'ajouter une documentation.';
			$doc_page_name = isset($_POST['doc_page_name'])?$_POST['doc_page_name']:null;
			$doc_html = isset($_POST['content'])?$_POST['content']:null;
			$doc_url = isset($_POST['doc_url'])?$_POST['doc_url']:null;
		}
		
		echo '
			<h2>'.$title.' <a class="add-new-h2" href="?page=' . self::doc_slug . '">'.__('Revenir &agrave; la liste','wpshop').'</a></h2>
			<p>'.$desc.'</p>
			<div class="stuffbox metabox-holder" style="padding-top:0;">
				<h3 style="display:block;height:17px;"><label for="title" style="width:100%;">'.__('Nom de la page document&eacute;e','wpshop').'</label></h3>
				<div class="inside" style="margin-top:12px;">
					<input type="text" value="'.$doc_page_name.'" name="doc_page_name" id="title" style="width:100%;" />
					<p>'.__('Correspond au nom de la page &agrave; documenter','wpshop').'</p>
				</div>
			</div>
			
			<div class="stuffbox metabox-holder" style="padding-top:0;margin-bottom:20px;">
				<h3 style="display:block;height:17px;"><label for="doc_url">'.__('URL de la page','wpshop').'</label></h3>
				<div class="inside" style="margin-top:12px;">
					<input type="text" value="'.$doc_url.'" name="doc_url" id="doc_url" style="width:100%;" />
					<p>
						'.__('Correspond &agrave; l\'URL de la page &agrave; documenter. Exemple&nbsp;: <code>http://www.monsite.com/wp-admin/tools.php?page=' . self::doc_slug . '</code>', 'wpshop');
						if(!empty($doc_url)):
							echo '<div id="mylink"><a href="'.$doc_url.'" target="_blank">'.__('Acc&eacute;der &agrave ce lien','wpshop').'</a></div>';
						else:
							echo '<div id="mylink" class="wpshopHide" target="_blank"><a href="">'.__('Acc&eacute;der &agrave ce lien','wpshop').'</a></div>';
						endif;
					echo '
					</p>
				</div>
			</div>
	
			<div id="poststuff" class="metabox" style="margin-bottom:20px;">';
				the_editor($doc_html);
			echo '</div>
	
			<input type="hidden" name="doc_id" value="'.((!empty($_GET['d']) && is_numeric($_GET['d']) && $_GET['d']>0)?$_GET['d']:0).'" />
			<input class="button-primary" type="submit" value="'.$submit_value.'" name="submit_doc" />';
	}
	
	/** Delete a documentation (page)
	* @return void
	*/
	function delete_doc() {
		global $wpdb;
		
		if(!empty($_GET['d']) && is_numeric($_GET['d']) && $_GET['d']>0) {
			$query = 'SELECT doc_page_name FROM '.$wpdb->prefix.self::prefix.'__documentation WHERE doc_id='.$_GET['d'];
			$data = $wpdb->get_results($query);
			$doc_page_name = $data[0]->doc_page_name;
		}
		
		echo '
			<h2>'.__('Suppression d\'une documentation','wpshop').' 
				<a class="add-new-h2" href="?page=' . self::doc_slug . '">'.__('Revenir &agrave; la liste','wpshop').'</a>
			</h2>
			<p>'.__('Cette page vous permet de supprimer une documentation.','wpshop').'</p>
			<div id="namediv" class="stuffbox metabox-holder" style="padding-top:0;">
				<h3 style="display:block;height:17px;">'.__('Confirmation','wpshop').'</h3>
				<div class="inside" style="margin-top:12px;">
					<p>'.__('Etes-vous certain de vouloir supprimer la documentation de la page','wpshop').' <code>'.$doc_page_name.'</code> ?</p>
				</div>
			</div>
			<input type="hidden" name="doc_id" value="'.((!empty($_GET['d']) && is_numeric($_GET['d']) && $_GET['d']>0)?$_GET['d']:0).'" />
			<input class="button-primary" type="submit" value="'.__('Confirmer la suppression','wpshop').'" name="delete_doc" />
			<a href="?page=' . self::doc_slug . '" class="button-secondary">'.__('Annuler', 'wpshop').'</a>';
	}
	
	/*
	** Retourne les infos sur une documentation
	** @return array
	*/
	function get_doc_pages_name_array() {
		global $wpdb;
		$query = 'SELECT * FROM '.$wpdb->prefix.self::prefix.'__documentation';
		$data = $wpdb->get_results($query);
		$array = array();
		foreach($data as $d)
			$array[]=$d->doc_url;
		return $array;
	}
	
	/*
	** Retourne la liste des pages a documenter
	** @return array
	*/
	function get_doc_info($url) {
		global $wpdb;
		$query = 'SELECT doc_html FROM '.$wpdb->prefix.self::prefix.'__documentation WHERE doc_url="'.$url.'"';
		return $wpdb->get_results($query);
	}

	/* Affichage du menu d'aide */
	function pippin_contextual_help($contextual_help, $screen_id, $screen) {
		$data = self::get_doc_info($_GET['page']);
		$string = '';
		foreach($data as $d) { $string .= $d->doc_html.'<br />'; }
		return nl2br($string).$contextual_help;
	}

	/*
	** Initiation du bloc d'�dition WYSIWYG
	** @return void
	*/
	function init_wysiwyg(){
		wp_enqueue_script('editor');
		add_thickbox();
		wp_enqueue_script('media-upload');
		add_action('admin_print_footer_scripts', 'wp_tiny_mce', 25);
		wp_enqueue_script('quicktags');
	}
}
?>