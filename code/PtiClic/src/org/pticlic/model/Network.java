package org.pticlic.model;

import java.io.IOException;
import java.io.InputStreamReader;
import java.net.URL;
import java.net.URLConnection;

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
		GET_GAMES
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
	
	/**
	 * Constructeur
	 * 
	 * @param serverURL Chaine de caractères représentant l'URL où se situe le serveur.
	 * @param mode Le type de partie que l'on veut récupérer.
	 */
	public Network(String serverURL, Mode mode) {
		this.mode = mode;
		this.serverURL = serverURL;
	}
	
	/**
	 * Cette méthode permet de récupérer du serveur un certain nombre de parties.
	 * @param nbGames Le nombre de parties que l'on veut récupérer.
	 * @return
	 */
	public Game getGames(int nbGames) {
		Game game = null;
		try {
			URL url = new URL(this.serverURL);
			URLConnection connection = url.openConnection();
			connection.addRequestProperty("action", "getparties");
			connection.addRequestProperty("nb", String.valueOf(nbGames));
			connection.addRequestProperty("mode", mode.value());
			
			Gson gson = new Gson();
			JsonReader reader = new JsonReader(new InputStreamReader(connection.getInputStream(), "UTF-8"));
			
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
	private Game makeGame(JsonReader reader, Gson gson) throws IOException {
		int 		id = -1;
		int 		cat1 = -1;
		int 		cat2 = -1;
		int 		cat3 = -1;
		int 		cat4 = -1;
		Game.Word 	center = null;
		Game.Word[]	cloud = null;
		
		reader.beginObject();
		while (reader != null && reader.hasNext()) {
			String name = reader.nextName();
			if (name.equals("id")) {
				id = reader.nextInt();
			} else if (name.equals("cat1")) {
				cat1 = reader.nextInt();
			} else if (name.equals("cat2")) {
				cat2 = reader.nextInt();
			} else if (name.equals("cat3")) {
				cat3 = reader.nextInt();
			} else if (name.equals("cat4")) {
				cat4 = reader.nextInt();
			} else if (name.equals("center")) {
				center = gson.fromJson(reader, Game.Word.class);
			} else if (name.equals("cloud")) {
				cloud = gson.fromJson(reader, Game.Word[].class);
			} else {
				reader.skipValue();
			}
		}
		reader.endObject();
		return new Game(id, cat1, cat2, cat3, cat4, center, cloud);
	}
	
	
	/**
	 * Cette méthode permet d'envoyer les parties au serveur pour qu'il puisse les 
	 * rajouter à la base de données, et calculer le score.
	 * @param game La partie jouee par l'utilisateur 
	 * @return <code>true</code> si on a pu envoyer la partie au serveur <code>false</code> sinon.
	 */
	public boolean sendGame(GamePlayed game) {
		try {
			URL url = new URL(this.serverURL);
			URLConnection connection = url.openConnection();
			connection.addRequestProperty("action", "sendpartie");
			connection.addRequestProperty("mode", mode.value());
			
			Gson gson = new Gson();
			String json = gson.toJson(game);
			
			connection.addRequestProperty("json", json);
			
			// TODO : Regarder si il ne faudrait pas que le serveur renvoie une valeur indiquant si l'action c'est bien derouler.
			connection.connect();
		} catch (IOException e) {
			return false;
		}
		return true;
	}
}
