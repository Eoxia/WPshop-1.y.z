===Wpshop - simple eCommerce===
Contributors: Eoxia
Tags: shop, boutique, produits, e-commerce, commerce, m-commerce, mcommerce, shopping cart, ecommerce, catalog, catalogue, responsive
Donate link: http://www.eoxia.com/wpshop-simple-ecommerce-pour-wordpress/
Requires at least: 3.4.0
Tested up to: 3.5
Stable tag: 1.3.3.5

Wpshop une extension e-commerce 100% libre, gratuite et d&eacute;velopp&eacute;e en France. Inclus un th&egrave;me adapt&eacute; pour les mobiles.

== Description ==
<h3>Wpshop est un plugin ecommerce Made in France, simple et flexible.</h3>
<p>L'utilisation de shortcodes permet une int&eacute;gration en toute simplicit&eacute; des produits ou cat&eacute;gories sur vos pages et articles. Modes de paiement CB inclus : paypal et Cybermut pour le Cr&eacute;dit Mutuel et le CIC. Le th&egrave;me int&eacute;gr&eacute; est con&ccedil;u en responsive web design pour une adaptation sans zoom pour les smartphones et tablettes.</p>

<p style="text-align: center;"><img src="http://www.wpshop.fr/wp-content/themes/WpshopCommunication/images/wpshop_logo.png" alt="extension wordpress ecommerce"></p>
<p><a title="extension wordpress e-commerce" href="http://www.wpshop.fr">Site de l'extension wpshop.fr</a></p>
<p><a title="documentation de l'extension wpshop e-commerce" href="http://www.eoxia.com/wpshop-simple-ecommerce-pour-wordpress/">Documentation de l'extension wpshop</a></p>
Wpshop an ecommerce extension 100% free, open source and developed in France. Included free theme web design responsive.
Wpshop is a simple and free Shopping cart plugin. Simple and easy to use, with short codes, the development of the site is flexible and easy. Responsive design for tablets and smartphones included, its theme suited for mobile e commerce propels your site to mCommerce.

== Installation ==

L'installation du plugin peut se faire de 2 fa&ccedil;ons :

* M&eacute;thode 1

1. T&eacute;l&eacute;chargez le fichier zip depuis le site de wordpress
2. Envoyez le dossier `wpshop` dans le r&eacute;pertoire `/wp-content/plugins/`
3. Activer le plugin dans le menu `Extensions` de Wordpress

* M&eacute;thode 2

1. Rechercher le plugin "WPSHOP" &agrave; partir du menu "Extension" de Wordpress
2. Lancer l'installation du plugin


== Frequently Asked Questions ==

Question 1 : Comment ajouter un menu avec mon catalogue dans la partie visible du site ?

Pour le moment vous ne pouvez ajouter le contenu de votre catalogue sous forme de menu qu'&agrave; travers un widget. Pour cela rendez-vous dans la partie administration des widgets puis ajoutez le widget correspondant aux cat&eacute;gories de produit &agrave; l'endroit d&eacute;sir&eacute;. Vous pouvez lui donner un titre, si aucun titre n'est d&eacute;fini alors le titre par d&eacute;faut sera "Catalogue"

Question 2 : Mes produits et cat&eacute;gories ne sont pas accessible dans la partie visible du site ?

Il faut v&eacute;rifier que le r&eacute;glage des permaliens pour votre site est bien r&eacute;gl&eacute; sur "/%postname%"


== Screenshots ==

1. Interface de gestion des cat&eacute;gories (Aucune cat&eacute;gorie)
2. Interface de gestion des cat&eacute;gories (Avec une cat&eacute;gorie)
3. Interface d'&eacute;dition d'une cat&eacute;gorie
4. Fiche d'une cat&eacute;gorie sans sous-cat&eacute;gorie ni produit
5. Fiche d'une cat&eacute;gorie avec ses sous-&eacute;l&eacute;ments
6. Interface de listing des produits
7. Interface d'&eacute;dition des produits
8. Fiche d'un produit dans la partie publique
9. Liste des attributs
10. Interface d'&eacute;dition d'un attribut
11. Interface de gestion des attributs dans les groupes (drag and drop) . Permet d'organiser l'ordre et les attributs pr&eacute;sents.


== Changelog ==

= Version 1.3.3.5 =

Am&eacute;liorations

