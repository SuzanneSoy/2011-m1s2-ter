package org.pticlic.model;

import java.util.ArrayList;

public class GamePlayed {
	private ArrayList<CharSequence>	relation1;
	private ArrayList<CharSequence>	relation2;
	private ArrayList<CharSequence>	relation3;
	private ArrayList<CharSequence> 	relation4;
	private Game				game;

	public GamePlayed() {
		relation1 = new ArrayList<CharSequence>();
		relation2 = new ArrayList<CharSequence>();
		relation3 = new ArrayList<CharSequence>();
		relation4 = new ArrayList<CharSequence>();
	}

	public void setGame(Game game) {
		this.game = game;
	}

	public void add(int relation, CharSequence word) {
		switch (relation) {
		case 1:	relation1.add(word); break;
		case 2: relation2.add(word); break;
		case 3:	relation3.add(word); break;
		case 4:	relation4.add(word); break;
		}
	}

}
