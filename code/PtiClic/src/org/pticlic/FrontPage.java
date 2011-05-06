package org.pticlic;

import org.pticlic.js.JavaScriptInterface;
import org.pticlic.model.Constant;

import android.app.Activity;
import android.os.Bundle;
import android.util.Log;
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
		webView.getSettings().setJavaScriptEnabled(true);
		
		js = new JavaScriptInterface(this);
		webView.addJavascriptInterface(js, "PtiClicAndroid");
		Log.i("[INFO]", Constant.SERVER + Constant.SERVER_URL);
		webView.loadUrl(Constant.SERVER + Constant.SERVER_URL);
	}
		
	@Override
	public void onBackPressed() {
		webView.goBack();
	}

}
