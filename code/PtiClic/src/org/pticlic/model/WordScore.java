package org.pticlic.model;

/**
 * 
 * @author John CHARRON
 *
 */

public class WordScore {

	private int idmot;
	private double score;
	private double probaR1;
	private double probaR2;
	
	public WordScore() {}

	public WordScore(int idmot, double score, double probaR1, double probaR2) {
		this.idmot = idmot;
		this.score = score;
		this.probaR1 = probaR1;
		this.probaR2 = probaR2;
	}

	public int getIdmot() {
		return idmot;
	}

	public void setIdmot(int idmot) {
		this.idmot = idmot;
	}

	public double getScore() {
		return score;
	}

	public void setScore(double score) {
		this.score = score;
	}

	public double getProbaR1() {
		return probaR1;
	}

	public void setProbaR1(double probaR1) {
		this.probaR1 = probaR1;
	}

	public double getProbaR2() {
		return probaR2;
	}

	public void setProbaR2(double probaR2) {
		this.probaR2 = probaR2;
	}

	@Override
	public String toString() {
		return "WordScore [idmot=" + idmot + ", score=" + score + ", probaR1="
				+ probaR1 + ", probaR2=" + probaR2 + "]";
	}
}
