package org.pticlic.model;

import java.io.Serializable;
import java.util.ArrayList;

/**
 * @author Bertrand BRUN
 * 
 * Cette classe represente une partie joue.
 * Elle sera envoyer au serveur pour que celui-ci
 * puisse calculer le score obtenue.
 *
 */
public class GamePlayed implements Serializable {

	private static final long serialVersionUID = 1L;
	private ArrayList<CharSequence>	relation1;
	private ArrayList<CharSequence>	relation2;
	private ArrayList<CharSequence>	relation3;
	private ArrayList<CharSequence> relation4;
	private ArrayList<CharSequence> poubelle;
	private DownloadedGame			game;

	public GamePlayed() {
		relation1 = new ArrayList<CharSequence>();
		relation2 = new ArrayList<CharSequence>();
		relation3 = new ArrayList<CharSequence>();
		relation4 = new ArrayList<CharSequence>();
		poubelle = new ArrayList<CharSequence>();
	}

	public void setGame(DownloadedGame game) {
		this.game = game;
	}

	public DownloadedGame getGame() {
		return game;
	}

	public void add(int relation, CharSequence word) {
		switch (relation) {
		case 1:		relation1.add(word); break;
		case 2: 	relation2.add(word); break;
		case 3:		relation3.add(word); break;
		case 4:		relation4.add(word); break;
		default:	poubelle.add(word); break;
		}
	}
}
