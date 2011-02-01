package org.pticlic;

import org.pticlic.model.Constant;
import org.pticlic.model.GamePlayed;

import android.app.Activity;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
import android.widget.Button;

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
		
		((Button)findViewById(R.id.saw)).setOnClickListener(this);
		
	}
	
	@Override
	public void onBackPressed() {
		super.onBackPressed();
		
		finish();
	}
	
	protected double calculateTotal(){
		throw new UnsupportedOperationException();
		//return this.corrects - this.manquants - this.mauvais;
	}

	@Override
	public void onClick(View v) {
		if (v.getId()==R.id.saw) {
			finish();
		}
		
	}
}
