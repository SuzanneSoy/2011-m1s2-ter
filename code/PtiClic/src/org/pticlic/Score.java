package org.pticlic;

import org.pticlic.model.Constant;
import org.pticlic.model.DownloadedScore;
import org.pticlic.model.GamePlayed;
import org.pticlic.model.Network;
import org.pticlic.model.Network.Mode;

import android.app.Activity;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;

/**
 * @author John CHARRON
 * 
 * Permet l'affichage du score obtenu par le joueur lors de sa partie.
 */
public class Score extends Activity implements OnClickListener{
	
	private GamePlayed 	gamePlayed;
	
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.score);
		
		SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(this);
		String serverURL = sp.getString(Constant.SERVER_URL, "http://dumbs.fr/~bbrun/pticlic.json"); // TODO : Mettre comme valeur par defaut l'adresse reel du serveur
		String id = sp.getString(Constant.USER_ID, "joueur");
		String passwd = sp.getString(Constant.USER_PASSWD, "");
		Mode mode = null;
		
		if (getIntent().getExtras() != null) {
			// Pour JC : GamePlayed contient toutes les infos sur la partie jouee
			this.gamePlayed = (GamePlayed) getIntent().getExtras().get(Constant.SCORE_GAMEPLAYED);
			mode = (Mode) getIntent().getExtras().get(Constant.SCORE_MODE);
		}

		Network network = new Network(serverURL, mode, id, passwd);
		
		// FIXME : Pour l'instant ne marche pas, attend de savoir comment est formater le score que l'on recois.
		//DownloadedScore score = network.sendGame(gamePlayed);
		
		((Button)findViewById(R.id.saw)).setOnClickListener(this);
		
	}
	
	@Override
	public void onBackPressed() {
		super.onBackPressed();
		
		finish();
	}
	
	protected double calculateTotal(){
		throw new UnsupportedOperationException();
		//return this.corrects - this.manquants - this.mauvais;
	}

	@Override
	public void onClick(View v) {
		if (v.getId()==R.id.saw) {
			finish();
		}
		
	}
}
