<!DOCTYPE html>
<html>
	<head>
		<title>PtiClic pre-alpha 0.2</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="target-densitydpi=device-dpi" />
		<style>.screen { display: none; }</style>
		<link rel="stylesheet" href="ressources/green.css" title="green" />
		<link rel="alternate stylesheet" href="ressources/black.css" title="black" />
		<script src="ressources/jquery-1.5.1.min.js"></script>
		<script src="ressources/jquery-ui-1.8.11.custom.min.js"></script>
		<script src="ressources/jquery.ba-hashchange.min.js"></script>
		<script src="ressources/jquery.JSON.js"></script>
		<script src="ressources/my-extensions.js"></script>
		<script src="ressources/pticlic.js"></script>
	</head>
	<body>
		<div id="nojs">Chargement…</div>
		<div class="screen" id="splash">
			<img src="ressources/img/splash.png" />
		</div>
		<div class="screen" id="game">
			<div id="mc-caption-block"></div>
			<div id="mn-caption-block"></div>
			<div id="mc-caption" class="mc"></div>
			<div id="mn-caption" class="mn"></div>
			<div class="relations"></div>
		</div>
		<div class="screen" id="frontpage">
			<div id="title-block"></div>

			<span id="title">PtiClic</span>
			<div class="frontpage-button game">
				<div><img class="icon" alt="" src="ressources/img/72/default.png" /></div>
				<div class="text">Jouer</div>
			</div>
			<div class="frontpage-button prefs">
				<div><img class="icon" alt="" src="ressources/img/72/default.png" /></div>
				<div class="text">Configuration</div>
			</div>
			<div class="frontpage-button connection">
				<div><img class="icon" alt="" src="ressources/img/72/default.png" /></div>
				<div class="text">Connexion</div>
			</div>
			<div class="frontpage-button info">
				<div><img class="icon" alt="" src="ressources/img/72/default.png" /></div>
				<div class="text">A Propos</div>
			</div>
		</div>
		<div class="screen" id="score">
			<h1>Score total : <span class="scoreTotal"></span></h1>
			<div class="scores"></div>
			<input type="button" value="J'ai vu !" id="jaivu"/>
		</div>
		<div class="screen" id="connection">
			<form id="connect-form" action="#" method="GET">
				<label id="user-label" for="user">Login : </label>
				<input type="text" name="user" id="user" />
				<label id="passwd-label" for="passwd">Mot de passe : </label>
				<input type="password" name="passwd" id="passwd" />
				<input type="submit" name="connect" id="connect" value="Se connecter" />
			</form>
		</div>
		<div class="screen" id="info">
			<div class="container">
				<p>
					PtiClic a été conçu et développé par Mathieu Lafourcade
					(LIRMM - Université Montpellier 2) et Virginie Zampa
					(LIDILEM - Université Stendhal Grenoble 3)
				</p>
				<p>
					La présente version pour SmartPhone sous Android, en cours
					de développement a été conçue et réalisée par des
					étudiants en Master 1 à l'Université Montpellier II :
					Yoann BONAVERO, Bertrand BRUN, John CHARRON et
					Georges DUPÉRON.
				</p>
				<p>
					Cette version du PtiClic est une version Alpha. Elle n'est
					pas exempte de bogues.
				</p>
				<p>
					Si vous souhaitez participer au projet en tant que
					Bêta-testeur, rendez-vous sur le site
					<a href="http://pticlic.fr/">http://pticlic.fr</a>
					pour vous y inscrire.
				</p>
				<p>
					Si vous souhaitez signaler des bogues ou nous faire part
					de vos commentaires, vous pouvez nous contacter par
					courriel à l'adresse suivante : <a href="mailto:pticlic.android.beta@gmail.com">pticlic.android.beta@gmail.com</a>
				</p>
				<p id="info-back-p">
					<input id="info-back" type="button" value="Retour" />
				</p>
			</div>
		</div>
		<div class="screen" id="prefs">
			<form id="prefs-form" action="#" method="GET">
				<fieldset id="theme">
					<legend>Thème</legend>
					<input type="radio" id="theme-green" name="theme" value="green" checked="checked" /><label for="theme-green">Colline verdoyante</label><br/>
					<input type="radio" id="theme-black" name="theme" value="black" /><label for="theme-black">Bas-fond de cachot</label>
				</fieldset>
				<input type="submit" name="prefs-apply" id="prefs-apply" value="Appliquer" />
				<input type="reset" name="prefs-cancel" id="prefs-cancel" value="Annuler" />
			</form>
		</div>
		<div id="templates" style="display: none;">
			<div class="relationBox">
				<div class="relation"><img class="icon" alt="" src="ressources/img/72/default.png" /><span class="text"></span></div>
				<div class="clearboth"></div>
			</div>
			<div class="scoreLine">
				<span class="word"></span> (<span class="score"></span>)
			</div>
		</div>
		<div id="message" style="display: none;">
		</div>
	</body>
</html>
<html>
