package org.pticlic.model;

/**
 * 
 * @author John CHARRON
 *
 */

public class TotalScore {

	private TotalScore scoreTotal;
	private WordScore scores;
	
	public TotalScore() {
	}

	public TotalScore(TotalScore scoreTotal, WordScore wordscores) {
		this.scoreTotal = scoreTotal;
		this.scores = wordscores;
	}

	public TotalScore getScoreTotal() {
		return scoreTotal;
	}

	public void setScoreTotal(TotalScore scoreTotal) {
		this.scoreTotal = scoreTotal;
	}

	public WordScore getWordscores() {
		return scores;
	}

	public void setWordscores(WordScore wordscores) {
		this.scores = wordscores;
	}

	@Override
	public String toString() {
		return "TotalScore [scoreTotal=" + scoreTotal + ", wordscores="
				+ scores + "]";
	}
	
	
}