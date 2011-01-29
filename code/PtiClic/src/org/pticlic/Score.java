package org.pticlic;

import model.GamePlayed;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;
import android.widget.TextView;

public class Score extends Activity implements OnClickListener{
	
	private double corrects;
	private double manquants;
	private double mauvais;
	private double total;
	private GamePlayed gamePlayed;
	
	
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.score);
		
		if (getIntent().getExtras() != null) {
			this.corrects = getIntent().getExtras().getDouble("corrects");
			this.manquants = getIntent().getExtras().getDouble("manquants");
			this.mauvais = getIntent().getExtras().getDouble("mauvais");
			this.total = this.calculateTotal();
		}
		((TextView)findViewById(R.id.corrects)).setText("Mots corrects : " 
				+ this.corrects);
		((TextView)findViewById(R.id.manquants)).setText("Mots manquants : "
				+ this.manquants);
		((TextView)findViewById(R.id.mauvais)).setText("Mots mauvais : " 
				+ this.mauvais);
		((TextView)findViewById(R.id.total)).setText("Total de " + total 
				+ "point(s)");
		 ((Button)findViewById(R.id.jaivu)).setOnClickListener(this);
	}
	
	

	protected double calculateTotal(){
		return this.corrects - this.manquants - this.mauvais; 
	}

	@Override
	public void onClick(View v) {
		if (v.getId()==R.id.jaivu) {
			startActivity(new Intent(this, Main.class));
		}
		
	}
}
