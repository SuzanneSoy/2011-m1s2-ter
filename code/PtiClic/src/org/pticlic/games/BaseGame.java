package org.pticlic.games;

import org.pticlic.R;
import org.pticlic.Score;
import org.pticlic.model.Constant;
import org.pticlic.model.DownloadedGame;
import org.pticlic.model.Match;
import org.pticlic.model.Network;
import org.pticlic.model.Network.Mode;
import org.pticlic.model.Relation;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.view.Display;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.animation.AlphaAnimation;
import android.view.animation.Animation;
import android.view.animation.Animation.AnimationListener;
import android.view.animation.AnimationSet;
import android.view.animation.TranslateAnimation;
import android.widget.ImageView;
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

public class BaseGame extends Activity implements OnClickListener, AnimationListener {
	private int 			currentWord = 0;
	private TextView 		currentWordTextView;
	private int 			nbWord = 0;
	private DownloadedGame	game;
	private Match 			match;
	private Network 		network;

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
		match = new Match();
		match.setGame(game);

		// Boutons des relations
		ImageView r1 = ((ImageView)findViewById(R.id.relation1));
		ImageView r2 = ((ImageView)findViewById(R.id.relation2));
		ImageView r3 = ((ImageView)findViewById(R.id.relation3));
		ImageView r4 = ((ImageView)findViewById(R.id.relation4));

		Relation r = Relation.getInstance();

		// TODO : Pour l'instant la poubelle ne fait rien. Il faudra certainement la ranger dans un categorie dans GamePlayed pour calculer le score.
		ImageView trash = ((ImageView)findViewById(R.id.trash));
		trash.setOnClickListener(this);
		trash.setImageResource(android.R.drawable.ic_menu_delete);

		// Écoute des clics sur les relations
		if (nbrel > 0) { r1.setOnClickListener(this); r1.setImageResource(r.getRelationImage(game.getCat1())); } else { r1.setVisibility(View.GONE); }
		if (nbrel > 1) { r2.setOnClickListener(this); r2.setImageResource(r.getRelationImage(game.getCat2()));} else { r2.setVisibility(View.GONE); }
		if (nbrel > 2) { r3.setOnClickListener(this); r3.setImageResource(r.getRelationImage(game.getCat3()));} else { r3.setVisibility(View.GONE); }
		if (nbrel > 3) { r4.setOnClickListener(this); r4.setImageResource(r.getRelationImage(game.getCat4()));} else { r4.setVisibility(View.GONE); }		

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
		currentWordTextView = (TextView)findViewById(R.id.currentWord);

		// On defini un ensemble d'animation
		AnimationSet set = new AnimationSet(true);
		set.setDuration(1000);
		set.setFillAfter(true);
		set.setAnimationListener(this);

		TranslateAnimation translate = new TranslateAnimation(mainWord.getScrollX() / 2, mainWord.getScrollX() / 2, mainWord.getScrollY() / 2, width / 2);
		translate.setDuration(500);
		set.addAnimation(translate);

		AlphaAnimation alpha = new AlphaAnimation(0, 1);
		alpha.setDuration(1000);
		set.addAnimation(alpha);

		// Que l'on rajoute a notre vue.
		currentWordTextView.startAnimation(set);
	}

	/**
	 *  
	 */
	private void leaveView() {
		currentWordTextView.clearAnimation();
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
			leaveView();
			start();
		} else {
			Intent intent = new Intent(this, Score.class);
			intent.putExtra(Constant.SCORE_GAMEPLAYED, match);
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
		case (R.id.relation1) : match.add(1, currentWord); next();	break;
		case (R.id.relation2) : match.add(2, currentWord); next(); break;
		case (R.id.relation3) : match.add(3, currentWord); next(); break;
		case (R.id.relation4) : match.add(4, currentWord); next(); break;
		case (R.id.trash) : match.add(0, currentWord); next(); break;
		}
	}

	@Override
	public void onAnimationEnd(Animation animation) {

	}

	@Override
	public void onAnimationRepeat(Animation animation) {
		// TODO Auto-generated method stub

	}

	@Override
	public void onAnimationStart(Animation animation) {
		// TODO Auto-generated method stub

	}
}