package org.pticlic;

import org.pticlic.games.BaseGame;
import org.pticlic.model.Constant;
import org.pticlic.model.Network;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.net.Uri;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.ImageView;
import android.widget.TextView;

public class FrontPage extends Activity implements OnClickListener{

	private Uri uri = null;
	
	/** Called when the activity is first created. */
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.frontpage);

		// Écoute des clics sur les différents boutons
		((ImageView)findViewById(R.id.prefs)).setOnClickListener(this);
		((ImageView)findViewById(R.id.play)).setOnClickListener(this);
		((ImageView)findViewById(R.id.infoButton)).setOnClickListener(this);

		SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(this);
		String serverURL = sp.getString(Constant.SERVER_URL, Constant.SERVER);
		Uri.parse(serverURL + "/signup.php");
	}

	@Override
	protected void onStart() {
		super.onStart();

		// On récupère le nom du joueur des préférences.
		SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(this);
		Boolean connected = sp.getBoolean(Constant.SERVER_AUTH, false);
		if (connected) {
			((TextView)findViewById(R.id.login)).setText(R.string.frontpage_user_connected);	
		} else {
			((TextView)findViewById(R.id.login)).setText(R.string.frontpage_user_notconnected);
		}
	}

	/* (non-Javadoc)
	 * @see android.view.View.OnClickListener#onClick(android.view.View)
	 */
	@Override
	public void onClick(View v) {
		switch (v.getId()) {
		case (R.id.prefs) : startActivity(new Intent(this, Preference.class)); break;
		case (R.id.play) : checkAllIsOk(BaseGame.class); break;
		case (R.id.infoButton) : startActivity(new Intent(this, Information.class)); break;
		}
	}

	@SuppressWarnings("rawtypes")
	private void checkAllIsOk(Class c) {
		if (Network.isConnected(this)) {
			if (Network.isLoginCorrect(this)) {
				startActivity(new Intent(this, c));
			} else {
				AlertDialog.Builder builder = new AlertDialog.Builder(this);
				builder.setTitle(getString(R.string.app_name))
				.setIcon(android.R.drawable.ic_dialog_alert)
				.setMessage(getString(R.string.frontpage_bad_loginmdp))
				.setCancelable(false)
				.setNeutralButton(getString(R.string.frontpage_inscription_button), new DialogInterface.OnClickListener() {
					public void onClick(DialogInterface dialog, int id) {
						dialog.dismiss();
						// TODO : Essayer de trouver comment mettre l'url qui est dans les preferences.
						startActivity(new Intent(Intent.ACTION_VIEW, uri));
					}
				})
				.setPositiveButton(getString(R.string.frontpage_preference_button), new DialogInterface.OnClickListener() {
					public void onClick(DialogInterface dialog, int id) {
						dialog.dismiss();
						startActivity(new Intent(getApplicationContext(), Preference.class));
					}
				});
				AlertDialog alert = builder.create();
				alert.show();
			}
		} else {
			AlertDialog.Builder builder = new AlertDialog.Builder(this);
			builder.setTitle(getString(R.string.app_name))
			.setIcon(android.R.drawable.ic_dialog_alert)
			.setMessage(getString(R.string.frontpage_no_connection))
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
