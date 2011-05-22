package org.pticlic.js;

import android.app.Activity;
import android.app.ProgressDialog;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;
import android.webkit.WebView;
import android.widget.Toast;

public class JavaScriptInterface {
	private Activity mContext;
	private ProgressDialog dialog;
	private String screen;
	private WebView webView;

    /** Instantie l'interface et initialise le context */ 
    public JavaScriptInterface(Activity c, WebView webView) {
        mContext = c;
        this.webView = webView;
    }
   
    /**
     * Permet de setter une valeur dans les preferences
     * 
     * @param aName Le nom de la preference 
     * @param aValue La valeur que l'on veux pour la preference
     */
    public void setPreference(String aName, String aValue) {
    	SharedPreferences prefs = PreferenceManager.getDefaultSharedPreferences(mContext);
    	prefs.edit().putString(aName, aValue).commit();
    }
    
    /** Permet de recupere une des preferences du systeme.
     * 
     * @param pref La preference que l'on veux recupere
     * @return La preference a recupere.
     */
    public String getPreference(String aName) {
    	SharedPreferences prefs = PreferenceManager.getDefaultSharedPreferences(mContext);
    	String res = prefs.getString(aName, "");
    	return res;
    }
    
    /** Permet d'afficher une progressbar 
     *	@param title Le titre a afficher par la ProgressBar
     *	@param message Le message a afficher par la progressBar 
     */
    public void show(String title, String message) {
    	dialog = ProgressDialog.show(mContext, title, message);
    }
    
    public void info(String title, String message) {
    	Toast.makeText(mContext, message, Toast.LENGTH_SHORT);
    }
    
    /** Permet de retirer l'affichage de la boite de dialog
     * 
     */
    public void dismiss() {
        if (dialog.isShowing())
        	dialog.dismiss();
    }
    
    public void switchCSS(String newTheme) {
    	webView.reload();
    }
    
    public boolean isAndroid() {
    	return true;
    }
    
    /** Permet de quitter l'application
     * 
     */
    public void exit() {
    	mContext.finish();
    }

	public void setScreen(String screen) {
		this.screen = screen;
	}

	public String getScreen() {
		return screen;
	}
}
