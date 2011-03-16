package org.pticlic;

import org.pticlic.exception.PtiClicException;
import org.pticlic.model.Constant;
import org.pticlic.model.Match;
import org.pticlic.model.Network;
import org.pticlic.model.Network.Mode;
import org.pticlic.model.Network.ScoreResponse;

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
public class BaseScore extends Activity implements OnClickListener{

	private Match           gamePlayed;
	private ScoreResponse   sr = null;
	
	private void networkStuff() {
		SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(this);
		String id = sp.getString(Constant.USER_ID, "joueur");
		String passwd = sp.getString(Constant.USER_PASSWD, "");
		String serverURL = sp.getString(Constant.SERVER_URL, Constant.SERVER);
		Mode mode = null;

		if (getIntent().getExtras() != null) {
			// GamePlayed contient toutes les infos sur la partie jouee
			this.gamePlayed = (Match) getIntent().getExtras().get(Constant.SCORE_GAMEPLAYED);
			mode = (Mode) getIntent().getExtras().get(Constant.SCORE_MODE);
		}

		// TODO : factoriser le serverUrl dans Network
		sp.edit().remove(Constant.NEW_BASE_GAME).commit();
		Network network = new Network(serverURL, mode, id, passwd);
		try {
			sr = network.sendBaseGame(gamePlayed);
			sp.edit().putString(Constant.NEW_BASE_GAME, sr.getNewGame()).commit();
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
	}

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.score);

		this.networkStuff();

		((TextView)findViewById(R.id.total)).setText("42");
		// TODO : Attention, le cast en (BaseGame) n'est pas sûr !
		((TextView)findViewById(R.id.scoreRel1)).setText("Foo1");
		((TextView)findViewById(R.id.scoreRel2)).setText("Foo2");
		((TextView)findViewById(R.id.scoreRel3)).setText("Foo3");
		((TextView)findViewById(R.id.scoreRel4)).setText("Foo4");
		
		((Button)findViewById(R.id.saw)).setOnClickListener(this);

	}

	@Override
	public void onBackPressed() {
		super.onBackPressed();

		finish();
	}

	protected double calculateTotal(){
		throw new UnsupportedOperationException();
	}

	@Override
	public void onClick(View v) {
		if (v.getId()==R.id.saw) {
			finish();
		}
	}
}
