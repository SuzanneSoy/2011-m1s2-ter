package org.pticlic;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;

import android.app.Activity;
import android.graphics.Color;
import android.os.Bundle;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.widget.TextView;

public class Information extends Activity {

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.info);
		
		((TextView)findViewById(R.id.infoVersion)).setText("version : " + getString(R.string.version));
		InputStream in = getResources().openRawResource(R.raw.info);
		WebView webview = (WebView)findViewById(R.id.textContent);
		webview.setBackgroundColor(Color.WHITE);
		webview.setScrollBarStyle(WebView.SCROLLBARS_OUTSIDE_OVERLAY);
		WebSettings webSettings = webview.getSettings();
		webSettings.setJavaScriptEnabled(true);
		
		if (in != null) {
			
			InputStreamReader tmp = new InputStreamReader(in);
			BufferedReader reader = new BufferedReader(tmp);
			String html;
			StringBuffer buf = new StringBuffer();
			
			try {
				while ((html = reader.readLine()) != null) {
					buf.append(html + "\n");
				}
			
			in.close();
			webview.loadUrl("http://www.pticlic.fr/html5/code/html5/");
			
			} catch (IOException e) {
				//TODO : Ajouter un boite de dialog indiquant qu'une erreur est arrivee.
			}
		}
	}
}
