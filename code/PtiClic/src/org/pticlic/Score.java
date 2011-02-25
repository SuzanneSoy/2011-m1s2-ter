package org.pticlic;

import java.text.DecimalFormat;

import org.pticlic.exception.PtiClicException;
import org.pticlic.model.Constant;
import org.pticlic.model.Match;
import org.pticlic.model.Network;
import org.pticlic.model.Network.Mode;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.TextView;

/**
 * @author John CHARRON
 * 
 * Permet l'affichage du score obtenu par le joueur lors de sa partie.
 */
public class Score extends Activity implements OnClickListener{
	
	private Match 			gamePlayed;
	
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.score);
		
		SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(this);
		String serverURL = sp.getString(Constant.SERVER_URL, Constant.SERVER);
		String id = sp.getString(Constant.USER_ID, "joueur");
		String passwd = sp.getString(Constant.USER_PASSWD, "");
		Mode mode = null;
		
		if (getIntent().getExtras() != null) {
			// Pour JC : GamePlayed contient toutes les infos sur la partie jouee
			this.gamePlayed = (Match) getIntent().getExtras().get(Constant.SCORE_GAMEPLAYED);
			mode = (Mode) getIntent().getExtras().get(Constant.SCORE_MODE);
		}

		Network network = new Network(serverURL, mode, id, passwd);
		try {
			// Permet de regler la precision : rajoute/enlever des # pour modifier la precision
			DecimalFormat dfrmtr = new DecimalFormat("#.##");
			Double score = network.sendGame(gamePlayed);
			((TextView)findViewById(R.id.total)).setText(String.valueOf(dfrmtr.format(score)));
			sp.edit().putString(Constant.NEW_BASE_GAME, network.getNewGame()).commit();			
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
		} catch (Exception e) {
			AlertDialog.Builder builder = new AlertDialog.Builder(this);
			builder.setTitle(getString(R.string.app_name))
			.setIcon(android.R.drawable.ic_dialog_alert)
			.setMessage(R.string.server_down)
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
