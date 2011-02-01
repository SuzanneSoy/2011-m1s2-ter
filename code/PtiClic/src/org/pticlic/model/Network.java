package org.pticlic.model;

import java.io.IOException;
import java.io.InputStreamReader;
import java.net.URL;
import java.net.URLConnection;

import com.google.gson.Gson;
import com.google.gson.stream.JsonReader;

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
	
	public Network(String serverURL, Mode mode) {
		this.mode = mode;
		this.serverURL = serverURL;
	}
	
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
			
			return game;
			 
		} catch (IOException e) {
			e.printStackTrace();
			
			return null;
		}
	}
	
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
	
	public boolean sendGame(Game game) {
		throw new UnsupportedOperationException();
	}
}
