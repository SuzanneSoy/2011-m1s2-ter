package org.pticlic;

import org.pticlic.js.JavaScriptInterface;
import org.pticlic.model.Constant;

import android.app.Activity;
import android.os.Bundle;
import android.util.Log;
import android.webkit.WebChromeClient;
import android.webkit.WebView;

public class Main extends Activity {

	private WebView webView;
	private JavaScriptInterface js = null;
	
	/** Called when the activity is first created. */
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.frontpage);

		webView = (WebView) findViewById(R.id.webview);
		webView.getSettings().setJavaScriptEnabled(true);
		webView.setWebChromeClient(new WebChromeClient());
		webView.setVerticalScrollBarEnabled(false);
		webView.setHorizontalScrollBarEnabled(false);

		js = new JavaScriptInterface(this);
		webView.addJavascriptInterface(js, "PtiClicAndroid");
		Log.i("[INFO]", Constant.SERVER + Constant.SERVER_URL);	
	}
	
	@Override
	protected void onStart() {
		super.onStart();
		webView.loadUrl(Constant.SERVER + Constant.SERVER_URL);
	}
		
	@Override
	public void onBackPressed() {
		if (js.getScreen().equals("splash") || js.getScreen().equals("frontpage"))
			finish();
		else
			webView.goBack();
	}
	
	@Override
	protected void onStop() {
		super.onStop();

		finish();
	}
}