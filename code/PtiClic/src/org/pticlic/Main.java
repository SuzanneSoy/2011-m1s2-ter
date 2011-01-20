package org.pticlic;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;

public class Main extends Activity implements OnClickListener {
    /** Called when the activity is first created. */
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.main);
        ((Button)findViewById(R.id.prefs)).setOnClickListener(this);
        
    }

	@Override
	public void onClick(View v) {
		if (v.getId()==R.id.prefs) {
			startActivity(new Intent(this, Preference.class));
		}
	}
}