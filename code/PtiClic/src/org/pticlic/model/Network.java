package org.pticlic.model;

import java.io.ByteArrayInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.Serializable;
import java.io.UnsupportedEncodingException;

import org.pticlic.exception.PtiClicException;

import android.content.Context;
import android.content.SharedPreferences;
import android.net.ConnectivityManager;
import android.preference.PreferenceManager;

import com.google.gson.Gson;
import com.google.gson.stream.JsonReader;


/**
 * @author Bertrand BRUN
 *
 * Cette classe permet de dialoguer avec le serveur de PtiClic pour récupérée soit des parties 
 * soit le score qu'a réalisé un utilisateur. 
 * Elle permet aussi d'envoyer au serveur les parties realiser par l'utilisateur pour que le serveur
 * puisse insérer la contribution de l'utilisateur, mais aussi pouvoir calculer le score de celui-ci.
 */
public class Network {

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

	/**
	 * Cette méthode permet de récupérer du serveur un certain nombre de parties.
	 * @param nbGames Le nombre de parties que l'on veut récupérer.
	 * @return
	 */
	public DownloadedGame getGames(int nbGames) throws PtiClicException, Exception {
		switch (mode) {
		case SIMPLE_GAME:
			return DownloadBaseGame(nbGames);
		default:
			return null;
		}
	}

	private DownloadedBaseGame DownloadBaseGame(int nbGames) throws PtiClicException, Exception {
		Gson gson = null;
		String json = null;
		DownloadedBaseGame game = null;

		//		URLConnection connection = url.openConnection();
		//		connection.addRequestProperty("action", Action.GET_GAMES.value());
		//		connection.addRequestProperty("user", this.id);
		//		connection.addRequestProperty("passwd", this.passwd);
		//		connection.addRequestProperty("nb", String.valueOf(nbGames));
		//		connection.addRequestProperty("mode", mode.value());

		String urlS = this.serverURL
			+ "?action=" + Action.GET_GAMES.value()
			+ "&user=" + this.id
			+ "&passwd=" + this.passwd
			+ "&nb=" + String.valueOf(nbGames)
			+ "&mode="+mode.value();
			
		gson = new Gson();
		json = HttpClient.SendHttpPost(urlS);

		try {

			//JsonReader reader = new JsonReader(new InputStreamReader(connection.getInputStream(), "UTF-8"));
			InputStream in = new ByteArrayInputStream(json.getBytes("UTF-8"));
			JsonReader jsonReader = new JsonReader(new InputStreamReader(in));

			// FIXME : Attention lorsque l'on pourra vraiment recupere plusieur partie, il faudra changer ce qui suit.
			jsonReader.beginArray();
			while (jsonReader.hasNext()) {
				game = makeBaseGame(jsonReader, gson);
			}
			jsonReader.endArray();
			jsonReader.close();
		} catch (UnsupportedEncodingException e1) {
			throw new PtiClicException(0, "Impossible de recuperer l'erreur, nous avons pris note de cette erreur.\n Merci");
		} catch (IOException e1) {
			throw new PtiClicException(0, "Impossible de recuperer l'erreur, nous avons pris note de cette erreur.\n Merci");
		} catch (Exception e) {
			throw new PtiClicException(json);
		}

		return game;
	}

	/**
	 * Permet la transformation du Json en une instance de Game.
	 * 
	 * @param reader Le Json sous forme d'un flux.
	 * @param gson Une instance de Gson.
	 * @return Une nouvelle instance de Game.
	 * @throws IOException
	 */
	private DownloadedBaseGame makeBaseGame(JsonReader reader, Gson gson) throws IOException {
		int			gid = -1;
		int 		pgid = -1;
		int 		id = -1;
		int 		cat1 = -1;
		int 		cat2 = -1;
		int 		cat3 = -1;
		int 		cat4 = -1;
		DownloadedBaseGame.Word 	center = null;
		DownloadedBaseGame.Word[]	cloud = null;

		reader.beginObject();
		while (reader.hasNext()) {
			String name = reader.nextName();
			if (name.equals("id")) {
				id = reader.nextInt();
			} else if (name.equals("gid")) {
				gid = reader.nextInt();
			} else if (name.equals("pgid")) {
				pgid = reader.nextInt();
			} else if (name.equals("cat1")) {
				cat1 = reader.nextInt();
			} else if (name.equals("cat2")) {
				cat2 = reader.nextInt();
			} else if (name.equals("cat3")) {
				cat3 = reader.nextInt();
			} else if (name.equals("cat4")) {
				cat4 = reader.nextInt();
			} else if (name.equals("center")) {
				center = gson.fromJson(reader, DownloadedBaseGame.Word.class);
			} else if (name.equals("cloud")) {
				cloud = gson.fromJson(reader, DownloadedBaseGame.Word[].class);
			} else {
				reader.skipValue();
			}
		}
		reader.endObject();
		return new DownloadedBaseGame(id, gid, pgid, cat1, cat2, cat3, cat4, center, cloud);
	}

