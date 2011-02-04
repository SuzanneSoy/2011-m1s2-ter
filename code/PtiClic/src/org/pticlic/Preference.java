package org.pticlic;

import org.pticlic.model.Constant;
import org.pticlic.model.Network;

import android.content.SharedPreferences;
import android.content.SharedPreferences.OnSharedPreferenceChangeListener;
import android.os.Bundle;
import android.preference.PreferenceActivity;
import android.widget.Toast;

public class Preference extends PreferenceActivity implements OnSharedPreferenceChangeListener {

	/* (non-Javadoc)
	 * @see android.preference.PreferenceActivity#onCreate(android.os.Bundle)
	 */
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		addPreferencesFromResource(R.xml.prefs);

		SharedPreferences prefs = getPreferenceManager().getSharedPreferences();
		prefs.registerOnSharedPreferenceChangeListener(this);

	}

	@Override
	public void onSharedPreferenceChanged(SharedPreferences sharedPreferences, String key) {
		if (key.equals("passwd")) {
			if (Network.isConnected(this)) {
				String id = sharedPreferences.getString("login", "");
				String passwd = sharedPreferences.getString("passwd", "");
				if (Network.isLoginCorrect(this, id, passwd)) {
					Toast.makeText(this,
							"Couple login/mdp valide.",
							Toast.LENGTH_LONG).show();
					
					SharedPreferences.Editor editor = sharedPreferences.edit();
					editor.putBoolean(Constant.SERVER_AUTH, true);
					editor.commit();
				} else {
					Toast.makeText(this,
							"Couple login/mdp non valide.",
							Toast.LENGTH_LONG).show();
					
					SharedPreferences.Editor editor = sharedPreferences.edit();
					editor.putBoolean(Constant.SERVER_AUTH, false);
					editor.commit();
				}
			} else {
				Toast.makeText(this,
						"Pas connecter au reseau, verification du login/mdp impossible",
						Toast.LENGTH_LONG).show();
			}
		}

	}


}
