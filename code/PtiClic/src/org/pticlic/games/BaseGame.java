package org.pticlic.games;

import org.pticlic.R;
import org.pticlic.Score;
import org.pticlic.model.Constant;
import org.pticlic.model.DownloadedGame;
import org.pticlic.model.GamePlayed;
import org.pticlic.model.Network;
import org.pticlic.model.Relation;
import org.pticlic.model.Network.Mode;

import com.google.gson.Gson;

import android.app.Activity;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.view.Display;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.animation.AlphaAnimation;
import android.view.animation.AnimationSet;
import android.view.animation.TranslateAnimation;
import android.widget.Button;
import android.widget.TextView;

/**
 * @author Bertrand BRUN et Georges DUPÉRON
 * 
 * Cette classe est le controlleur du premier jeu.
 * 
 * Ce premier jeu appeler "Jeux de Base", permet de creer des relations en selectionnant
 * le type de relation d'un mot du nuage de mot par rapport au mot central.
 * 
 * La vue de ce jeu se presente sous la forme d'un fenetre presentant en haut le mot central,
 * et les mots du nuage descende en partant du mot central vers le centre du mobile.
 * Une fois le mot du nuage afficher, l'utilisateur peut selectionner, parmis les relations
 * proposer celle qui lui semble le mieux approprier.
 *
 */
public class BaseGame extends Activity implements OnClickListener {
	private int 		currentWord = 0;
	private int 		nbWord = 0;
	private DownloadedGame 		game;
	private GamePlayed 	gamePlayed;

	/** Called when the activity is first created. */
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.game);

		// On recupere du PreferenceManager les differentes information dont on a besoin
		SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(this);
		String serverURL = sp.getString(Constant.SERVER_URL, "http://dumbs.fr/~bbrun/pticlic.json"); // TODO : Mettre comme valeur par defaut l'adresse reel du serveur
		String id = sp.getString(Constant.USER_ID, "joueur");
		String passwd = sp.getString(Constant.USER_PASSWD, "");

		// On initialise la classe permettant la communication avec le serveur.
		Network network = new Network(serverURL, Mode.SIMPLE_GAME, id, passwd);
		game = network.getGames(1);
		int nbrel = game.getNbRelation();
		nbWord = game.getNbWord();

		// On initialise la partie.
		gamePlayed = new GamePlayed();
		gamePlayed.setGame(game);

		Relation r = Relation.getInstance();

		// Boutons des relations
		Button r1 = ((Button)findViewById(R.id.relation1));
		Button r2 = ((Button)findViewById(R.id.relation2));
		Button r3 = ((Button)findViewById(R.id.relation3));
		Button r4 = ((Button)findViewById(R.id.relation4));

		// TODO : Pour l'instant la poubelle ne fait rien. Il faudra certainement la ranger dans un categorie dans GamePlayed pour calculer le score.
		Button trash = ((Button)findViewById(R.id.trash));
		trash.setOnClickListener(this);
		trash.setText("poubelle");

		// Écoute des clics sur les relations
		if (nbrel > 0) { r1.setOnClickListener(this); r1.setText(r.getRelationName(game.getCat1())); } else { r1.setVisibility(View.GONE); }
		if (nbrel > 1) { r2.setOnClickListener(this); r2.setText(r.getRelationName(game.getCat2()));} else { r2.setVisibility(View.GONE); }
		if (nbrel > 2) { r3.setOnClickListener(this); r3.setText(r.getRelationName(game.getCat3()));} else { r3.setVisibility(View.GONE); }
		if (nbrel > 3) { r4.setOnClickListener(this); r4.setText(r.getRelationName(game.getCat4()));} else { r4.setVisibility(View.GONE); }


		((TextView)findViewById(R.id.mainWord)).setText(DownloadedGame.getName(game.getCentre()));
	}

	/* (non-Javadoc)
	 * @see android.app.Activity#onStart()
	 */
	@Override
	protected void onStart() {
		super.onStart();

		start();
	}

	/* (non-Javadoc)
	 * @see android.app.Activity#onActivityResult(int, int, android.content.Intent)
	 */
	@Override
	protected void onActivityResult(int requestCode, int resultCode, Intent data) {
		super.onActivityResult(requestCode, resultCode, data);
		finish();
	}

	/**
	 * Cette methode permet au mot courant de partir du mot central vers le centre de l'appareil.
	 */
	private void arrivalView() {
		//On recupere la largueur de l'ecran.
		Display display = getWindowManager().getDefaultDisplay(); 
		int width = display.getWidth();

		//On recupere le centre de mainWord pour l'animation de translation.
		TextView mainWord = (TextView)findViewById(R.id.mainWord);

		// On defini un ensemble d'animation
		AnimationSet set = new AnimationSet(true);
		set.setFillAfter(true);
		set.setDuration(1000);

		TranslateAnimation translate = new TranslateAnimation(mainWord.getScrollX() / 2, mainWord.getScrollX() / 2, mainWord.getScrollY() / 2, width / 2);
		translate.setDuration(1000);
		set.addAnimation(translate);

		AlphaAnimation alpha = new AlphaAnimation(.1f, 1);
		alpha.setDuration(1000);
		set.addAnimation(alpha);

		// Que l'on rajoute a notre vue.
		findViewById(R.id.currentWord).startAnimation(set);
	}

	/**
	 * Cette methode permet de passer au mot courant suivant et de lancer l'animation. 
	 */
	private void start() {
		((TextView)findViewById(R.id.currentWord)).setText(DownloadedGame.getName(game.getWordInCloud(currentWord)));
		arrivalView();
	}

	/**
	 * Permet de verifier si la partie est fini auquel cas on lance l'activite Score, sinon on passe au mot suivant.
	 */
	private void next() {
		if (++currentWord < nbWord) {
			start();
		} else {
			Intent intent = new Intent(this, Score.class);
			intent.putExtra(Constant.SCORE_GAMEPLAYED, gamePlayed);
			intent.putExtra(Constant.SCORE_MODE, Mode.SIMPLE_GAME);

			startActivityForResult(intent, 0x100);
		}
	}

	/* (non-Javadoc)
	 * @see android.view.View.OnClickListener#onClick(android.view.View)
	 */
	@Override
	public void onClick(View v) {
		int currentWord = game.getWordInCloud(this.currentWord).getId();
		switch (v.getId()) {
		case (R.id.relation1) : gamePlayed.add(1, currentWord); next();	break;
		case (R.id.relation2) : gamePlayed.add(2, currentWord); next(); break;
		case (R.id.relation3) : gamePlayed.add(3, currentWord); next(); break;
		case (R.id.relation4) : gamePlayed.add(4, currentWord); next(); break;
		case (R.id.trash) : gamePlayed.add(0, currentWord); next(); break;
		}
	}
}