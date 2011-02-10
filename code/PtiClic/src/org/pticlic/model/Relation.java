package org.pticlic.model;

import java.util.HashMap;

import org.pticlic.R;

/**
 * @author Bertrand BRUN
 * 
 * Cette classe permet de recuperer le noms ou l'image d'un relation en fonction du numero de son id.
 *
 */
public class Relation {
	// TODO : Penser a peut etre remplacer les HashMap par une BDD.
	
	private static Relation instance = null;
	
	HashMap<Integer, String> stringRelations;
	HashMap<Integer, Integer> imageRelations;
	
	private Relation() {
		imageRelations = new HashMap<Integer, Integer>();
		imageRelations.put(-1, android.R.drawable.ic_menu_delete);
		imageRelations.put(5, R.drawable.synonyme);
		imageRelations.put(7, R.drawable.contraire);
		imageRelations.put(9, R.drawable.contenu);
		imageRelations.put(10, R.drawable.contenant);
		
		stringRelations = new HashMap<Integer, String>();
		stringRelations.put(-1, "poubelle");
		stringRelations.put(0, "idée");
		stringRelations.put(1, "raffinement sémantique");
		stringRelations.put(2, "raffinement morphologique");
		stringRelations.put(3, "domaine");
		stringRelations.put(4, "r_pos");
		stringRelations.put(5, "synonyme");
		stringRelations.put(6, "générique");
		stringRelations.put(7, "contraire");
		stringRelations.put(8, "spécifique");
		stringRelations.put(9, "partie");
		stringRelations.put(10, "tout");
		stringRelations.put(11, "locution");
		stringRelations.put(12, "potentiel de FL");
		stringRelations.put(13, "action>agent");
		stringRelations.put(14, "action>patient");
		stringRelations.put(15, "chose>lieu");
		stringRelations.put(16, "action>instrument");
		stringRelations.put(17, "caractéristique");
		stringRelations.put(18, "r_data");
		stringRelations.put(19, "r_lemma");
		stringRelations.put(20, "magn");
		stringRelations.put(21, "antimagn");
		stringRelations.put(22, "famille");
		stringRelations.put(29, "predicat");
		stringRelations.put(30, "lieu>action");
		stringRelations.put(31, "action>lieu");
		stringRelations.put(32, "sentiment");
		stringRelations.put(33, "erreur");
		stringRelations.put(34, "manière");
		stringRelations.put(35, "sens/signification");
		stringRelations.put(36, "information potentielle");
		stringRelations.put(37, "rôle télique");
		stringRelations.put(38, "rôle agentif");
		stringRelations.put(41, "conséquence");
		stringRelations.put(42, "cause");
		stringRelations.put(52, "succession");
		stringRelations.put(53, "produit");
		stringRelations.put(54, "est le produit de");
		stringRelations.put(55, "s'oppose à");
	}

	public synchronized static Relation getInstance() {
		if (instance == null) {
			instance = new Relation();
		}
		return instance;
	}
	
	public String getRelationName(int id) {
		return stringRelations.get(id);
	}
	
	public Integer getRelationImage(int id) {
		return imageRelations.get(id);
	}
}
