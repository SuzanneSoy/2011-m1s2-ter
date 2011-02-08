package org.pticlic.model;

import java.io.IOException;
import java.io.InputStreamReader;
import java.net.URL;
import java.net.URLConnection;

import android.content.Context;
import android.net.ConnectivityManager;

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

	public enum Action {
		GET_GAMES(0),
		SEND_GAME(1);

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
		this.serverURL = serverURL;
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
	public static boolean isLoginCorrect(Context context, String id, String passwd) {
		
		//TODO : A decommenter le jour ou ce sera implemente cote serveur
//		SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(context);
//		String serverURL = sp.getString(Constant.SERVER_URL, "http://dumbs.fr/~bbrun/pticlic.json");
//
//		URL url;
//		boolean res = false;
//		try {
//			url = new URL(serverURL);
//			URLConnection connection = url.openConnection();
//			connection.addRequestProperty("action", "verifyAccess");
//			connection.addRequestProperty("user", id);
//			connection.addRequestProperty("passwd", passwd);
//
//			InputStream in = connection.getInputStream();
//			BufferedReader buf = new BufferedReader(new InputStreamReader(in));
//			res = Boolean.getBoolean(buf.readLine());
//
//		} catch (MalformedURLException e) {
//			return false;
//		} catch (IOException e) {
//			return false;
//		}
//
//		return res;
		return true;
	}

	/**
	 * Cette méthode permet de récupérer du serveur un certain nombre de parties.
	 * @param nbGames Le nombre de parties que l'on veut récupérer.
	 * @return
	 */
	public DownloadedGame getGames(int nbGames) {
		DownloadedGame game = null;
		try {
			// TODO : ne restera le temps que les requete du serveur passe du GET au POST
			String urlS = this.serverURL+"/pticlic.php?"
			+ "action=" + Action.GET_GAMES.value()
			+ "&user=" + this.id
			+ "&passwd=" + this.passwd
			+ "&nb=" + String.valueOf(nbGames)
			+ "&mode="+mode.value();
			
			URL url = new URL(urlS);			

//			URLConnection connection = url.openConnection();
//			connection.addRequestProperty("action", Action.GET_GAMES.value());
//			connection.addRequestProperty("user", this.id);
//			connection.addRequestProperty("passwd", this.passwd);
//			connection.addRequestProperty("nb", String.valueOf(nbGames));
//			connection.addRequestProperty("mode", mode.value());

			Gson gson = new Gson();
			//JsonReader reader = new JsonReader(new InputStreamReader(connection.getInputStream(), "UTF-8"));
			JsonReader reader = new JsonReader(new InputStreamReader(url.openStream(), "UTF-8"));

			// FIXME : Attention lorsque l'on pourra vraiment recupere plusieur partie, il faudra changer ce qui suit.
			reader.beginArray();
			while (reader.hasNext()) {
				game = makeGame(reader, gson);
			}
			reader.endArray();
			reader.close();
		} catch (IOException e) {
			e.printStackTrace();

			return null;
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
	private DownloadedGame makeGame(JsonReader reader, Gson gson) throws IOException {
		int			gid = -1;
		int 		pgid = -1;
		int 		id = -1;
		int 		cat1 = -1;
		int 		cat2 = -1;
		int 		cat3 = -1;
		int 		cat4 = -1;
		DownloadedGame.Word 	center = null;
		DownloadedGame.Word[]	cloud = null;

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
				center = gson.fromJson(reader, DownloadedGame.Word.class);
			} else if (name.equals("cloud")) {
				cloud = gson.fromJson(reader, DownloadedGame.Word[].class);
			} else {
				reader.skipValue();
			}
		}
		reader.endObject();
		return new DownloadedGame(id, gid, pgid, cat1, cat2, cat3, cat4, center, cloud);
	}


	/**
	 * Cette méthode permet d'envoyer les parties au serveur pour qu'il puisse les 
	 * rajouter à la base de données, et calculer le score.
	 * @param game La partie jouee par l'utilisateur 
	 * @return Le score sous forme JSON.
	 */
	public TotalScore sendGame(Match game) {
		TotalScore score = null;
		try {
			URL url = new URL(this.serverURL);
			URLConnection connection = url.openConnection();
			connection.addRequestProperty("action", Action.SEND_GAME.value());
			connection.addRequestProperty("user", this.id);
			connection.addRequestProperty("passwd", this.passwd);
			connection.addRequestProperty("mode", mode.value());
			connection.addRequestProperty("pgid", String.valueOf(game.getGame().getId()));

			if (game.getGame().getCat1() != -1) {
				for (Integer i : game.getRelation1()) {
					connection.addRequestProperty("cat1[]", i.toString());
				}
			}
			if (game.getGame().getCat2() != -1) {
				for (Integer i : game.getRelation2()) {
					connection.addRequestProperty("cat2[]", i.toString());
				}
			}
			if (game.getGame().getCat3() != -1) {
				for (Integer i : game.getRelation3()) {
					connection.addRequestProperty("cat3[]", i.toString());
				}
			}
			if (game.getGame().getCat4() != -1) {
				for (Integer i : game.getRelation4()) {
					connection.addRequestProperty("cat4[]", i.toString());
				}
			}
			for (Integer i : game.getTrash()) {
				connection.addRequestProperty("trash[]", i.toString());
			}

			Gson gson = new Gson();
			JsonReader reader = new JsonReader(new InputStreamReader(connection.getInputStream(), "UTF-8"));

			score = gson.fromJson(reader, TotalScore.class);


		} catch (IOException e) {
			return score;
		}
		return score;
	}
}
