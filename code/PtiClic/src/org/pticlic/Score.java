package org.pticlic;

import org.pticlic.model.Constant;
import org.pticlic.model.GamePlayed;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;

public class Score extends Activity implements OnClickListener{
	private GamePlayed 	gamePlayed;
	
	
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.score);
		
		if (getIntent().getExtras() != null) {
			// Pour JC : GamePlayed contient toutes les infos sur la partie jouee
			this.gamePlayed = (GamePlayed) getIntent().getExtras().get(Constant.SCORE_INTENT);
		}
//		((TextView)findViewById(R.id.corrects)).setText("Mots corrects : " 
//				+ this.corrects);
//		((TextView)findViewById(R.id.manquants)).setText("Mots manquants : "
//				+ this.manquants);
//		((TextView)findViewById(R.id.mauvais)).setText("Mots mauvais : " 
//				+ this.mauvais);
//		((TextView)findViewById(R.id.total)).setText("Total de " + total 
//				+ "point(s)");
//		 ((Button)findViewById(R.id.jaivu)).setOnClickListener(this);
	}
	
	

	protected double calculateTotal(){
		throw new UnsupportedOperationException();
		//return this.corrects - this.manquants - this.mauvais;
	}

	@Override
	public void onClick(View v) {
		if (v.getId()==R.id.jaivu) {
			startActivity(new Intent(this, Main.class));
		}
		
	}
}
