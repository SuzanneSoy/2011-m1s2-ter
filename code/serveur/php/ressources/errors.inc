<?php

function errRequestIncomplete() {
	throw new Exception("La requête est incomplète", 1);
}

function errUserUnknownOrBadPassword() {
	throw new Exception("Utilisateur non enregistré ou mauvais mot de passe", 2);
}

function errGameNeverPlayed() {
	throw new Exception("Cette partie n'a jamais été jouée.", 3); // TODO : code d'erreur en doublon avec celui ci-dessous.
}

function errGameNotAssociatedWithUser() {
	throw new Exception("Cette partie n'est pas associée à votre nom d'utilisateur.", 4);
}

function errAnswerInvalidForWord($r1, $r2, $r0, $trash) {
	throw new Exception("Réponse ($relanswer) invalide pour le mot $num. Les réponses possibles sont : $r1, $r2, $r0, $trash", 5);
}

function errSetPartiNoRelation($num) {
	throw new Exception("Cette requête \"Set partie\" ne donne pas de réponse (une relation) pour le mot numéro $num de la partie.", 6);
}

function errGetGame() {
	throw new Exception("Erreur lors de la récupération de la partie. Vérifiez qu'il y a au moins une partie.", 7);
}

function errFollowingPointer($word) {
	throw new Exception("Erreur lors du suivi des pointeurs de spécialisation du mot $word.", 8);
}

function errLoopDetected($word) {
	throw new Exception("Boucle rencontrée lors du suivi des pointeurs de spécialisation du mot $word.", 9);
}

function errTooMuchRecursion($word) {
	throw new Exception("Trop de niveaux de récursions lors du suivi des pointeurs de spécialisation du mot $word.", 10);
}

function errUnknownCommand() {
	throw new Exception("Commande inconnue", 11);
}

?>