package org.pticlic.js;

import android.app.ProgressDialog;
import android.content.Context;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;

public class JavaScriptInterface {
	private Context mContext;
	private ProgressDialog dialog;

    /** Instantie l'interface et initialise le context */
    public JavaScriptInterface(Context c) {
        mContext = c;
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
    	return prefs.getString(aName, "");
    }
    
    /** Permet d'afficher une progressbar 
     *	@param title Le titre a afficher par la ProgressBar
     *	@param message Le message a afficher par la progressBar 
     */
    public void show(String title, String message) {
    	dialog = ProgressDialog.show(mContext, title, message);
    }
    
    /** Permet de retirer l'affichage de la boite de dialog
     * 
     */
    public void dismiss() {
        if (dialog.isShowing())
        	dialog.dismiss();
    }
    
    /** Permet de quitter l'application
     * 
     */
    public void exit() {
    	System.exit(0);
    }
}
