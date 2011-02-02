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

	private static final long 	serialVersionUID = 1L;
	private ArrayList<String>	relation1;
	private ArrayList<String>	relation2;
	private ArrayList<String>	relation3;
	private ArrayList<String> 	relation4;
	private ArrayList<String> 	trash;
	private DownloadedGame		game;

	public GamePlayed() {
		relation1 = new ArrayList<String>();
		relation2 = new ArrayList<String>();
		relation3 = new ArrayList<String>();
		relation4 = new ArrayList<String>();
		trash = new ArrayList<String>();
	}

	public void setGame(DownloadedGame game) {
		this.game = game;
	}

	public DownloadedGame getGame() {
		return game;
	}

	public void add(int relation, String word) {
		switch (relation) {
		case 1:		relation1.add(word); break;
		case 2: 	relation2.add(word); break;
		case 3:		relation3.add(word); break;
		case 4:		relation4.add(word); break;
		default:	trash.add(word); break;
		}
	}

	/**
	 * @return the relation1
	 */
	public ArrayList<String> getRelation1() {
		return relation1;
	}

	/**
	 * @return the relation2
	 */
	public ArrayList<String> getRelation2() {
		return relation2;
	}

	/**
	 * @return the relation3
	 */
	public ArrayList<String> getRelation3() {
		return relation3;
	}

	/**
	 * @return the relation4
	 */
	public ArrayList<String> getRelation4() {
		return relation4;
	}

	/**
	 * @return the trash
	 */
	public ArrayList<String> getTrash() {
		return trash;
	}

	/**
	 * @param relation1 the relation1 to set
	 */
	public void setRelation1(ArrayList<String> relation1) {
		this.relation1 = relation1;
	}

	/**
	 * @param relation2 the relation2 to set
	 */
	public void setRelation2(ArrayList<String> relation2) {
		this.relation2 = relation2;
	}

	/**
	 * @param relation3 the relation3 to set
	 */
	public void setRelation3(ArrayList<String> relation3) {
		this.relation3 = relation3;
	}

	/**
	 * @param relation4 the relation4 to set
	 */
	public void setRelation4(ArrayList<String> relation4) {
		this.relation4 = relation4;
	}

	/**
	 * @param trash the trash to set
	 */
	public void setTrash(ArrayList<String> trash) {
		this.trash = trash;
	}
	
	
}