* ST359 - R&eacute;cup&eacute;ration de toutes les tailles d'image pour la g&eacute;n&eacute;ration de l'image &agrave; la une dans la fiche produit (Permet de choisir la taille voulue directement en indiquant le bon code dans le template)
* ST361 - Homog&eacute;n&eacute;isation des boutons de s&eacute;lection en masse et d'ajout d'une nouvelle valeur pour les attributs de type liste d&eacute;roulante
* ST362 - Ajout de la possibilit&eacute; d'afficher les diff&eacute;rents de template disponible pour chaque bloc en activant l'option (Dans Options -&gt; Avanc&eacute;es mettre la cl&eacute; "WPSHOP_DISPLAY_AVAILABLE_KEYS_FOR_TEMPLATE" et "true" dans la valeur)
* ST364 - Possibilit&eacute; d'afficher des informations sur les valeurs des attributs utilis&eacute;s pour les produits avec options (En choisissant type "interne a wordpress" lors de la cr&eacute;ation de l'attribut, en cochant la case "Afficher une description pour cet attribut" dans l'onglet option de l'attribut puis en ins&eacute;rant le shortcode [wpshop_product_variation_value_detail] dans la page)
* ST369 - D&eacute;finition des attributs Prix / Stock / Reference du produit comme attribut par d&eacute;faut pour les informations sp&eacute;cifiques des options (Permet d'&eacute;viter &agrave; l'administrateur d'avoir &agrave; le d&eacute;finir et corrige les erreurs lorsqu'aucun attribut n'est s&eacute;lectionn&eacute;)
* ST373 - Prise en compte de la langue courante du plugin pour les valeurs des attributs (Permet l'installation du plugin WMPL pour g&eacute;rer les traductions)
* ST375 - Possibilit&eacute; d'afficher le texte "&agrave; partir de" sur les produits ayant des options (A partir des param&egrave;tres de chaque produit)
* ST376 - Possibilit&eacute; de choisir d'afficher le prix le plus bas des options comme prix de d&eacute;part pour les produits avec options

Corrections

* ST360 - Affectation des valeurs d&eacute;j&agrave; s&eacute;lectionn&eacute;es pour les attributs de type liste d&eacute;roulante a choix multiple ap&egrave;rs ajout d'une nouvelle valeur
* ST363 - Impossibilit&eacute; d'ajouter une valeur &agrave; un attribut de type liste d&eacute;roulante si une erreur survient la premi&egrave;re fois (ex. la valeur existe d&eacute;j&agrave; apr&egrave;s modification: impossible de cr&eacute;er une nouvelle valeur sans fermer la fen&ecirc;tre et r&eacute;ouvrir)
* ST365 - G&eacute;n&eacute;ration d'une option simple pour les produits
* ST366 - Modification de l'&eacute;x&eacute;cution du shortcode de la page checkout pour que le contenu soit plac&eacute; au bon endroit
* ST367 - Affichage des boutons d'actions sur les attributs de type liste dans la fiche d'&eacute;dition d'un produit
* ST368 - Affichage du bouton "Ajouter au panier" dans la fiche produit (Si il existe des d&eacute;clinaisons, un bouton "configurer le produit" &eacute;tait ins&eacute;rer &agrave; la place)
* ST370 - Corrections du calcul du prix en "live" si des options &eacute;taient pr&eacute;sents pour le produit en cours d'&eacute;dition
* ST371 - Affichage du boutton de suppression en masse des options si s&eacute;lection non globale
* ST372 - Correction notices php
* ST374 - Suppression de l'affichage des valeurs non affect&eacute;es au produits dans les listes permettant de g&eacute;n&eacute;rer des options simple et de f&eacute;inir les valeurs par d&eacute;faut dans les param&egrave;tres
* ST377 - Correction de l'affichage du panier quand un produit avec option est ajout&eacute;


= Version 1.3.3.4 =

Am&eacute;liorations

* ST297 - Gestion des d&eacute;clinaisons de produits
* ST332 - Gestion des formulaires (Addresses postales / Compte utilisateur)
* ST346 - Inclusion des attributs dans la recherche globale de wordpress (L'inclusion se fait au travers des options des attributs)

Corrections

* ST356 - Compatibilit&eacute; version 3.5 de wordpress


= Version 1.3.3.3 =

Corrections

* ST357 - Compatibilit&eacute; avec la version 3.5 de wordpress (- Librairie Jquery / - Fonction wpdb-&gt;prepare() demandant obligatoirement 2 param&egrave;tres)
* ST358 - Formulaire de connexion lors du processus de commande (le formulaire ne s'affichait plus)

= Version 1.3.3.2 =

Am&eacute;liorations

* ST349 - Non affichage de l'image a la une du produit dans la gallerie

Corrections

* ST350 - Probl&egrave;mes li&eacute;s &agrave; la version 3.5 de wordpress (- Param&egrave;tre manquant pour la fonction $wpdb-&gt;prepare() qui attend un second param&egrave;tre obligatoire / - Version de jquery et jquery-ui en conflit)
* ST351 - Montant des commandes au retour de paypal provoquait un statut "montant incorrect"


= Version 1.3.3.0 =

Am&eacute;liorations

* ST348 - Taille d'affichage des images dans la fiche produit (Possibilit&eacute; de choisir la taille en fonction des images g&eacute;n&eacute;r&eacute;es par wordpress / Classe css)

Corrections

* ST347 - Paypal (Frais de livraison non prix en compte / Retour du paiement non prix en compte)


= Version 1.3.2.9 =

Corrections

* ST342 - Bugs mineurs (Gestion des templates / Gestion des attributs (&eacute;dition en masse / attribut liste d&eacute;roulante multiple) / Erreurs lanc&eacute;es par exec / Template d'affichage des cat&eacute;gories)
* ST343 - G&eacute;n&eacute;ration des num&eacute;ros de facture (Ajout du bouton de facturation dans les cas ou la facturation n'est pas automatique)
* ST344 - Conflits entre librairies Javascript dans la partie client
* ST345 - Paiement par Paypal (Le prix unitaire du produit n'&eacute;tait pas correctement choisi)


= Version 1.3.2.8 =

Am&eacute;liorations

* ST340 - Possibilit&eacute; de g&eacute;rer l'affichage des informations produits par produit

Corrections

* ST338 - Affichage du mini panier
* ST339 - Lien des images dans les fiches produits
* ST341 - Affichage des pages cat&eacute;gories (- shortcodes non ex&eacute;cut&eacute; dans les pages cat&eacute;gories / - Si on d&eacute;coche les produits dans les pages cat&eacute;gories un message est affich&eacute;)


= Version 1.3.2.7 =

Am&eacute;liorations

* ST315 - Ajout d'un statut annul&eacute; pour les commandes
* ST333 - Ajout de la liste des pays dans le formulaire des coordonn&eacute;es client
* ST334 - Affichage du listing des commandes d'un client dans sa fiche (Menu "Entit&eacute; -&gt; Clients")
* ST335 - Gestion des unit&eacute;s dans les attributs
* ST336 - Gestion de l'unicit&eacute; de la r&eacute;f&eacute;rence d'un produit

Corrections

* ST326 - Affichage du prix dans la fiche du produit pour les installations anciennes du plugin (Probl&egrave;mes dus &agrave; la nouvelle gestion de templates)
* ST327 - Galerie d'images dans la fiche du produit si une seule image &eacute;tait affect&eacute;e
* ST328 - Listing des produits dans les cat&eacute;gories (Un seul produit est pr&eacute;sent)
* ST329 - Boutton d'ajout au panier non affich&eacute; pour les versions install&eacute;es avant la 1.3.2.9
* ST330 - Listing des clients dans les commandes indisponible suivant le statu de la commande


= Version 1.3.2.6 =

Am&eacute;liorations

* ST316 - Simplification de la gestion des templates
* ST320 - Possibilit&eacute; de choisir quel attribut afficher sous forme de colonne dans le listing des produits
* ST321 - Insertion des shortcodes dans les pages/articles int&eacute;gr&eacute; &agrave; l'&eacute;diteur wysiwyg
* ST322 - Modification rapide/en masse des produits
* ST323 - Possibilit&eacute; de ne pas choisir de valeur par d&eacute;faut pour les attributs
* ST324 - Duplication de produit (Ajout d'une image de chargement indiquant que l'op&eacute;ration est en cours, ajout d'un suffixe au produit permettant de le diff&eacute;rencier, changement du statut en brouillon, ajout d'un lien vers le nouveau produit une fois le produit dupliqu&eacute; correctement)

Corrections

* ST312 - Les informations concernant les commandes n'&eacute;taient plus affich&eacute;es dans le listing principal
* ST313 - Suppression des produits li&eacute;s (On ne pouvait pas supprimer le dernier produit li&eacute;)
* ST314 - Lien de visualisation des factures dans la page d'&eacute;dition des commandes du cot&eacute; administrateur
* ST317 - Nettoyage des &eacute;l&eacute;ments cr&eacute;&eacute;s en doublon lors de la mise &agrave; jour en version 1.3.2.4
* ST325 - Erreurs php lors de la cr&eacute;ation d'une nouvelle entit&eacute;


= Version 1.3.2.5 =

Am&eacute;liorations

* ST308 - Possiblit&eacute; de choisir les pages a utiliser pour les diff&eacute;rentes pages de wpshop (panier/boutique/mon compte/...)
* ST311 - Possibilit&eacute; d'activer le addon devis apr&egrave;s avoir r&eacute;cup&eacute;r&eacute; un code d'activation

Corrections

* ST309 - Erreur php si aucune case coch&eacute;e dans les sections support&eacute;es pour les entit&eacute;s 
* ST310 - Duplication automatique des entit&eacute;s 


= Version 1.3.2.4 =

Am&eacute;liorations

* ST303 - R&eacute;organisation des menus (Menu coupons devient un sous-menu de commande / Ajout d'un onglet "Addons" dans les options de la boutique)
* ST304 - Possibilit&eacute; de dupliquer un attribut vers une autre entit&eacute;
* ST307 - Possibilit&eacute; de s&eacute;lectionner les boites a afficher dans chaque entit&eacute;

Corrections

* ST302 - Cr&eacute;ation de deux entit&eacute; produits &agrave; l'installation du plugin
* ST305 - Choix par d&eacute;faut non pris en compte pour les listes d&eacute;roulantes
* ST306 - Le prix des frais de livraisons personnalis&eacute; &eacute;taient limit&eacute;s &agrave; 1 chiffre apr&egrave;s la virgule


= Version 1.3.2.3 =

Am&eacute;liorations

* ST295 - Gestion des "entit&eacute;s" (Il est possible de cr&eacute;er des &eacute;l&eacute;ments poss&eacute;dant une url et un affichage dans la partie front exemple: fabricants/vendeurs/clients/....)
* ST296 - Possibilit&eacute; d'ajouter une valeur &agrave; la liste d'un attribut de type liste d&eacute;roulante utilisant les donn&eacute;es de wordpress depuis l'interface d'&eacute;dition d'un produit
* ST298 - Transf&eacute;rer les &eacute;l&eacute;ments d'un attribut de type liste d&eacute;roulante (Du type personnalis&eacute; vers un type d'&eacute;l&eacute;ment de wordpress et inversement)

Corrections

* ST294 - La r&eacute;f&eacute;rence du produit &eacute;tait recalcul&eacute;e &agrave; chaque enregistrement du produit
* ST300 - Les listes d&eacute;roulantes &agrave; choix multiple &eacute;taient mal affich&eacute;es dans la fiche d'&eacute;dition d'un produit


= Version 1.3.2.2 =

Am&eacute;liorations

* ST291 - Possibilit&eacute; d'ajouter une valeur &agrave; la liste d'un attribut de type liste d&eacute;roulante depuis l'interface d'&eacute;dition d'un produit

Corrections

* ST290 - Gestion des valeurs pour les attributs de type liste d&eacute;roulante
* ST292 - Probl&egrave;me d'affichage des onglets correspondant aux groupes d'attributs dans le frontend
* ST293 - Bugs mineurs


= Version 1.3.2.0 =

Am&eacute;liorations

* ST287 - Possibilit&eacute; de cr&eacute;er un groupe d'attribut en dupliquant le contenu d'un groupe existant
* ST288 - Possibilit&eacute; de choisir si on pilote les prix en TTC ou en HT
* ST289 - Gestion des mails am&eacute;lior&eacute;e (Possibilit&eacute; d'envoyer des emails avec du code html avec les fonctionnalit&eacute;s de wordpress)

Corrections

* ST285 - Le nom d'une nouvelle section d'un groupe d'attribut n'est pas enregistr&eacute;
* ST286 - Listing des commandes dans la fiche client (La liste &eacute;tait faite par rapport au cr&eacute;ateur de la commande et non au client associ&eacute; &agrave; cette commande)


= Version 1.3.1.9 =

Am&eacute;liorations

* ST251 - Ajout &eacute;diteur wysiwyg pour les descriptions cat&eacute;gories (Ajout &eacute;diteur wysiwyg pour les descriptions cat&eacute;gories)
* ST268 - Choix du type de donn&eacute;es pour les attributs de type liste d&eacute;roulantes (Personnalis&eacute; ou interne &agrave; wordpress (utilisateurs/pages/articles/....) / Possibilit&eacute; d'activer l'autocompl&eacute;tion pour les types internes de wordpress)
* ST276 - Rechargement du contenu du mini panier si il est pr&eacute;sent dans la page
* ST279 - Gestion des coupons de r&eacute;duction par pourcentage (Gestion des coupons de r&eacute;duction par pourcentage)
* ST280 - Possibilit&eacute; de g&eacute;rer les stocks ou non
* ST281 - Ergonomie des diff&eacute;rentes interface de gestion dans l'administration

Corrections

* ST160 - Suppression de l'affichage de la vignette du produit dans la galerie photo du produit si il n'y a que cette image
* ST267 - Groupe d'attribut non enregistr&eacute; si plusieurs groupes disponible

= Version 1.3.1.8 =

Corrections

* ST278 - Contenu des posts de wordpress non affich&eacute;s dans les pages archives/cat&eacute;gories


= Version 1.3.1.6 =

Am&eacute;liorations

* ST245 - Ajout d'une aide pour la page de r&eacute;glages boutique (Ajout d'une aide pour la page de r&eacute;glages boutique) 
* ST248 - Gestion des frais de port personnalis&eacute; (Il est maintenant possible de g&eacute;rer ses frais de port de mani&egrave;re pr&eacute;cise en fixant une table de prix par poid et/ou prix total du panier) 
* ST252 - Possibilit&eacute; pour le vendeur de choisir les frais de port personnalis&eacute; pour les commandes 
* ST256 - Gestion des produits t&eacute;l&eacute;chargeables (Attribution d'un fichier attach&eacute; &agrave; un produit que l'on peut t&eacute;l&eacute;charger une fois la commande termin&eacute;e) 
* ST257 - Installation simplifi&eacute;e - catalogue de pr&eacute;sentation/site de vente 
* ST260 - Possibilit&eacute; de vider le panier via un seul bouton (Possibilit&eacute; de vider le panier via un seul bouton) 
* ST261 - Ajout de chams personnalis&eacute;s pour les attributs (Ajout de chams personnalis&eacute;s pour les attributs (notament file_url, manage_stock et allow_presale pour l'attribut is_downloadable_)) 
* ST263 - Ajout d'un tableau de bord de la boutique(Ajout d'un tableau de bord de la boutique) 
* ST264 - Possibilit&eacute; de payer une commande non termin&eacute;e depuis l'interface client 

Corrections

* ST244 - Bugs tri produits 
* ST246 - Duplication de produits (Duplication de produits => la duplication des attributs servant dans le tri ne se faisait pas) 
* ST247 - T&eacute;l&eacute;chargement facture (La facture s'affiche maintenant dans le navigateur et laisse le choix a l'utilisateur de la t&eacute;l&eacute;charger ou non) 
* ST250 - URL cat&eacute;gories et sous cat&eacute;gories (Corrections URL cat&eacute;gories et sous cat&eacute;gories) 
* ST258 - Correction affichage bouton d'achat et prix en fonction du type de magasin (Correction affichage bouton d'achat et prix en fonction du type de magasin) 
* ST259 - Correction bug suppression dernier produit panier (La fonction de suppression du dernier produit du panier a &eacute;t&eacute; corrig&eacute;e) 
* ST262 - Correction bug sur les stocks (Correction du bug sur les stock qui ne r&eacute;duisait pas les stock apr&egrave;s un achat valid&eacute;) 
* ST266 - Corrections de bugs mineurs (Notice php) 


= Version 1.3.1.5 =

Am&eacute;liorations 

* ST241 - Personnalisation de la page de r&eacute;sultats de recherche (Si lors d'une recherche on a des post de type wpshop_product alors afficher ce post sous forme de produit et non pas d'article de wordpress modifiable avce un shortcode) 

Corrections 

* ST243 - chmod upload_dir


= Version 1.3.1.4 =

Am&eacute;liorations

* ST233 - Simplification du menu de l'administration de la boutique 
* ST239 - Ajout du num&eacute;ro d'identification de TVA intracommunautaire (Inscription client et facture) 
* ST240 - Possibilit&eacute; de t&eacute;l&eacute;charger les factures depuis l'admin

Corrections

* ST232 - Attributs non affich&eacute;s d&ucirc; au set d'attribut non enregistr&eacute;
* ST238 - Enregistrement num&eacute;ro de transaction ch&egrave;que (Enregistrement num&eacute;ro de transaction ch&egrave;que)
* ST237 - Non affichage des onglets et box correspondant aux set d'attributs contenant aucun attribut dans l'administration


= Version 1.3.1.3 =

Corrections

* ST230 - Loading lors de l'ajout au panier sur la page d'un produit
* ST231 - Nombre d'&eacute;l&eacute;ments par ligne dans le mode grille non pris en compte


= Version 1.3.1.2 =

Am&eacute;liorations

* ST215 - Ajout de fonctionnalit&eacute; dans les shortcodes (- Voir tous les param&egrave;tres possible et ajouter &agrave; la doc / - V&eacute;rifier le param&egrave;tre "limit" du shortcode de produit / - Possibilit&eacute; de passer des param&egrave;tres au shortcode de cat&eacute;gories qui soient pris en compte dans le shortcode des produits inclus dans ce shortcode de cat&eacute;gorie)
* ST216 - Mise en place du bloc vedette sur les produits (Les attributs sont en place, il faut faire comme "declare_as_new" ajouter une classe sur le li du produit et afficher un texte dans le template de display)
* ST221 - D&eacute;finition d'un type d'attribut multis&eacute;lection (A ajouter pour le param&egrave;tre "frontend_input" de l'attribut / Choix 1: Liste d&eacute;roulante multis&eacute;lection => Done. / Choix 2: Checkbox)
* ST229 - Choix de l'affichage pour un groupe d'attributs (Possibilit&eacute;s de choisir la mani&egrave;re dont doit &ecirc;tre affich&eacute; un groupe d'attributs dans l'admin (box fixe ou box libre et d&eacute;pla&ccedil;able))

Corrections

* ST213 - Erreur lors de l'enregistrement des options d'un attribut de type liste d&eacute;roulante (Warning dans attribute.class.php ligne 354)
* ST214 - Envoi des images des cat&eacute;gories(Probl&egrave;me de droits sur le dossier (&agrave; tester sur un serveur corriger avec exec))
* ST217 - Affichage des attributs de type liste d&eacute;roulante dans le front (On affiche l'identifiant au lieu de la valeur)
* ST218 - Set d'attribut par d&eacute;faut n'est pas attribu&eacute; au produit (Il faut v&eacute;rifier dans la fonction appel&eacute;e par le hook "save_post" si il n'y a qu'un set d'attribut de l'affecter par d&eacute;fault)
* ST222 - Shortcode attributs (lors du tri / changement de page / changement d'ordre de tri l'attribut s&eacute;lectionn&eacute; disparait et le shortcode retourne tous les produits existant)
* ST223 - R&eacute;cup&eacute;ration de l'extrait d'un produit avec the_excerpt et non $post->excerpt (Dans product.class.php &agrave; la ligne $product_excerpt = $product->post_excerpt;)
* ST225 - Connexion par pseudo (Connexion par pseudo bloqu&eacute;e ?? email incorrect)
* ST226 - Acc&egrave;s au r&eacute;capitulatif des commandes autres utilisateurs (L'acc&egrave;s &agrave; toute les commandes est possible en touchant le num&eacute;ro dans l'url...)


= Version 1.3.1.1 = 

Am&eacute;liorations

* ST206 - Gestion shortcode simplifi&eacute;es
* ST207 - Checkbox suivi commande du client (Checkbox permettant d'envoyer un mail au client le notifiant des modifications apport&eacute;es &agrave; la commande + suivi des mails concernant chaque commande)
* ST208 - Mode de paiement cic (Ajout du mode de paiement cic  + ajout infos entreprise (tva, tel etc..))
* ST209 - Ajout de la la pagination pour les produits li&eacute;s et shortcode par attributs

Corrections

* ST210 - Choix de l'affichage grille/liste
* ST211 - &Eacute;dition de commande dans l'admin (le prix et la devise disparaissaient)
* ST212 - Redirection paypal (Lors du passage de commande, la redirection vers paypal ne se faisait plus)


= Version 1.3.1.0 =

Am&eacute;liorations

* ST203 - Ajout de param&egrave;tre sur les shortcodes (Ne pas afficher le sorting, nombre d'&eacute;l&eacute;ments par ligne en mode grille)

Corrections

* ST200 - Liste des valeurs associ&eacute;es a un attribut affich&eacute; plusieurs fois si plusieurs produits affect&eacute;s &agrave; cette valeur
* ST201 - Changement du type de l'attribut Nouveaut&eacute; (text en select)
* ST202 - Affichage des produits en relation avec un autre produit (La fonction ne retournait pas le r&eacute;sultat)


= Version 1.3.0.9 =

* Mise &agrave; jour du readme


= Version 1.3.0.7 =

Am&eacute;liorations

* ST190 - Ajout de possibilit&eacute; de tri par d&eacute;faut (Nom du produit / Date d'ajout / Date de modification / Stock) 

Corrections

* ST191 - Lorsque le tri ne retourne pas de r&eacute;sultat, on affiche la liste normale (L'ajax retournait false et n'affichait plus rien dans la liste des produits) 
* ST192 - Mise &agrave; jour des attributs utilis&eacute;s pour le tri dans les post meta
* ST193 - Reprise des commande exec lors de l'envoi et des copies de fichiers
* ST194 - Onglets des fiches produits qui ne s'affichaient pas


= Version 1.3.0.6 =

Am&eacute;liorations

* ST72 - Gestion des nouveaut&eacute;s / produits en vedettes (Possibilit&eacute; de choisir des dates pour d&eacute;finir l'intervalle pendant lequel le produit est marqu&eacute; comme nouveau ou &agrave; la une) 
* ST170 - Gestion du listing des produits par attributs avec un shortcode ([wpshop_products att_name="CODE_ATTRIBUT" att_value="VALEUR_DE_L_ATTRIBUT"]) 
* ST171 - Simplification de la gestion des templates (Les templates sont inclus directement depuis le dossier du plugin. Pour modifier le comportement de l'affichage dans le front, il faut copier le fichier d&eacute;sir&eacute; dans le dossier du th&egrave;me utilis&eacute; actuellement) 
* ST173 - Mise en place des templates conforme aux normes HTML 
* ST174 - Possiblit&eacute;s d'ajouter une commande depuis l'administration pour un client donn&eacute; (V1 - L'utilisateur et les produits doivent &ecirc;tre d&eacute;j&agrave; existant) 
* ST175 - Factorisation du code pour une meilleure maintenance et une meilleure fiabilit&eacute; 
* ST183 - Possibilit&eacute; de choisir le d&eacute;but de la r&eacute;&eacute;criture d'url pour les produits et cat&eacute;gories dans les options 
* ST184 - Possibilit&eacute; de choisir les fonctionnalit&eacute;s wordpress associ&eacute;es aux post sur les produits de wpshop depuis les options 
* ST185 - Possibilit&eacute; de choisir l'url depuis les options lorsqu'un produit n'est affect&eacute; a aucune cat&eacute;gorie 
* ST189 - Gestion des coupons de r&eacute;ductions sur une commande 

Corrections

* ST172 - Lors de la duplication d'un produit tous les nouveaux produits avaient la m&ecirc;me url 
* ST176 - Configurations des tarifs de livraison g&eacute;n&eacute;raux non pris en compte 


= Version 1.3.0.5 =

Am&eacute;liorations

* Gestion du widget cat&eacute;gories dans le front
* Gestion du template du "mini" panier

Corrections

* Corrections du nombre d'articles affich&eacute;s dans le "mini" panier
* Appel de jquery form pour les formulaires de connexion et de cr&eacute;ation de compte lors de la commande d'un client dans la partie front



= Version 1.3.0.4 =

Am&eacute;liorations

* ST128 - Possibilit&eacute; de dupliquer un produit 
* ST161 - Changement de la page option : gestion &agrave; la wordpress 
* ST162 - Gestion des frais de livraison (v1) (Gestion d'un prix min/max sur les frais de livraison - Possibilit&eacute; de mettre la gratuit&eacute; &agrave; partir d'une certaine somme) 
* ST166 - Panier : ajout d'un bouton de rechargement (Le bouton permet de recalculer l'ensemble du panier avant de le soumettre) 
* ST167 - Connexion et inscription en ajax (La connexion et l'inscription se fait maintenant en ajax.) 

Corrections 

* ST163 - Centrage de l'alert du panier (Correction de la fonction de centrage de l'alerte concernant l'ajoute d'articles au panier => bug sur certain &eacute;crans) 
* ST164 - Correction du wpshop_mini_cart (Affichage du prix total du panier dans le mini cart) 
* ST165 - Corrections javascript diverses et vari&eacute;es (Correction erreur javascript => &eacute;l&eacute;ment non trouvable dans la page qui entrainait le bugguage de tout le javascript) 


= Version 1.3.0.3 =

Am&eacute;liorations

* ST8 - interface de listing produit dans le front (Tri par nom/date/prix/stock/al&eacute;atoire + Pagination + Affichage en grille ou liste)
* ST69 - Gestion de produits li&eacute;s (Possibilit&eacute; pour chaque produit de le lier avec d'autres (du genre "vous aimerez surement :")) 
* ST147 - Gestion des devises par magasin (G&eacute;rable dans les options) 
* ST150 - Envoie des mails au format HTML 

Corrections

* ST146 - Ajouter de exec('chmod -R 755 lecheminachangerlesdroits'); partout ou il y a des cr&eacute;ations de dossiers (Permet de corriger le fait que php ne donne pas les droits correct aux dossiers cr&eacute;&eacute;s) 
* ST148 - Modification de la g&eacute;n&eacute;ration des num&eacute;ros de facture 
* ST149 - Correction variable "WPSHOP_UPLOAD_DIR" avec slash manquant


= Version 1.3.0.2 =

Am&eacute;liorations 

* ST23 - Possibilit&eacute; de choisir les fichiers &agrave; &eacute;craser dans le th&egrave;mes 
* ST83 - Acc&egrave;s au template des photos du produit (La galerie des documents attach&eacute;s &agrave; un produit est maintenant compl&egrave;tement personnalisable depuis les fichiers de template! Attention il faut r&eacute;initialiser les templates pour que cette modification soit prise en compte correctement) 
* ST136 - Ajout du flag "par d&eacute;faut" sur les groupes d'attributs et les sections des groupes d'attributs pour affecter les attributs cr&eacute;&eacute;s automatiquement &agrave; ces groupes 
* ST139 - On ne peut plus supprimer une option d'une liste d&eacute;roulante dans les attributs si celle ci est d&eacute;j&agrave; utilis&eacute;e 
* ST140 - Lors de la modification des valeurs des options de l'attribut Taxe (tx_tva) les prix sont recalcul&eacute;s automatiquement pour tous les produits utilisant la valeur modifi&eacute;es 
* ST141 - Possiblit&eacute; d'ordonner les options des listes d&eacute;roulantes, de choisir une valeur par d&eacute;faut et g&eacute;rer les labels 
* ST142 - Prise en compte de la balise "more" dans la description des produits dans les shortcodes 
* ST143 - Ajout de la r&eacute;cusivit&eacute; sur l'execution des shortcodes (Un shortcode qui est inclus dans la description d'un produit qui lui m&ecirc;me est appel&eacute; par un shortcode sera ex&eacute;cut&eacute;)
 
Corrections 

* ST127 - Fermeture de l'image agrandie (Les images permettant de naviguer et de fermer la fen&ecirc;tre de visualisation des images associ&eacute;es aux produits n'&eacute;tait plus dans le bon dossier) 
* ST133 - Masquage de deux champs de type hidden dans le formulaire de cr&eacute;ation d'un attribut 
* ST134 - Modification d'un attribut (La modification d'un attribut entrainait une modification syst&eacute;matique du groupe d'attribut attribu&eacute; &agrave; cet attribut et provoquait sa disparition de la page du produit dans certains cas)


= Version 1.3.0.1 =

Am&eacute;liorations 

* ST17 - Gestion des prix de base (Prix de vente HT / Taxe / Prix de vente TTC) 
* ST21 - Taxes (La gestion se fait par les attributs) 
* ST49 - Message alerte &agrave; l'installation (- Ne pas mettre le message pour masquer / - Mettre un lien vers la page de configuration) 
* ST58 - Configuration de la boutique (- Information sur la societe / - Mode de paiement disponible / - Emails de la boutique / - Personnalisation des emails / - Utilisation ou non * des permaliens personnalis&eacute;s (si on d&eacute;coche une confirmation est demand&eacute;e) / - Nombre de chiffre minimum composant les num&eacute;ros de facture et de commande) 
* ST65 - Possibilit&eacute; de modifier son mot de passe (client) 
* ST117 - Modification des scripts de mise &agrave; jour de la base de donn&eacute;es (- Une interface est disponible en changeant une variable de configuration dans les fichiers de config) 
* ST118 - V&eacute;rification de certaines valeurs entr&eacute;es avant enregistrement du produit (R&eacute;f&eacute;rence: si vide on remplit avec un sch&eacute;ma d&eacute;finit (variable de configuration) / Prix: Calcul des diff&eacute;rentes valeurs suivant le type de pilotage (variable de configuration)) 
* ST119 - Possibilit&eacute; de choisir liste d&eacute;roulante pour les attributs (Avec gestion de la liste des &eacute;l&eacute;ments) 
* ST121 - Interface de visualisation des emails envoy&eacute;s par la boutique (Avec possiblit&eacute; de les renvoyer) 
* ST122 - Possibilit&eacute; de facturer (Possibilit&eacute; de facturer les commandes) 
* ST123 - Ajout des frais de livraison (Ajout des frais de livraison) 
* ST125 - Suivi des mails (Possibilit&eacute; de g&eacute;rer/renvoyer les emails envoy&eacute; via le plugin au client.) 

Corrections 

* ST64 - Mettre wp_reset_query(); dans le shortcode 
* ST120 - L'affectation des vignettes pour le produit sont de nouveau en place pour la version 3.3.1 de wordpress 
* ST124 - Redirections en javascript (Les redirections sont maintenant effectu&eacute;es avec javascript) 
	
	
= Version 1.3.0.0 =

Am&eacute;liorations 

* Vendre vos produits est maintenant possible (Ajout du bouton ajouter au panier / Gestion du panier d'achat / Gestion des commandes)
* Ajout des prix sur les fiches produit
* Ajout de plusieurs shortcodes (wpshop_cart, wpshop_checkout, wpshop_myaccount) permettant une gestion plus avanc&eacute;e de votre boutique
* Gestion pr&eacute;cise des commandes
* Configuration &agrave; l'installation
* Possibilit&eacute; de choisir le paiement par ch&egrave;que ou par paypal

Corrections 

* Meilleure gestion des produits



= Version 1.2.1.1 =

Am&eacute;liorations 

* Ajout de la box permettant l'insertion d'un shortcode dans les articles 
* Affichage d'un bloc indiquant que le produit est inexistant si insertion d'un shortcode erron&eacute; 

Corrections 

* Le formulaire permettant de modifier les informations concernant les photos envoy&eacute;es ne s'affichait plus (L'encodage du fichier des unit&eacute;s des attributs provoquait une erreur) 
* Unit&eacute; par d&eacute;faut lors de la cr&eacute;ation d'un attribut 
* Insertion d'un espace avant et apr&eacute;s chaque shortcode ins&eacute;r&eacute; depuis la box dans les page et articles 
* Suppression du caract&egrave;re 'underscore' &agrave; la fin d'un attribut lors de la cr&eacute;ation 
* Lors de l'activation du plugin un message d'erreur apparait (Encodage du fichier de la classe des unit&eacute;s des attributs d&eacute;fini en UTF8) 
* Probl&egrave;me de cr&eacute;ation des tables de base du plugin (V&eacute;rification et cr&eacute;ation lors du chargement du plugin) 
* Affichage des messages d&eacute;cal&eacute;s sur certaines pages 
* Inclusion de certains javascript et de certaines fonctions entrant en conflit suivant les version de wordpress (Inf&eacute;rieure &agrave; 3.1 avant la mise &agrave; jour de Jquery dans wordpress)


= Version 1.2 =

Am&eacute;liorations

* Shortcodes pour afficher des cat&eacute;gories et/ou des produits(Cat&eacute;gories / Sous-cat&eacute;gories / Produits / Gestions de param&egrave;tres / Interface de gestion) 
* Ajout de boxs s&eacute;par&eacute;es pour g&eacute;rer les images et documents associ&eacute;s &agrave; un produit 
* Ajout des options permettant de choisir les types d'affichages pour la page cat&eacute;gorie(&eacute;l&eacute;ments &agrave; afficher (informations principales / sous-cat&eacute;gories / produits) - Affichage des produits et sous-cat&eacute;gories en liste ou grille (nombre de produit si mode grille))
* Possibilit&eacute; de choisir d'afficher ou non les produits dans le menu g&eacute;r&eacute; dans le widget 
* Dupliquer les &eacute;l&eacute;ments personnalisable dans le th&egrave;me courant(Template hml / ccs / js / - Option permettant de r&eacute;&eacute;craser)
* Onglets fiche produit(Descriptif / Attributs)
* Affectation d'un groupe d'unit&eacute; aux attributs (Pour ne pas avoir la liste de toute les unit&eacute;s sur tous les attributs) 
* G&eacute;n&eacute;rer un shortcode pour les attributs et les sections de groupes d'attributs (R&eacute;cup&eacute;rable et pla&ccedil;able n'importe o&ugrave;)
* Ajout d'une option sur les attributs permettant de les historiser 
* Gestion des groupes d'attributs si plusieurs groupes existant (Permet de s&eacute;lectionner le groupe d'attribut &agrave; utiliser par produit) 
* Gestion automatique de la mise &agrave; jour de la base de donn&eacute;e (Lors de l'ajout d'un champs ou d'une table lors du lancement la mise &agrave; jour est effectu&eacute;e automatiquement)

Corrections

* Lors de la d&eacute;sactivation et de la r&eacute;activation certaines donn&eacute;es &eacute;taient ins&eacute;r&eacute;es plusieurs fois dans la base 


= Version 1.1 =

Am&eacute;liorations

* Utilisation du syst&egrave;me de gestion interne &agrave; wordpress pour g&eacute;rer les produits et cat&eacute;gories de produits (permet d'avoir les fonctionnalit&eacute;s par d&eacute;faut de wordpress)
* Gestion des groupes d'attributs
* Affichage de la fiche des produits dans la partie publique du site ( Avec affichage d'une galerie d'image, d'une galerie de documents et de la liste des attributs associ&eacute;s au produit)
* Affichage de la fiche d'une cat&eacute;gorie dans la partie publique du site
* Possibilit&eacute; d'ajouter un widget contenant la liste des cat&eacute;gories et produits
* Possibilit&eacute; d'ajouter une photo &agrave; une cat&eacute;gorie


= Version 1.0 =

* Possibilit&eacute; de g&eacute;rer des produits (R&eacute;f&eacute;rence/Nom/Descriptions/Documents/Images/Cat&eacute;gories)
* Possibilit&eacute; de g&eacute;rer les cat&eacute;gories de produits (Nom/Description)
* Possibilit&eacute; de g&eacute;rer des documents (Nom/Description/Par d&eacute;faut/Ne pas afficher dans la gallerie dans le frontend) (Dans les produits)


== Am&eacute;liorations Futures ==

* Ajout des produits dans le panier
* Moyen de paiement
* Facturation
* Expedition


== Upgrade Notice ==

= Version 1.2 =
Improve attributes management functionnalities. Add possibility to add product or categories shortcode where you want

= Version 1.1 =
Improve product and categories management

= Version 1.0 =
Plugin first delivery

== Contactez l'auteur ==

dev@eoxia.com
