<?php
	session_start();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic sous Android™ - Version Alpha - Accueil</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="ressources/simple.css" />
		<link rel="stylesheet" href="ressources/backend.css" />
	</head>
	<body>
		<?php include("ressources/menu.inc"); ?>
		<div class="content">
			<?php include("ressources/showmsg.inc"); ?>
			<h2>PtiClic : Affichage de parties</h2>
			<?php
				require_once("ressources/backend.inc");
				require_once("ressources/relations.inc");
				$gameId = randomGame();
				if (isset($_GET['gid'])) $gameId = intval($_GET['gid']);
				if (isset($_POST['gid'])) $gameId = intval($_POST['gid']);
				$game = game2array("foo", $gameId);
			?>
			<h3>Informations internes sur le nuage</h3>
			<p>
				Partie numéro <?php echo $gameId; ?> (pgid=<?php echo $game['pgid']; ?>). Pous pouvez obtenir aléatoirement une <a href="showGame.php">autre partie</a>,
				ou avoir un lien vers <a href="showGame.php?gid=<?php echo $gameId; ?>">celle-ci</a>.
			</p>
			<p>
				Vous avez actuellement un score de <?php echo $currentUserScore = getDB()->querySingle("SELECT score FROM user WHERE login='foo';"); ?> points
				et une réputation de <?php echo computeUserReputation($currentUserScore); ?>, ce qui est assez mauvais.
				Vous n'avez aucun espoir de briller grâce à ce jeu. Retournez donc coder.
			</p>
			<?php
				if (isset($_POST['gid'])) {
					$scores = setGame("foo", $game['pgid'], $gameId, $_POST);
					echo '<a name="results"></a>';
					echo '<p>Voilà une bien belle partie ! Vous avez gagné '.$scores['total'].' points au total. Maintenant, retournez coder.</p>';
				}
			?>
			<ul>
				<li>Poids désigne le poids pour cette relation entre le mot central et le mot en cours (pour cette partie).</li>
				<li>PoidsTotal désigne la somme des poids sur la ligne. C'est un bon indice de la fiabilité des poids pour ce mot : plus PoidsTotal est faible, moins c'est fiable.</li>
				<li>Proba désigne la probabilité que le mot soit associé au mot central avec cette relation.</li>
				<li>Score indique le score que ferait un utilisateur pour ce mot avec cette relation, s'il avait un score de départ de 10, 100, 1000.</li>
			</ul>
			<table class="show-game">
				<thead>
					<tr>
						<th colspan="3" style="color: darkgreen;"><?php echo $game['center']['name'] . " (eid = " . $game['center']['id'] . ")"; ?></th>
						<th rowspan="2">PoidsTotal</th>
						<th colspan="6"><?php echo $stringRelations[$game['cat1']] . " (rid = " . $game['cat1'] . ")"; ?></th>
						<th colspan="6"><?php echo $stringRelations[$game['cat2']] . " (rid = " . $game['cat2'] . ")"; ?></th>
						<th colspan="6"><?php echo $stringRelations[$game['cat3']] . " (rid = " . $game['cat3'] . ")"; ?></th>
						<th colspan="6"><?php echo $stringRelations[$game['cat4']] . " (rid = " . $game['cat4'] . ")"; ?></th>
						<?php
							if (isset($_POST['gid'])) {
								echo '<th rowspan="2">Votre réponse</th>';
								echo '<th rowspan="2">Votre score</th>';
							}
						?>
					</tr>
					<tr>
						<th>Num.</th>
						<th>Mot</th>
						<th>EID</th>
						
						<th>Poids</th>
						<th>Proba</th>
						<th colspan="4">Score 0, 10, 100, 1000</th>
						
						<th>Poids</th>
						<th>Proba</th>
						<th colspan="4">Score 0, 10, 100, 1000</th>
						
						<th>Poids</th>
						<th>Proba</th>
						<th colspan="4">Score 0, 10, 100, 1000</th>
						
						<th>Poids</th>
						<th>Proba</th>
						<th colspan="4">Score 0, 10, 100, 1000</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach ($game['cloud'] as $k => $v) {
					?>
					<tr>
						<td><?php echo $k . "."; ?></td>
						<th><?php echo $v['name']; ?></th>
						<td><?php echo $v['id']; ?></td>
						<td><?php echo $v['totalWeight']; ?></td>
						<?php
							$columns = array(0 => 'probaR1', 1 => 'probaR2', 2 => 'probaR0', 3 => 'probaTrash');
							foreach ($columns as $answer => $probaRX) {
								echo "<td";
								if (isset($_POST['gid']) && $game['cat'.($answer+1)] == $_POST[$k])
									echo ' style="background-color:#ddd;"';
								echo '>' . $v[$probaRX] . "</td>";
								echo '<td style="color:#'
									. str_pad(dechex(max(0,min(255,0xff - 1.3*255*$v['probas'][$answer]))), 2, "0", STR_PAD_LEFT)
									. str_pad(dechex(max(0,min(255,       1.3*255*$v['probas'][$answer]))), 2, "0", STR_PAD_LEFT)
									. '00;">'
									. (round($v['probas'][$answer]*100)/100) . "</td>";
								echo "<td>" . computeScore($v['probas'], $v['difficulty'], $answer, computeUserReputation(0))."</td>";
								echo "<td>" . computeScore($v['probas'], $v['difficulty'], $answer, computeUserReputation(10))."</td>";
								echo "<td>" . computeScore($v['probas'], $v['difficulty'], $answer, computeUserReputation(100))."</td>";
								echo "<td>" . computeScore($v['probas'], $v['difficulty'], $answer, computeUserReputation(1000))."</td>";
							}
						?>
						<?php
							if (isset($_POST['gid'])) {
								echo '<td>'.$stringRelations[$_POST[$k]]." (rid=".$_POST[$k].")".'</td>';
								echo '<td>'.$scores[$k].'</td>';
							}
						?>
					</tr>
					<?php
						}
					?>
				</tbody>
			</table>
			
			<h3>Jouer à la partie</h3>
			<form action="showGame.php#results" method="POST">
				<input type="hidden" name="gid" id="gid" value="<?php echo $gameId; ?>" />
				<p>Mot central : <span style="color: darkgreen;"><?php echo $game['center']['name']; ?></span>.</p>
				<table class="show-game">
					<tbody>
						<?php
							foreach ($game['cloud'] as $k => $v) {
						?>
						<tr>
							<th><?php echo $v['name']; ?></th>
							<?php for ($answer = 0; $answer < 4; $answer++) { ?>
							<td>
								<input type="radio" name="<?php echo $k; ?>" id="<?php echo $k . '-' . $answer; ?>" value="<?php echo $game['cat'.($answer+1)]; ?>" />
								<label for="<?php echo $k . '-' . $answer; ?>"><?php echo $stringRelations[$game['cat'.($answer+1)]]; ?></label>
							</td>
							<?php } ?>
						</tr>
						<?php
							}
						?>
					</tbody>
				</table>
				<input type="submit" value="Gamble !" />
			</form>
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
