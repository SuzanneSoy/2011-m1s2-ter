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
				require_once("pticlic.php");
				require_once("relations.php");
				$game = game2array("foo", (isset($_GET['gid']) ? $_GET['gid'] : randomGame()));
			?>
			<h3><?php echo $game['center']['name'] . " (eid = " . $game['center']['id'] . ")"; ?></h3>
			<p>
				<?php $scoreAvantPartie = 10; ?>Score de l'utilisateur avant la partie : <?php echo $scoreAvantPartie; ?>.
			</p>
			<ul>
				<li>Poids désigne le poids pour cette relation entre le mot central et le mot en cours (pour cette partie).</li>
				<li>PoidsTotal désigne la somme des poids sur la ligne. C'est un bon indice de la fiabilité des poids pour ce mot : plus PoidsTotal est faible, moins c'est fiable.</li>
				<li>Proba désigne la probabilité que le mot soit associé au mot central avec cette relation.</li>
				<li>Score indique le score que ferait un utilisateur pour ce mot avec cette relation, s'il avait un score de départ de 10, 100, 1000.</li>
			</ul>
			<table class="show-game">
				<thead>
					<tr>
						<th colspan="3">Mot</th>
						<th rowspan="2">PoidsTotal</th>
						<th colspan="6"><?php echo $stringRelations[$game['cat1']] . " (rid = " . $game['cat1'] . ")"; ?></th>
						<th colspan="6"><?php echo $stringRelations[$game['cat2']] . " (rid = " . $game['cat2'] . ")"; ?></th>
						<th colspan="6"><?php echo $stringRelations[$game['cat3']] . " (rid = " . $game['cat3'] . ")"; ?></th>
						<th colspan="6"><?php echo $stringRelations[$game['cat4']] . " (rid = " . $game['cat4'] . ")"; ?></th>
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
								echo "<td>" . $v[$probaRX] . "</td>";
								echo '<td style="color:#'
									. str_pad(dechex(max(0,min(255,0xff - 2*255*$v['probas'][$answer]))), 2, "0", STR_PAD_LEFT)
									. str_pad(dechex(max(0,min(255,       2*255*$v['probas'][$answer]))), 2, "0", STR_PAD_LEFT)
									. '00;">'
									. $v['probas'][$answer] . "</td>";
								echo "<td>" . computeScore($v['probas'], $v['difficulty'], $answer, computeUserReputation(0))."</td>";
								echo "<td>" . computeScore($v['probas'], $v['difficulty'], $answer, computeUserReputation(10))."</td>";
								echo "<td>" . computeScore($v['probas'], $v['difficulty'], $answer, computeUserReputation(100))."</td>";
								echo "<td>" . computeScore($v['probas'], $v['difficulty'], $answer, computeUserReputation(1000))."</td>";
							}
						?>
					</tr>
					<?php
						}
					?>
				</tbody>
			</table>
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
