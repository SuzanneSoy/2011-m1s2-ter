package org.pticlic.games;

import org.pticlic.R;
import org.pticlic.Score;
import org.pticlic.exception.PtiClicException;
import org.pticlic.model.Constant;
import org.pticlic.model.DownloadedBaseGame;
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
import android.view.Gravity;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.animation.AlphaAnimation;
import android.view.animation.AnimationSet;
import android.view.animation.TranslateAnimation;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.LinearLayout.LayoutParams;
import android.widget.TextView;

import com.google.gson.Gson;

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
	private int 				currentWord = 0;
	private TextView 			currentWordTextView;
	private TextView			wordRemaining;
	private int 				nbWord = 0;
	private DownloadedBaseGame	game;
	private Match 				match;
	private Network 			network;
	private boolean				help = false;
	private String 				gameJson;

	/** Called when the activity is first created. */
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.basegame);

		// On recupere du PreferenceManager les differentes information dont on a besoin
		SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(this);
		String serverURL = sp.getString(Constant.SERVER_URL, Constant.SERVER); // TODO : Mettre comme valeur par defaut l'adresse reel du serveur
		String id = sp.getString(Constant.USER_ID, "joueur");
		String passwd = sp.getString(Constant.USER_PASSWD, "");
		gameJson = sp.getString(Constant.NEW_BASE_GAME, null); 

		// On initialise la classe permettant la communication avec le serveur.
		network = new Network(serverURL, Mode.SIMPLE_GAME, id, passwd);
	}



	/* (non-Javadoc)
	 * @see android.app.Activity#onStart()
	 */
	@Override
	protected void onStart() {
		super.onStart();
		try {
			Gson gson = new Gson();
			if (gameJson == null) game = (DownloadedBaseGame)network.getGames(1);
			else game = gson.fromJson(gameJson, DownloadedBaseGame.class);
			runMatch();
			start();
		} catch (PtiClicException e) {
			AlertDialog.Builder builder = new AlertDialog.Builder(this);
			builder.setTitle(getString(R.string.app_name))
			.setIcon(android.R.drawable.ic_dialog_alert)
			.setMessage(e.getMessage())
			.setCancelable(false)
			.setNegativeButton("Ok", new DialogInterface.OnClickListener() {
				public void onClick(DialogInterface dialog, int id) {
					dialog.cancel();
					finish();
				}
			});
			AlertDialog alert = builder.create();
			alert.show();
		}

	}

	private void runMatch() {
		nbWord = game.getNbWord();

		wordRemaining = (TextView)findViewById(R.id.wordRemaining);
		wordRemaining.setText((currentWord + 1) + "/" + nbWord);

		// On initialise la partie.
		match = new Match();
		match.setGame(game);

		// Boutons des relations
		ImageView r1 = ((ImageView)findViewById(R.id.relation1));
		ImageView r2 = ((ImageView)findViewById(R.id.relation2));
		ImageView r3 = ((ImageView)findViewById(R.id.relation3));
		ImageView r4 = ((ImageView)findViewById(R.id.relation4));


		// Layout des relations
		TextView rn1 = ((TextView)findViewById(R.id.relation1Name));
		TextView rn2 = ((TextView)findViewById(R.id.relation2Name));
		TextView rn3 = ((TextView)findViewById(R.id.relation3Name));
		TextView rn4 = ((TextView)findViewById(R.id.relation4Name));

		// Bouton d'aide
		ImageView aide = ((ImageView)findViewById(R.id.aideBaseGame));
		aide.setOnClickListener(this);

		Relation r = Relation.getInstance();

		// Écoute des clics sur les relations
		// TODO : A enlever lorsque l'on aura toutes les images des relations.
		try {
			r1.setOnClickListener(this); 
			rn1.setText(r.getRelationName(game.getCat1()));
			r1.setImageResource(r.getRelationImage(game.getCat1()));
		} catch (Exception e) {
			r1.setImageResource(R.drawable.icon);
		}
		// TODO : A enlever lorsque l'on aura toutes les images des relations.
		try {
			r2.setOnClickListener(this); 
			rn2.setText(r.getRelationName(game.getCat2()));
			r2.setImageResource(r.getRelationImage(game.getCat2()));
		} catch (Exception e) {
			r2.setImageResource(R.drawable.icon);
		}
		// TODO : A enlever lorsque l'on aura toutes les images des relations.
		try {
			r3.setOnClickListener(this); 
			rn3.setText(r.getRelationName(game.getCat3()));
			r3.setImageResource(r.getRelationImage(game.getCat3()));
		} catch (Exception e) {
			r3.setImageResource(R.drawable.icon);
		}
		// TODO : A enlever lorsque l'on aura toutes les images des relations.
		try {
			r4.setOnClickListener(this);
			rn4.setText(r.getRelationName(game.getCat4()));
			r4.setImageResource(r.getRelationImage(game.getCat4()));
		} catch (Exception e) {
			r4.setImageResource(R.drawable.icon);
		}

		((TextView)findViewById(R.id.mainWord)).setText(DownloadedBaseGame.getName(game.getCentre()));
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

		TranslateAnimation translate;
		if (isInHelpMode())
			translate = new TranslateAnimation(mainWord.getScrollX() / 2, mainWord.getScrollX() / 2, mainWord.getScrollY() / 2, width / 8);
		else
			translate = new TranslateAnimation(mainWord.getScrollX() / 2, mainWord.getScrollX() / 2, mainWord.getScrollY() / 2, width / 4);
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
		((TextView)findViewById(R.id.currentWord)).setText(DownloadedBaseGame.getName(game.getWordInCloud(currentWord)));
		arrivalView();
	}

	/**
	 * Permet de verifier si la partie est fini auquel cas on lance l'activite Score, sinon on passe au mot suivant.
	 */
	private void next() {
		if (++currentWord < nbWord) {
			wordRemaining.setText((currentWord + 1) + "/" + nbWord);
			leaveView();
			start();
		} else {
			Intent intent = new Intent(this, Score.class);
			intent.putExtra(Constant.SCORE_GAMEPLAYED, match);
			intent.putExtra(Constant.SCORE_MODE, Mode.SIMPLE_GAME);

			startActivityForResult(intent, 0x100);
		}
	}

	/**
	 * Cette methode est appeler lorsque l'utilisateur appuie sur le bouton d'aide.
	 * Elle change la disposition des elements de maniere a afficher la description
	 * de l'icone a cote de l'icone.
	 */
	private void helpMode() {
		if (!isInHelpMode()) {
			help = true;

			LayoutParams layoutParams = new LayoutParams(LayoutParams.FILL_PARENT, LayoutParams.FILL_PARENT, 2);

			// On modifie l'affichage du layout
			LinearLayout menuLayout = ((LinearLayout)findViewById(R.id.menuLayout));
			menuLayout.setOrientation(LinearLayout.VERTICAL);
			menuLayout.setLayoutParams(layoutParams);

			// Puis on modifie l'affichage des relations
			//relation1
			LinearLayout relationLayout = ((LinearLayout)findViewById(R.id.relation1Layout));
			relationLayout.setGravity(Gravity.LEFT);

			TextView relationName = ((TextView)findViewById(R.id.relation1Name));
			relationName.setVisibility(View.VISIBLE);

			//relation2
			relationLayout = ((LinearLayout)findViewById(R.id.relation2Layout));
			relationLayout.setGravity(Gravity.LEFT);

			relationName = ((TextView)findViewById(R.id.relation2Name));
			relationName.setVisibility(View.VISIBLE);

			//relation3
			relationLayout = ((LinearLayout)findViewById(R.id.relation3Layout));
			relationLayout.setGravity(Gravity.LEFT);

			relationName = ((TextView)findViewById(R.id.relation3Name));
			relationName.setVisibility(View.VISIBLE);

			//relation4
			relationLayout = ((LinearLayout)findViewById(R.id.relation4Layout));
			relationLayout.setGravity(Gravity.LEFT);

			relationName = ((TextView)findViewById(R.id.relation4Name));
			relationName.setVisibility(View.VISIBLE);


			// On met le mot courant au bon endroit dans la fenetre
			// On recupere la largueur de l'ecran.
			Display display = getWindowManager().getDefaultDisplay(); 
			int width = display.getWidth();

			//On recupere le centre de mainWord pour l'animation de translation.
			TextView mainWord = (TextView)findViewById(R.id.mainWord);
			currentWordTextView = (TextView)findViewById(R.id.currentWord);

			TranslateAnimation translate = new TranslateAnimation(mainWord.getScrollX() / 2, mainWord.getScrollX() / 2, mainWord.getScrollY() / 2, width / 8);
			translate.setDuration(0);
			translate.setFillAfter(true);

			currentWordTextView.setAnimation(translate);

		} else {
			help = false;

			LayoutParams layoutParams = new LayoutParams(LayoutParams.FILL_PARENT, LayoutParams.FILL_PARENT, 10);

			// On modifie l'affichage du layout
			LinearLayout menuLayout = ((LinearLayout)findViewById(R.id.menuLayout));
			menuLayout.setOrientation(LinearLayout.HORIZONTAL);
			menuLayout.setLayoutParams(layoutParams);

			// Puis on modifie l'affichage des relations
			//relation1
			LinearLayout relationLayout = ((LinearLayout)findViewById(R.id.relation1Layout));
			relationLayout.setGravity(Gravity.CENTER);

			TextView relationName = ((TextView)findViewById(R.id.relation1Name));
			relationName.setVisibility(View.GONE);

			//relation2
			relationLayout = ((LinearLayout)findViewById(R.id.relation2Layout));
			relationLayout.setGravity(Gravity.CENTER);

			relationName = ((TextView)findViewById(R.id.relation2Name));
			relationName.setVisibility(View.GONE);

			//relation3
			relationLayout = ((LinearLayout)findViewById(R.id.relation3Layout));
			relationLayout.setGravity(Gravity.CENTER);

			relationName = ((TextView)findViewById(R.id.relation3Name));
			relationName.setVisibility(View.GONE);

			//relation4
			relationLayout = ((LinearLayout)findViewById(R.id.relation4Layout));
			relationLayout.setGravity(Gravity.CENTER);

			relationName = ((TextView)findViewById(R.id.relation4Name));
			relationName.setVisibility(View.GONE);

			// On met le mot courant au bon endroit dans la fenetre
			// On recupere la largueur de l'ecran.
			Display display = getWindowManager().getDefaultDisplay(); 
			int width = display.getWidth();

			//On recupere le centre de mainWord pour l'animation de translation.
			TextView mainWord = (TextView)findViewById(R.id.mainWord);
			currentWordTextView = (TextView)findViewById(R.id.currentWord);

			TranslateAnimation translate = new TranslateAnimation(mainWord.getScrollX() / 2, mainWord.getScrollX() / 2, mainWord.getScrollY() / 2, width / 4);
			translate.setDuration(0);
			translate.setFillAfter(true);

			currentWordTextView.setAnimation(translate);
		}
	}

	/**
	 * Permet de savoir si l'on se trouve ou non dans le mode d'aide
	 * 
	 * @return <code>true</code> si l'on ce trouve dans le mode d'aide <code>false</code> sinon
	 */
	private boolean isInHelpMode() {
		return help;
	}

	/* (non-Javadoc)
	 * @see android.view.View.OnClickListener#onClick(android.view.View)
	 */
	@Override
	public void onClick(View v) {
		switch (v.getId()) {
		case (R.id.relation1) : match.add(1, currentWord); next();	break;
		case (R.id.relation2) : match.add(2, currentWord); next(); break;
		case (R.id.relation3) : match.add(3, currentWord); next(); break;
		case (R.id.relation4) : match.add(4, currentWord); next(); break;
		case (R.id.aideBaseGame) : helpMode(); break;
		}
	}
}