package org.pticlic.model;

import java.io.Serializable;


public class Game implements Serializable {

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
	
	private int 		id;
	private int 		cat1;
	private int 		cat2;
	private int 		cat3;
	private int 		cat4;
	private Word		center;
	private Game.Word[] cloud;
	
	public Game() {
		cloud = new Game.Word[3];
	}

	public int getNbRelation() {
		int res = 0;
		
		if (cat1 != -1) {
			res++;
		}
		if (cat2 != -1) {
			res++;
		}
		if (cat3 != -1) {
			res++;
		}
		if (cat4 != -1) {
			res++;
		}
		
		return res;
	}
	
	public static String getName(Word word) {
		return word.getName();
	}
	
	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
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
	
	public Game.Word getWordInCloud(int index) {
		return cloud[index];
	}

	@Override
	public String toString() {
		return "Game [id=" + id + ", cat1=" + cat1 + ", cat2=" + cat2
				+ ", cat3=" + cat3 + ", cat4=" + cat4 + ", center=" + center
				+ ", cloud=" + cloud + "]";
	}
	
	
}
