package org.pticlic;

import org.pticlic.js.JavaScriptInterface;
import org.pticlic.model.Constant;

import android.app.Activity;
import android.os.Bundle;
import android.webkit.WebSettings;
import android.webkit.WebView;

public class FrontPage extends Activity {

	private WebView webView;
	private JavaScriptInterface js = null;
	
	/** Called when the activity is first created. */
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.frontpage);

		webView = (WebView) findViewById(R.id.webview);
		WebSettings webSettings = webView.getSettings();
		webSettings.setJavaScriptEnabled(true);
		
		js = new JavaScriptInterface(this);
		webView.addJavascriptInterface(js, "PtiClicAndroid");
		webView.loadUrl(Constant.SERVER + Constant.SERVER_URL);
	}
	
	@Override
	public void onBackPressed() {
		webView.goBack();
	}

}