	/**
	 * Cette méthode permet d'envoyer les parties au serveur pour qu'il puisse les 
	 * rajouter à la base de données, et calculer le score.
	 * @param game La partie jouee par l'utilisateur 
	 * @return Le score sous forme JSON.
	 */
	public double sendGame(Match game) throws PtiClicException, Exception  {
		switch (mode) {
		case SIMPLE_GAME:
			return sendBaseGame(game);
		default:
			return -1;
		}
	}


	public double sendBaseGame(Match game) throws PtiClicException, Exception {
		double score = -1;
		Gson gson = null;
		String json = null;
		try {

			// TODO : ne restera le temps que les requete du serveur passe du GET au POST
			String urlS = this.serverURL
			+ "?action=" + Action.SEND_GAME.value()
			+ "&user=" + this.id
			+ "&passwd=" + this.passwd
			+ "&pgid=" + game.getGame().getPgid()
			+ "&gid=" + game.getGame().getGid()
			+ "&mode="+mode.value();

			// TODO : faut gere le mode
			for (Integer i : game.getRelation1()) {
				urlS += "&" + i + "=" + ((DownloadedBaseGame)game.getGame()).getCat1() ;
			}
			for (Integer i : game.getRelation2()) {
				urlS += "&" +  i + "=" + ((DownloadedBaseGame)game.getGame()).getCat2();
			}
			for (Integer i : game.getRelation3()) {
				urlS += "&" +  i + "=" + ((DownloadedBaseGame)game.getGame()).getCat3();
			}
			for (Integer i : game.getRelation4()) {
				urlS += "&" + i + "=" + ((DownloadedBaseGame)game.getGame()).getCat4();
			}

			//			URL url = new URL(this.serverURL); // Attention ! this.serverURL contient "/server.php"
			//			URLConnection connection = url.openConnection();
			//			connection.addRequestProperty("action", Action.SEND_GAME.value());
			//			connection.addRequestProperty("user", this.id);
			//			connection.addRequestProperty("passwd", this.passwd);
			//			connection.addRequestProperty("mode", mode.value());
			//			connection.addRequestProperty("pgid", String.valueOf(game.getGame().getId()));

			gson = new Gson();
			json = HttpClient.SendHttpPost(urlS);

			//JsonReader reader = new JsonReader(new InputStreamReader(connection.getInputStream(), "UTF-8"));
			InputStream in = new ByteArrayInputStream(json.getBytes("UTF-8"));
			JsonReader jsonReader = new JsonReader(new InputStreamReader(in));

			// Comme gson ne renvoie pas une erreur si l'objet qui recupere ne correspond pas a la classe qu'il attends.
			// On creer tout d'abord une objet error et si celui-ci est vide on creer l'objet score, sinon on lance
			// une exception.
			PtiClicException.Error error = gson.fromJson(json, PtiClicException.Error.class);
			if (error.getMsg() == null) {
				score = getScore(jsonReader, gson);
			} else {
				throw new PtiClicException(error);
			}

		} catch (UnsupportedEncodingException e1) {
			throw new PtiClicException(0, "Impossible de recuperer l'erreur, nous avons pris note de cette erreur.\n Merci");
		} catch (IOException e1) {
			throw new PtiClicException(0, "Impossible de recuperer l'erreur, nous avons pris note de cette erreur.\n Merci");
		}

		return score;
	}

	private double getScore(JsonReader reader, Gson gson) throws IOException {
		double					score = -1;

		reader.beginObject();
		while (reader.hasNext()) {
			String name = reader.nextName();
			if (name.equals("score")) {
				score = reader.nextDouble();
			} else if (name.equals("newGame")) {
				DownloadedBaseGame newGame = gson.fromJson(reader, DownloadedBaseGame.class);
				newGameJson = gson.toJson(newGame);
			} else {
				reader.skipValue();
			}
		}
		reader.endObject();
		return score;	
	}

	public String getNewGame() {
		return this.newGameJson;
	}
}
