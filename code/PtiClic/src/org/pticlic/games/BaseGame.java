package org.pticlic.games;

import org.pticlic.R;
import org.pticlic.model.Constant;
import org.pticlic.model.Game;
import org.pticlic.model.GamePlayed;
import org.pticlic.model.Network;
import org.pticlic.model.Network.Mode;

import android.app.Activity;
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

public class BaseGame extends Activity implements OnClickListener {
	private int currentWord = 0;
	private int nbWord = 0;
	private Game game;
	private GamePlayed gamePlayed;
	
	/** Called when the activity is first created. */
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.game);
		
		SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(this);
		String serverURL = sp.getString(Constant.SERVER_URL, "http://serveur/pticlic.php");
		
		Network network = new Network(serverURL, Mode.SIMPLE_GAME);
		game = network.getGames(1);
		int nbrel = game.getNbRelation();
		nbWord = game.getNbWord();
		
		gamePlayed = new GamePlayed();
		gamePlayed.setGame(game);
		
		// Boutons des relations
		Button r1 = ((Button)findViewById(R.id.relation1));
		Button r2 = ((Button)findViewById(R.id.relation2));
		Button r3 = ((Button)findViewById(R.id.relation3));
		Button r4 = ((Button)findViewById(R.id.relation4));
		
		// Écoute des clics sur les relations
		if (nbrel > 0) { r1.setOnClickListener(this); } else { r1.setVisibility(View.GONE); }
		if (nbrel > 1) { r2.setOnClickListener(this); } else { r2.setVisibility(View.GONE); }
		if (nbrel > 2) { r3.setOnClickListener(this); } else { r3.setVisibility(View.GONE); }
		if (nbrel > 3) { r4.setOnClickListener(this); } else { r4.setVisibility(View.GONE); }
		
		r1.setText("=");
		r2.setText("Poubelle");
		r3.setText("∈");
		((TextView)findViewById(R.id.mainWord)).setText(Game.getName(game.getCentre()));
	}
	
	@Override
	protected void onStart() {
		super.onStart();
		
		start();
	}
	
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
	
	private void start() {
		((TextView)findViewById(R.id.currentWord)).setText(Game.getName(game.getWordInCloud(currentWord)));
		arrivalView();
	}
	
	private void next() {
		if (currentWord < nbWord) {
			currentWord++;
			start();
		} else {
			//TODO : Ajout l'envoie de GamePlayed a Score
		}
	}
	
	/* (non-Javadoc)
	 * @see android.view.View.OnClickListener#onClick(android.view.View)
	 */
	@Override
	public void onClick(View v) {
		CharSequence currentWord = ((TextView)findViewById(R.id.currentWord)).getText();
		switch (v.getId()) {
		case (R.id.relation1) : gamePlayed.add(1, currentWord); next();	break;
		case (R.id.relation2) : gamePlayed.add(2, currentWord); next(); break;
		case (R.id.relation3) : gamePlayed.add(3, currentWord); next(); break;
		case (R.id.relation4) : gamePlayed.add(4, currentWord); next(); break;
		}
	}
}