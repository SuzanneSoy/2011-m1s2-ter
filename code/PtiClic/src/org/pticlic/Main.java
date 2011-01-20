package org.pticlic;

import android.app.Activity;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.TextView;

public class Main extends Activity implements OnClickListener {
  
	/** Called when the activity is first created. */
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.main);
        ((Button)findViewById(R.id.prefs)).setOnClickListener(this);
        
        // On récupère le nom du joueur des préférences.
        SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(this);
        String loginPref = sp.getString("login", "joueur");
        ((TextView)findViewById(R.id.login)).setText("Login : " + loginPref);
    }

	@Override
	public void onClick(View v) {
		if (v.getId()==R.id.prefs) {
			startActivity(new Intent(this, Preference.class));
		}
	}
}