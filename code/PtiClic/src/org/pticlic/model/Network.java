package org.pticlic.model;

import java.net.URI;
import java.net.URISyntaxException;

import org.apache.http.HttpResponse;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.DefaultHttpClient;

import android.content.Context;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;

public class Network {

	public enum Action {
		GET_GAMES
	}
	
	public enum Mode {
		SIMPLE_GAME
	}
	
	private Mode mode;
	private Context context;
	
	public Network(Context context, Mode mode) {
		this.mode = mode;
		this.context = context;
	}
	
	
	// TODO : faire se qui est la 
//	http://developer.android.com/reference/java/net/URLConnection.html#addRequestProperty%28java.lang.String,%20java.lang.String%29
	
	public GamePlayed getGames(int nbGames) {
		DefaultHttpClient httpClient = new DefaultHttpClient();
		SharedPreferences sp = PreferenceManager.getDefaultSharedPreferences(context);
		String serverUrl = sp.getString(Constant.SERVER_URL, "http://serveur/pticlic.php");
		
		try {
			HttpResponse res;
			URI uri = new URI(serverUrl);
			HttpPost methodPost = new HttpPost(uri);
			//methodPost.setEntity(new StringEntity(s, charset));
		} catch (URISyntaxException e) {
			return null;
		}
		
		return null;
	}
	
	public boolean sendGame(GamePlayed game) {
		throw new UnsupportedOperationException();
	}
}
