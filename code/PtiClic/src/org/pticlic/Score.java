package org.pticlic;

<<<<<<< HEAD
import model.GamePlayed;
=======
import org.pticlic.model.Constant;
import org.pticlic.model.GamePlayed;

>>>>>>> f74dc5006757fe52d362cf1a0d642d01dda7cf43
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.view.View.OnClickListener;
<<<<<<< HEAD
import android.widget.Button;
import android.widget.TextView;

public class Score extends Activity implements OnClickListener{
	
	private double corrects;
	private double manquants;
	private double mauvais;
	private double total;
=======

public class Score extends Activity implements OnClickListener{

>>>>>>> f74dc5006757fe52d362cf1a0d642d01dda7cf43
	private GamePlayed gamePlayed;
	
	
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.score);
		
		if (getIntent().getExtras() != null) {
<<<<<<< HEAD
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
=======
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
>>>>>>> f74dc5006757fe52d362cf1a0d642d01dda7cf43
	}
	
	

	protected double calculateTotal(){
<<<<<<< HEAD
		return this.corrects - this.manquants - this.mauvais; 
=======
		throw new UnsupportedOperationException();
		//return this.corrects - this.manquants - this.mauvais; 
>>>>>>> f74dc5006757fe52d362cf1a0d642d01dda7cf43
	}

	@Override
	public void onClick(View v) {
		if (v.getId()==R.id.jaivu) {
			startActivity(new Intent(this, Main.class));
		}
		
	}
}
