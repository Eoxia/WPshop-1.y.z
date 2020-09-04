﻿=== WPshop - eCommerce ===
Contributors: Eoxia
Tags: boutique, catalog, catalogue, commerce, e-commerce, ecommerce, m-commerce, mcommerce, produits,  shop, shopping cart, wp shop, wordpress ecommerce, wordpress shop, WPshop, wp-shop, french payment gateway, passerelle de paiement française
Donate link: http://www.wpshop.fr/
Requires at least: 4.5
Tested up to: 5.2.3
Requires PHP: 5.6.3
Stable tag: 1.6.3
License: GPLv2 or later

Simple and powerfull eCommerce plugin, with French Payment and Shipping systems : Paybox, Paypal, Atos, Cybermut, SystemPay, Paysite-cash, Colissimo

== Description ==

= WPshop est un plugin ecommerce francophone simple et performant =
Simple, flexible et performant, il transformera votre site WordPress en véritable site e-Commerce. [wpshop.fr](http://www.wpshop.fr/ "extension wordpress e-commerce")

= Nos "french plugins" =
Développé en France, WPshop a développé des [modules de paiements CB](http://www.wpshop.fr/boutique/extensions/paiement/ "mode de paiement pour wpshop") VISA & MasterCard avec les passerelles Paybox, Paypal, Atos, Cybermut, SystemPay, Paysite-cash.
Vous pourrez ainsi fonctionner avec toutes les banques Françaises : CIC, Banque Populaire, Caisse d'épargne, Crédit agricole, Société Générale, BNP Parisbas, La Banque Postale, LCL, Crédit du Nord, HSBC...
Encaissez les règlements par Carte Bancaire avec notre extension [CB gratuite Paysite-cash](http://www.wpshop.fr/ecommerce/wpshop_product/module-paiement-cb-paysite-cash/ "mode de paiement gratuit pour wpshop").
Retrouvez aussi nos [modules de livraison](http://www.wpshop.fr/boutique/extensions/livraison/ "wpshop extension de livraison") dont le [plugin gratuit So Colissimo](http://www.wpshop.fr/ecommerce/livraison/module-so-colissimo-simplicite/ "module gratuit de livraison par colissimo").

Transformez votre site e-commerce en logiciel de caisse ou CRM avec nos [modules de gestion](http://www.wpshop.fr/boutique/extensions/commercial/ "modules commerciaux").

= Nos thèmes "ready for eCommerce" =
WPshop vous propose également des [thèmes wordpress eCommerce](http://www.wpshop.fr/boutique/themes/ "themes pour wpshop")</a> optimisés et web responsives pour tablettes et mobiles.

Consultez notre [documentation en ligne](http://www.wpshop.fr/documentations/presentation-wpshop "documentation wpshop") et nos [guides vidéos ecommerce](http://www.wpshop.fr/videotheque/ "guides vidéos wpshop").
Commandez du temps d'[assistance pour votre site WordPress](http://www.wpshop.fr/boutique/extensions/assistance/ "assistance wordpress et wpshop"), du dépannage ou de la formation.

== Installation ==

L'installation du plugin peut se faire de 2 façons :

* Méthode 1

1. Téléchargez le fichier zip depuis le site de wordpress
2. Envoyez le dossier `wpshop` dans le répertoire `/wp-content/plugins/`
3. Activer le plugin dans le menu `Extensions` de Wordpress

* Méthode 2

1. Rechercher le plugin "WPSHOP" à partir du menu "Extension" de Wordpress
2. Lancer l'installation du plugin


== Frequently Asked Questions ==

Question 1 : Comment ajouter un menu avec mon catalogue dans la partie visible du site ?

Pour le moment vous ne pouvez ajouter le contenu de votre catalogue sous forme de menu qu'à travers un widget. Pour cela rendez-vous dans la partie administration des widgets puis ajoutez le widget correspondant aux catégories de produit à l'endroit désiré. Vous pouvez lui donner un titre, si aucun titre n'est défini alors le titre par défaut sera "Catalogue"

Question 2 : Mes produits et catégories ne sont pas accessible dans la partie visible du site ?

Il faut vérifier que le réglage des permaliens pour votre site est bien réglé sur "/%postname%"

== Screenshots ==

1. Fiche produit simple / Simple product sheet (Theme: Easyshop)
2. Produit vedettes : affichable avec un shortcode (Theme: Easyshop)
3. Compte client : page principale : main page (Theme: Easyshop)
4. Tunnel de vente : étape 1 : step 1  (Theme: Easyshop)
5. Tableau de bord de la boutique
6. Statistiques de la boutique
7. Réglages de la boutique
8. Page d'édition d'un produit
9. Sidebar panier (Theme: Easyshop)
10. Edition d'un attribut


== Changelog ==

= Version 1.6.2 =

* 34727 - Ajout du message pour WPshop 2.0
* 34725 - Compatibilité PHP 7+

= Version 1.6.1 =

Corrections

* 22474 - Correction pilotage HT
* 22475 - Ajout de la gestion des prospects

= Version 1.6.0 =

Améliorations

* 12516 - Prise en compte des produits ayant le statut privé dans les commandes / Take care about private product into admin orders
* 12517 - Export des commandes / Order export
* 12519 - Masquage des clients "doublon" / Hide duplicate customers
* 12525 - Cacher la barre uniquement pour le rôle client lorsque la case est cochée dans les réglages / Hide admin bar only for customer role when checkbox is checked into settings
* 12626 - Possibilité de mettre le clients dans la corbeille / Allows admin to put customers into trash

Corrections

* 12515 - Faille de sécurité sur le lien de paiement direct / Direct payment link security fix
* 12513 - Les pages WPShop sont créées en double / WPShop pages created twice
* 10393 - Modification de l'unité de poids dans les réglages n'est pas répercutée dans le front / Weight unit settings is not taken into website frontend
* 12520 - Association - Changement de contact par défaut / Defaut contact association - update on customers
* 12575 - Visualisation des détails d'une commande pour un client dont la commande a été créée par un administrateur / Displaying order details for a customer whose order was created by an admin
* 12601 - Affichage des comptes clients disponible dans le compte utilisateur / Available customer account display in current connected user account
* 12806 - Impossibilité pour un client de payer un devis réalisé par un administrateur / Customer was not able to pay a quotation made by an administrator
* 12978 - Mise en page facture PDF / PDF invoice layout

= Version 1.5.2 =

Corrections

* 12370 - Problème d'ouverture des détails de la commande dans le compte client / Problem with order details in customer account

= Version 1.5.1 =

Corrections

* 12340 - Suppression du module de barcode du dépot de WordPress / Delete barcode module from WordPress repository
* 12345 - Problème de javascript dans le tunnel de vente sur certains navigateurs / Javascript problem during checkout process on some navigators

= Version 1.5.0 =

Améliorations

* 12190 - Report des informations de la société dans le pied de page des factures / Report company information in the footer of invoices
* 12192 - Mise en place de la notion de dû client / Implementation of customer due amount notion

Corrections

* 12194 - Calcul du montant déjà réglé et a régler sur les commandes avec des "," à la place des points / Calculation of the amount already settled and to settle on the orders with "," instead of points
* 12331 - Correction de l'export des commandes depuis le tableau de bord de la boutique / Fixed orders' export from the shop dashboard

= Version 1.4.5.0 =

Améliorations

* 10796 - Ajout d'un filtre permettant de changer le nom du fichier pdf généré / Added a filter allowing to change generated invoice file
* 10876 - Ajout de l'identifiant du produit dans l'appel de la modal de confirmation d'ajout au panier / Add product identifier when calling add to cart confirmation modal
* 11370 - Ajout de crochet dans le tunnel de vente permettant d'afficher des informations complémentaires au cours du processus / Add hook into order process in order to display custom informations allong the process
* 11382 - Création automatique d'un compte client pour un utilisateur qui a été créé avant l'installation de WPShop / Automatically create a customer account for users having their accounts created before WPShop installation
* 11442 - Ajout d'un do_action dans la colonne "statut" des commandes permettant d'intégrer n'importe qu'elle donnée supplémentaire depuis le thème ou un autre plugin / Add a do_action in "status" order column into back admmin allowing to display any data from theme or another plugin
* 11472 - Ajout des lignes précisant le domaine de traduction et le chemin vers les fichiers de traduction dans le fichier principal / Add lines for plugin internationnalisation with text-domain and path to translation files
* 11476 - Modification de l'affichage du template des avis sur les produits / Change display of product rating into product sheet

Corrections

* 10797 - Alignement de la liste des paiements sur une commande à gauche dans le fichier généré / Align to the left the payment list into generated file
* 10808 - Problème d'enregistrement sur les attributs de type date / Error when saving date attributes
* 11359 - Remise en place du champs permettant de choisir l'ordre d'affichage des produits dans la boutique / Put back the field allowing to choose product order display in shop
* 11371 - Correction ajout adresses dans les commandes / Fix addresses creation into orders
* 11372 - Correction de l'enregistrement des paiements avec le filtre de produit téléchargeable / Fix new payment addition when product is download
* 11373 - Correction du bouton de création client dans les commandes / Fix customer creation button into orders
* 11380 - Prise en compte de la date sélectionnée lors de l'ajout d'un paiement à une commande / Chosen date is now setted for order's payment
* 11419 - Incompatibilité entre les frais de port et les paiements partiels des commandes / Incompatibility between partial payment and Shipping cost

= Version 1.4.4.4 =

Améliorations

* 10764 - Meilleure prise en compte du module externe user switching / Better support for user switching external plugin

Corrections

* 10734 - Requêtes sql effectuées avec le préfixe de base de donnée par défaut sans prendre en compte le préfixe configuré à l'installation
* 10735 - Affichages des messages dans le compte et dans la fiche client suite à la mise en place des contacts

= Version 1.4.4.3 =

Améliorations

* 8058 - Ajouter les attributs dans les revisions produits
* 8057 - Interface produits en masse tri par colonnes
* 8519 - OWL Carousel enqueue only for complete_sheet
* 9497 - Interface en masse des attributs des produits
* 9874 - Masquage de la colonne Livraison dans le listing des commandes si il n'y a pas de livraison sur le site
* 10499 - Gestion des contacts par clients
* 10675 - Révision des produits
* 10676 - Numéro d'erreurs JS
* 10678 - Shortcode 'wps_after_check_order_payment_total_amount'

Corrections

* 9042 - Module statistiques mis à jour pour les sites ayant un grand nombre de commande
* 10394 - Le mail de produit téléchargeable et le lien s'affiche même si le probduit n'est pas téléchargeable
* 10677 - Téléchargement devis
* 10679 - WPS-Form even
* 10682 - Désactivation complet du module barcode
* 10683 - Correctif expédier

= Version 1.4.4.2 =

Corrections

* 8859 - Le bouton "Marquer comme expédié" dans les commandes ne fonctionne plus
* 7973 - Modification de l'affichage de la liste des produits dans les commandes: "Tout" par défaut

= Version 1.4.4.1 =

Corrections

* 8782 - Correctif des prix HT des options

== Upgrade Notice ==

Sauvegardez vos données, testez vos sauvegardes !
Puis mettre à jour

== Contactez l'auteur ==

dev@eoxia.com
