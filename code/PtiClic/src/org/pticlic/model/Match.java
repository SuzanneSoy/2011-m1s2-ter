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
public class Match implements Serializable {

	private static final long 	serialVersionUID = 1L;
	private ArrayList<Integer>	relation1;
	private ArrayList<Integer>	relation2;
	private ArrayList<Integer>	relation3;
	private ArrayList<Integer> 	relation4;
	private DownloadedGame		game;

	public Match() {
		relation1 = new ArrayList<Integer>();
		relation2 = new ArrayList<Integer>();
		relation3 = new ArrayList<Integer>();
		relation4 = new ArrayList<Integer>();
	}

	public void setGame(DownloadedGame game) {
		this.game = game;
	}

	public DownloadedGame getGame() {
		return game;
	}

	public void add(int relation, int word) {
		switch (relation) {
		case 1:		relation1.add(word); break;
		case 2: 	relation2.add(word); break;
		case 3:		relation3.add(word); break;
		case 4:		relation4.add(word); break;
		}
	}

	/**
	 * @return the relation1
	 */
	public ArrayList<Integer> getRelation1() {
		return relation1;
	}

	/**
	 * @return the relation2
	 */
	public ArrayList<Integer> getRelation2() {
		return relation2;
	}

	/**
	 * @return the relation3
	 */
	public ArrayList<Integer> getRelation3() {
		return relation3;
	}

	/**
	 * @return the relation4
	 */
	public ArrayList<Integer> getRelation4() {
		return relation4;
	}
}
