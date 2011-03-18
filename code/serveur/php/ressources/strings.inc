<?php

/* Chaque nom de chaîne est de la forme /^(err|ok|oth)_nompage_nommessage$/
* err : Une erreur s'est produite en lien avec cette page ou suite à une action effectuée sur une autre page.
* of  : Confirmation du bon déroulement d'une opération.
* oth : Permet d'afficher un message autre qu'une erreur ou une confirmation.
*/

$strings['err_contact_open_mailfile'] = "Erreur lors de l'ouverture du fichier d'enregistrement de mails.";
$strings['ok_contact_msg_sent'] = "Votre message à été envoyé, nous vous remercions pour vos commentaires.";
$strings['err_contact_fill_all'] = "Veuillez remplir tout les champs.";

$strings['err_creategame_fill_all'] = "Tous les mots du nuage ne sont pas renseignés.";
$strings['err_creategame_nbwords_value'] = "Veuillez entrer une valeur supérieure à zéro.";
$strings['ok_creategame_game_create'] = "La partie à bien été enregistrée.";

$strings['err_index_invalid_msg'] = "Paramètre de la requête invalide. Merci de nous signaler le problème.";
$strings['err_index_redirect'] = "Erreur de redirection. Merci de nous signaler le problème.";

$strings['ok_login_connect'] = "Vous êtes maintenant connecté(e).";
$strings['ok_login_disconnect'] = "Vous avez été déconnecté(e).";
$strings['err_login_bad_user_pass'] = "Mauvais nom d'utilisateur ou mot de passe";
$strings['err_login_fill_all'] = "Veuillez remplir tous les champs.";
$strings['err_login_dbopen'] = "Erreur lors de l'ouverture de la base de données SQLite3. Merci de nous signaler le problème.";
$strings['oth_login_download_nauth'] = "Afin d'accéder à la page de téléchargement et obtenir l'application vous devez être authentifié.";

$strings['err_signup_dbopen'] = "Erreur lors de l'ouverture de la base de données SQLite3. Merci de nous signaler le problème.";
$strings['err_signup_fill_mail'] = "Veuillez renseigner le champ 'Saisissez votre adresse mail'.";
$strings['err_signup_invalid_mail'] = "Adresse mail invalide. Vous pouvez utiliser des lettres, des chiffres et les caractères spéciaux '-', '+', '_' et '.' .";
$strings['err_signup_existing_mail'] = "L'adresse mail saisie existe déjà ! Veuillez nous contacter si vous avez oublié votre identifiant et/ou votre mot de passe.";
$strings['err_signup_fill_login'] = "Veuillez renseigner le champ 'Choisissez un identifiant'.";
$strings['err_signup_invalid_login'] = "Identifiant invalide. Vous pouvez utiliser des lettres, des chiffres et les caractères spéciaux '-', '_' et '.' .";
$strings['err_signup_existing_login'] = "Identifiant déjà pris ! Veuillez choisir un autre identifiant.";
$strings['err_signup_fill_passwd1'] = "Veuillez renseigner le champ 'Mot de passe'.";
$strings['err_signup_invalid_passwd1'] = "Mot de passe invalide. Votre mot de passe doit comporter au moins 5 caractères.";
$strings['err_signup_fill_passwd2'] = "Veuillez renseigner le champ 'Resaisissez le mot de passe'.";
$strings['err_signup_passwords_dont_match'] = "Les deux mots de passe saisis ne sont pas identiques.";
$strings['ok_signup_registered'] = "L'inscription s'est déroulée avec succès.";
$strings['err_signup_dbinsert'] = "Une erreur interne est survenue lors de l'inscription, merci de nous signaler le problème.";

?>
