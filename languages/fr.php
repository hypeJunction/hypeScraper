<?php

return [
	'scraper:settings:linkify' => "Transformer les liens dans l'affichage d'une zone de texte",
	'scraper:settings:linkify:help' => "Ajoute automatiquement les balises autour des URLs, des noms d'utilisateur, des hashtags et des emails dans la vue d'affichage des zone de texte",

	'scraper:settings:bookmarks' => "Ajouter une prévisualisation aux liens web",
	'scraper:settings:bookmarks:help' => 'Ajoute une prévisualoisation des liens web dans la rivière et la vue en pleine page',

	'admin:upgrades:scraper:move_to_db' => "Mettre à jour les URL extraites",
	'admin:upgrades:scraper:move_to_db:description' => "
		Les informations extraites des URL sont maintenant stockées dans la base de données.
		Ce script de mise à niveau va déplacer les informations des URL vers la base de données, 
		recréer les images de prévisualisation en utilisant une approche plus élaborée, 
		et nettoyer les informations résiduelles du stockage sur disque.
	",
];
