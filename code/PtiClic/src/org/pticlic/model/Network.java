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
		try {
			URL url = new URL(this.serverURL);
			URLConnection connection = url.openConnection();
			connection.addRequestProperty("action", "getparties");
			connection.addRequestProperty("nb", String.valueOf(nbGames));
			connection.addRequestProperty("mode", mode.value());
			
			Gson gson = new Gson();
//			JsonReader reader = new JsonReader(new InputStreamReader(connection.getInputStream(), "UTF-8"));
//			Game game = gson.fromJson(reader, Game[].class);
			
			// TODO : A enlever sert juste pour les tests
			String json = 
					"{" +
					"	id: 1234," +
					"	cat1: 11," +
					"	cat2: 23," +
					"	cat3: -1," +
					"	cat4: -1, " +
					"	center: { id: 555, name: \"chat\" }, " +
					"	cloud: [" +
					"		{ id: 123, name: \"souris\" }," +
					"		{ id: 111, name: \"lait\" }," +
					"		{ id: 345, name: \"machine à laver\" }" +
					"	]" +
					"}";
			Game game = gson.fromJson(json, Game.class);
			
			return game;
		} catch (IOException e) {
			return null;
		}
	}
	
	public boolean sendGame(Game game) {
		throw new UnsupportedOperationException();
	}
}
