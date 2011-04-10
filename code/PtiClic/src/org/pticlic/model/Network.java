package org.pticlic.model;

import java.io.Serializable;

import android.content.Context;
import android.content.SharedPreferences;
import android.net.ConnectivityManager;
import android.preference.PreferenceManager;

import com.google.gson.Gson;


/**
 * @author Bertrand BRUN
 *
 * Cette classe permet de dialoguer avec le serveur de PtiClic pour récupérée soit des parties 
 * soit le score qu'a réalisé un utilisateur. 
 * Elle permet aussi d'envoyer au serveur les parties realiser par l'utilisateur pour que le serveur
 * puisse insérer la contribution de l'utilisateur, mais aussi pouvoir calculer le score de celui-ci.
 */
public class Network {

	public static class ScoreResponse {
		private int scoreTotal;
		private int[] scores;
		private String newGame;
		private boolean alreadyPlayed;
		
		public ScoreResponse() {}
		
		public int[] getScores() {
			return scores;
		}
		
		public int getScoreOfWord(int i) {
			return scores[i];
		}
		
		public int getScoreTotal() {
			return scoreTotal;
		}
		
		public String getNewGame() {
			return newGame;
		}
		
		public boolean getAlreadyPlayed() { 
			return alreadyPlayed;
		}
	}

	public static class Check implements Serializable {
		private static final long serialVersionUID = 1L;
		private boolean login_ok = false;

		public boolean isLogin_ok() {
			return login_ok;
		}

		public void setLogin_ok(boolean login_ok) {
			this.login_ok = login_ok;
		}
	}

	String	 	newGameJson = null;

	public enum Action {
		GET_GAMES(0),
		SEND_GAME(1),
		CREATE_GAME(2),
		CHECK_LOGIN(3);

		private final int value;

		Action(int value) {
			this.value = value;
		}

		private String value() { return String.valueOf(value); }
	}

	public enum Mode {
		SIMPLE_GAME("normal");

		private final String value;

		Mode(String value) {
			this.value = value;
		}

		private String value() { return value; }
	}

	private Mode mode;
	private String serverURL;
	private String id;
	private String passwd;

	/**
	 * Constructeur
	 * 
	 * @param serverURL Chaine de caractères représentant l'URL où se situe le serveur.
	 * @param mode Le type de partie que l'on veut récupérer.
	 * @param id L'indentifiant du joueur.
	 * @param passwd Le mot de passe de l'utilisateur.
	 */
	public Network(String serverURL, Mode mode, String id, String passwd) {
		this.mode = mode;
		this.serverURL = serverURL + "/server.php";
		this.id = id;
		this.passwd = passwd;
	}

	/**
	 * Permet de savoir si l'application a access a internet ou non
	 * 
	 * @param context l'activite permettant de tester l'access a internet
	 * @return <code>true</code> si on a access a internet <code>false</code> sinon
	 */
	public static boolean isConnected(Context context) {
		ConnectivityManager cm = (ConnectivityManager)context.getSystemService(Context.CONNECTIVITY_SERVICE);		
		if (cm != null && (cm.getActiveNetworkInfo() == null 
				|| !cm.getActiveNetworkInfo().isConnected())) {
			return false;
		}
		return true;
	}

	/**
	 * Permet de verifier que la combinaison login/mdp est correct
	 * 
	 * @param context l'activite permettant de tester l'access a internet
	 * @param id l'identifiant de l'utilisateur
	 * @param passwd le mot de passe de l'utilisateur
	 * @return <code>true</code> si la combinaison login/mdp est correct <code>false</code> sinon
	 */
	public static boolean isLoginCorrect(Context context) {

		SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(context);
		String serverURL = sp.getString(Constant.SERVER_URL, Constant.SERVER) + "/server.php";
		String id = sp.getString(Constant.USER_ID, "joueur");
		String passwd = sp.getString(Constant.USER_PASSWD, "");
		Boolean auth = sp.getBoolean(Constant.SERVER_AUTH, false);

		if (auth) {
			return auth;
		}

		Gson gson = null;
		String json = null;
		boolean res = false;

		String urlS = serverURL
		+ "?action=" + Action.CHECK_LOGIN.value()
		+ "&user=" + id
		+ "&passwd=" + passwd;

		gson = new Gson();
		json = HttpClient.SendHttpPost(urlS);

		Check check = gson.fromJson(json, Check.class);
		res = check.isLogin_ok();

		SharedPreferences.Editor editor = sp.edit();
		editor.putBoolean(Constant.SERVER_AUTH, res);
		editor.commit();

		return res;
	}
}
