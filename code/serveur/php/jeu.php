<!DOCTYPE html>
<html>
	<head>
		<title>PtiClic pre-alpha 0.2</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="target-densitydpi=device-dpi" />
		<style>.screen { display: none; }</style>
		<script src="ressources/jquery-1.5.1.min.js"></script>
		<script src="ressources/jquery-ui-1.8.11.custom.min.js"></script>
		<script src="ressources/jquery.ba-hashchange.min.js"></script>
		<script src="ressources/jquery.JSON.js"></script>
		<script src="ressources/my-extensions.js"></script>
		<script src="ressources/pticlic.js"></script>
		<script src="server.php?callback=prefs.loadPrefs&action=7"></script>
		<style>
			body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; }
			.screen { width:100%; height:100%; position:absolute; }
			.highlight { display:none; width:100%; height:100%; border-width:medium; border-style:solid; border-radius:2em; position:absolute; }
			a:hover .highlight { display:block; }
			#frontpage a { color: black; text-decoration: none; display:inline-block; width: 30%; top: 32%; position:absolute; }
			#frontpage .icon-container img { display:block; position:relative; margin: 0 auto; }
			#frontpage .icon-label { width:100%; height:30%; position:relative; }
			a.button {
				color: black; text-decoration: none;
				padding: 0.4em; margin: 0.4em; display: inline-block;
				border: medium solid #4a4; background-color:#f0f8d0; border-radius:0.4em;
			}
			.relationBox { border-width: 3px; border-style: solid; border-radius:1em; padding: 0.5em; width: 95%; margin: 0 auto; }
			.formElement { width:30%; height: 10%; position:absolute; }
			.fitFont, .subFitFont { overflow:auto; }
			#score { text-align:center; }
			.marginBox { width: 90%; height: 90%; top: 5%; left:5%; position:absolute; }
			#message { left:25%; top:5%; width:50%; height:10%; position:absolute; border-radius:0.5em; text-align:center; opacity:0.9; }

			.transition { transition: all 0.7s linear; -moz-transition: all 0.7s linear; -webkit-transition: all 0.7s linear; }
			.transition-bg { transition: background-color 0.7s linear; -moz-transition: background-color 0.7s linear; -webkit-transition: background-color 0.7s linear; }
			
			#splash, #nojs { background-color:black; color: white; }
			/* couleurs green */
			body, .screen { background-color:#ffffe0; color: black; }
			#message { background-color:#f0f8d0; color:black; border:medium solid #4a4; }
			#mc-caption { color:#8b4; }
			#mn-caption-box { background-color:#f0f8d0; }
			.mn-caption { color: #4a4; }
			.borderbar { background-color: #4a4; }
			.relationBox { background-color:#f0f8d0; border-color: #4a4; }
			.highlight { background-color:#f0f8d0; border-color:#4a4; }
			.hot { background-color:yellow; }

			/* couleurs black */
			body.black, .black .screen { background-color:black; color: white; }
			.black #message { background-color:#222; color:white; border:medium solid #ccc; }
			.black #mc-caption { color:white; }
			.black #mn-caption-box { background-color:#222; }
			.black .mn-caption { color: #ccc; }
			.black .borderbar { background-color: #ccc; }
			.black .relationBox { background-color:#222; border-color: #ccc; }
			.black .highlight { background-color:#222; border-color:#ccc; }
			.black .hot { background-color:#aaa; }
		</style>
	</head>
	<body>
		<div id="splash" class="screen">
			<img src="ressources/img/splash.png" class="center" style="width:320px; height: 480px;"/>
		</div>
		<div id="game" class="screen">
			<div style="width: 100%; height:40%; position:absolute;">
				<div style="width: 90%; height:37.5%; top:7.5%; left:5%; position:absolute;" class="fitFont">
					<div id="mc-caption" class="mc center">Mot central</div>
				</div>
				<div class="borderbar" style="height:5%; width:100%; top:52.5%; position:absolute;"></div>
				<div id="mn-caption-box" style="top:57.5%; height:37.5%; width:100%; position:absolute;"></div>
				<div style="width: 90%; height:25%; top:63.75%; left: 5%; position:absolute;" class="fitFont">
					<div class="mn mn-caption center setFont">Mot du nuage</div>
				</div>
				<div class="borderbar" style="height:5%; width:100%; top:95%; position:absolute;"></div>
			</div>
			<div class="relations fitFontGroup" style="height:60%; width:100%; top:40%; position:absolute;">
			</div>
		</div>
		<div id="frontpage" class="screen fitFontGroup">
			<a href="index.php" style="width:7%; height:5%; top:2%; right:2%; position:absolute;" class="fitFont button">
				<span style="width:90%; height:90%; top:5%; left:5%; position:absolute;"><span class="center">Retour au site</span></span>
			</a>
			<div style="width:50%; height:24%; top:4%; left:25%; position:absolute;" class="fitFont">
				<span class="center">PtiClic</span>
			</div>
			<a href="#game" style="right:55%; top:33%;">
				<div class="highlight"></div>
				<div class="icon-container"><img alt="" src="ressources/img/72/default.png" /></div>
				<div class="icon-label subFitFont"><span class="text center">Jouer</span></div>
			</a>
			<a href="#prefs" style="left:55%; top:33%;">
				<div class="highlight"></div>
				<div class="icon-container"><img class="iconFitParent" alt="" src="ressources/img/72/default.png" /></div>
				<div class="icon-label subFitFont"><span class="text center">Configuration</span></div>
			</a>
			<a href="#connection" style="right:55%; top:66%;">
				<div class="highlight"></div>
				<div class="icon-container"><img class="iconFitParent" alt="" src="ressources/img/72/default.png" /></div>
				<div class="icon-label subFitFont"><span class="text center">Connexion</span></div>
			</a>
			<a href="#info" style="left:55%; top:66%;">
				<div class="highlight"></div>
				<div class="icon-container"><img class="iconFitParent" alt="" src="ressources/img/72/default.png" /></div>
				<div class="icon-label subFitFont"><span class="text center">À propos</span></div>
			</a>
		</div>
		<div id="score" class="screen">
			<div class="marginBox fitFont">
				<h1>Score total : <span class="scoreTotal"></span></h1>
				<div class="scores"></div>
				<p style="text-align: center;">
					<a class="button" href="#">J'ai vu !</a>
				</p>
			</div>
		</div>
		<div id="info" class="screen">
			<div class="marginBox fitFont">
				<p>
					PtiClic a été conçu et développé par Mathieu Lafourcade
					(LIRMM - Université Montpellier 2) et Virginie Zampa
					(LIDILEM - Université Stendhal Grenoble 3)
				</p>
				<p>
					La présente version pour SmartPhone sous Android, en cours
					de développement a été conçue et réalisée par des
					étudiants en Master 1 à l'Université Montpellier 2 :
					Yoann BONAVERO, Bertrand BRUN, John CHARRON et
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
				<p style="text-align: center;">
					<a class="button" href="#">Retour</a>
				</p>
			</div>
		</div>
		<div id="connection" class="screen">
			<form action="#" method="GET" style="width:100%; height:100%;" class="fitFontGroup">
				<div class="formElement subFitFont" style="right: 55%; top: 25%; text-align:right;"><label id="user-label" for="user">Login : </label></div>
				<div class="formElement subFitFont" style="left: 55%; top: 25%; text-align:left;"><input type="text" name="user" id="user" class="setFont" /></div>
				<div class="formElement subFitFont" style="right: 55%; top: 50%; text-align:right;"><label id="passwd-label" for="passwd">Mot de passe : </label></div>
				<div class="formElement subFitFont" style="left: 55%; top: 50%; text-align:left;"><input type="password" name="passwd" id="passwd" class="setFont" /></div>
				<div class="formElement subFitFont" style="right: 55%; top: 75%; text-align:right;">
					<input type="button" value="Retour" class="setFont goFrontpage" />
				</div>
				<div class="formElement subFitFont" style="left: 55%; top: 75%; text-align:left;">
					<input type="submit" name="connect" id="connect" value="Se connecter" class="setFont" />
				</div>
			</form>
		</div>
		<div id="prefs" class="screen">
			<form id="prefs-form" action="#" method="GET" class="fitFontGroup">
				<fieldset id="theme" class="subFitFont" style="width:50%; height:25%; left:25%; top:25%; position:absolute;">
					<legend>Thème</legend>
					<input type="radio" id="theme-green" name="theme" value="green" /><label for="theme-green">Colline verdoyante</label><br/>
					<input type="radio" id="theme-black" name="theme" value="black" /><label for="theme-black">Bas-fond de cachot</label>
				</fieldset>
				<div class="formElement subFitFont" style="top:75%; right:55%"><input class="center setFont" type="reset" name="prefs-cancel" id="prefs-cancel" value="Annuler" /></div>
				<div class="formElement subFitFont" style="top:75%; left:55%"><input class="center setFont" type="submit" name="prefs-apply" id="prefs-apply" value="Appliquer" /></div>
			</form>
		</div>
		<div id="templates" style="display: none;">
			<div class="relationBox subFitFont">
				<img class="icon" alt="" src="ressources/img/72/default.png" style="width:72px; height:72px; display:inline-block; vertical-align:middle;" />
				<span class="text" style="vertical-align:middle;"></span>
			</div>
			<div class="scoreLine">
				<span class="word"></span> (<span class="score"></span>)
			</div>
		</div>
		<div id="message" class="fitFont"><span class="text center">PtiClic…</span></div>
	</body>
</html>
