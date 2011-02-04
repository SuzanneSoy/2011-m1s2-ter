package org.pticlic;

import org.pticlic.games.BaseGame;
import org.pticlic.model.Network;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.ImageView;
import android.widget.TextView;

public class FrontPage extends Activity implements OnClickListener{

	/** Called when the activity is first created. */
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.frontpage);

		// Écoute des clics sur les différents boutons
		((ImageView)findViewById(R.id.prefs)).setOnClickListener(this);
		((ImageView)findViewById(R.id.play)).setOnClickListener(this);
		((ImageView)findViewById(R.id.infoButton)).setOnClickListener(this);

	}

	@Override
	protected void onStart() {
		super.onStart();

		if (Network.isConnected(this))
			System.out.println("Connecter");
		else
			System.out.println("Non Connecter");

		// On récupère le nom du joueur des préférences.
		SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(this);
		String loginPref = sp.getString("login", "joueur");
		// On l'ajoute dans le TextView prévu à cet effet
		((TextView)findViewById(R.id.login)).setText("Login : " + loginPref);
	}

	/* (non-Javadoc)
	 * @see android.view.View.OnClickListener#onClick(android.view.View)
	 */
	@Override
	public void onClick(View v) {
		switch (v.getId()) {
		case (R.id.prefs) : startActivity(new Intent(this, Preference.class)); break;
		case (R.id.play) : checkNetworkConnection(BaseGame.class); break;
		case (R.id.infoButton) : startActivity(new Intent(this, Information.class)); break;
		}
	}

	@SuppressWarnings("rawtypes")
	private void checkNetworkConnection(Class c) {
		if (Network.isConnected(this)) {
			startActivity(new Intent(this, c));
		} else {
			AlertDialog.Builder builder = new AlertDialog.Builder(this);
			builder.setTitle(getString(R.string.app_name))
			.setIcon(android.R.drawable.ic_dialog_alert)
			.setMessage("Problème de connexion au serveur. Vérifiez que vous êtes connecté au réseau.")
			.setCancelable(false)
			.setNegativeButton("Ok", new DialogInterface.OnClickListener() {
				public void onClick(DialogInterface dialog, int id) {
					dialog.cancel();
				}
			});
			AlertDialog alert = builder.create();
			alert.show();
		}
	}

	@Override
	public void onBackPressed() {
		System.exit(0);
	}

}
