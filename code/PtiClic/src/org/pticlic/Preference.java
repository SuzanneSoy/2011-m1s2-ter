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
			
			SharedPreferences.Editor editor = sharedPreferences.edit();
			editor.putBoolean(Constant.SERVER_AUTH, false);
			editor.commit();
			
			if (Network.isConnected(this)) {
				if (Network.isLoginCorrect(this)) {
					Toast.makeText(this,
							getString(R.string.preferences_loginmdp_valid),
							Toast.LENGTH_LONG).show();
					
				} else {
					Toast.makeText(this,
							getString(R.string.preferences_loginmdp_notvalid),
							Toast.LENGTH_LONG).show();
				}
			} else {
				Toast.makeText(this,
						getString(R.string.preferences_nonetworks),
						Toast.LENGTH_LONG).show();
			}
		}

	}


}
