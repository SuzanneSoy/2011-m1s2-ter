package org.pticlic.model;

import java.io.Serializable;
import java.util.Arrays;

/**
 * @author Bertrand BRUN
 *
 *	Classe metier reprensentant une parti "Normal" telecharge.
 */
public class DownloadedBaseGame extends DownloadedGame {

	private static final long serialVersionUID = 1L;

	public static class Word implements Serializable {

		private static final long serialVersionUID = 1L;
		private int id;
		private String name;

		public Word() {}

		public int getId() {
			return id;
		}

		public void setId(int id) {
			this.id = id;
		}

		public String getName() {
			return name;
		}

		public void setName(String name) {
			this.name = name;
		}
	}

	private int 				cat1;
	private int 				cat2;
	private int 				cat3;
	private int 				cat4;
	private Word				center;
	private Word[]				cloud;

	public DownloadedBaseGame() {
		super();
		this.cat1 = -1;
		this.cat2 = -1;
		this.cat3 = -1;
		this.cat4 = -1;
		this.center = null;
		this.cloud = null;
	}

	public DownloadedBaseGame(int id, int gid, int pgid, int cat1, int cat2,
			int cat3, int cat4, Word center, Word[] cloud) {
		super(id, gid, pgid);
		this.cat1 = cat1;
		this.cat2 = cat2;
		this.cat3 = cat3;
		this.cat4 = cat4;
		this.center = center;
		this.cloud = cloud;
	}
	
	public static String getName(Word word) {
		return word.getName();
	}
	
	public int getCat(int numCat) {
		switch (numCat) {
		case 1: return getCat1();
		case 2: return getCat2(); 
		case 3: return getCat3(); 
		default: return getCat4(); 
		}
	}
	
	public String getCatString(int numCat) {
		return Relation.getInstance().getRelationName(this.getCat(numCat));
	}

	public int getCat1() {
		return cat1;
	}
	
	public void setCat1(int cat1) {
		this.cat1 = cat1;
	}

	public int getCat2() {
		return cat2;
	}

	public void setCat2(int cat2) {
		this.cat2 = cat2;
	}

	public int getCat3() {
		return cat3;
	}

	public void setCat3(int cat3) {
		this.cat3 = cat3;
	}

	public int getCat4() {
		return cat4;
	}

	public void setCat4(int cat4) {
		this.cat4 = cat4;
	}

	public Word getCentre() {
		return center;
	}
	
	public void setCentre(Word center) {
		this.center = center;
	}
	
	public int getNbWord() {
		return cloud.length;
	}
	
	public Word getWordInCloud(int index) {
		return cloud[index];
	}
	

	@Override
	public String toString() {
		return "DownloadedBaseGame [gid=" + gid + ", pgid=" + pgid + ", id=" + id
				+ ", cat1=" + cat1 + ", cat2=" + cat2 + ", cat3=" + cat3
				+ ", cat4=" + cat4 + ", center=" + center + ", cloud="
				+ Arrays.toString(cloud) + "]";
	}
	
}
