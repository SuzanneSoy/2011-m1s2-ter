package org.pticlic;

import android.app.Activity;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.TextView;

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
		if (nbrel > 0) { r1.setOnClickListener(this); } else { r1.setVisibility(View.GONE); }
		if (nbrel > 1) { r2.setOnClickListener(this); } else { r2.setVisibility(View.GONE); }
		if (nbrel > 2) { r3.setOnClickListener(this); } else { r3.setVisibility(View.GONE); }
		if (nbrel > 3) { r4.setOnClickListener(this); } else { r4.setVisibility(View.GONE); }
		
		r1.setText("=");
		r2.setText("∈");
		((TextView)findViewById(R.id.mainWord)).setText("Chat");
		((TextView)findViewById(R.id.currentWord)).setText("Matou");
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