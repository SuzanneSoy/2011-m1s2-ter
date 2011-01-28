package org.pticlic;

import android.app.Activity;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;

public class Game extends Activity implements OnClickListener {
	/** Called when the activity is first created. */
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		int nbrel = 2; 
		setContentView(R.layout.game);
		
		// Boutons des relations
		Button r1 = ((Button)findViewById(R.id.relation1));
		Button r2 = ((Button)findViewById(R.id.relation2));
		Button r3 = ((Button)findViewById(R.id.relation3));
		Button r4 = ((Button)findViewById(R.id.relation4));
		
		// Écoute des clics sur les relations
		r1.setOnClickListener(this);
		r2.setOnClickListener(this);
		r3.setOnClickListener(this);
		r4.setOnClickListener(this);
	}
	
	/* (non-Javadoc)
	 * @see android.view.View.OnClickListener#onClick(android.view.View)
	 */
	@Override
	public void onClick(View v) {
		switch (v.getId()) {
		case (R.id.relation1) : break;
		case (R.id.relation2) : break;
		case (R.id.relation3) : break;
		case (R.id.relation4) : break;
		}
	}
}