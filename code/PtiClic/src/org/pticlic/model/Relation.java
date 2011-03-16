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
		imageRelations.put(-1, R.drawable.corbeille);
		imageRelations.put(0, R.drawable.rapport);
		imageRelations.put(5, R.drawable.synonyme);
		imageRelations.put(7, R.drawable.contraire);
		imageRelations.put(9, R.drawable.contenu);
		imageRelations.put(10, R.drawable.contenant);

		// ATTENTION ! Tout ce qui est ci-dessous est en double dans relations.php .
		stringRelations = new HashMap<Integer, String>();
		stringRelations.put(-1, "Mot non lié à '%s'");
		stringRelations.put(0, "'%s' est en rapport avec...");
		stringRelations.put(1, "raffinement sémantique"); // pas utilisé
		stringRelations.put(2, "raffinement morphologique"); // pas utilisé
		stringRelations.put(3, "domaine"); // pas utilisé
		stringRelations.put(4, "r_pos"); // pas utilisé
		stringRelations.put(5, "'%s' est un synonyme de...");
		stringRelations.put(6, "'%s' est une sorte de...");
		stringRelations.put(7, "Un contraire de '%s' est...");
		stringRelations.put(8, "Un spécifique de '%s' est...");
		stringRelations.put(9, "... est une partie de '%s'");
		stringRelations.put(10, "'%s' fait partie de...");
		stringRelations.put(11, "locution"); // pas utilisé
		stringRelations.put(12, "potentiel de FL"); // pas utilisé
		stringRelations.put(13, "Quoi/Qui pourrait '%s'");
		stringRelations.put(14, "action>patient"); // pas utilisé
		stringRelations.put(15, "Le lieu pour '%s' est...");
		stringRelations.put(16, "Un instrument pour '%s' est...");
		stringRelations.put(17, "Un caractéristique de '%s' est...");
		stringRelations.put(18, "r_data"); // pas utilisé
		stringRelations.put(19, "r_lemma"); // pas utilisé
		stringRelations.put(20, "magn"); // pas utilisé
		stringRelations.put(21, "antimagn"); // pas utilisé
		stringRelations.put(22, "'%s' est de la même famille que...");
		stringRelations.put(29, "predicat"); // pas utilisé
		stringRelations.put(30, "lieu>action"); // pas utilisé
		stringRelations.put(31, "action>lieu"); // pas utilisé
		stringRelations.put(32, "sentiment"); // pas utilisé
		stringRelations.put(33, "erreur"); // pas utilisé
		stringRelations.put(34, "manière"); // pas utilisé
		stringRelations.put(35, "sens/signification"); // pas utilisé
		stringRelations.put(36, "information potentielle"); // pas utilisé
		stringRelations.put(37, "rôle télique"); // pas utilisé
		stringRelations.put(38, "rôle agentif"); // pas utilisé
		stringRelations.put(41, "conséquence"); // pas utilisé
		stringRelations.put(42, "cause"); // pas utilisé
		stringRelations.put(52, "succession"); // pas utilisé
		stringRelations.put(53, "produit"); // pas utilisé
		stringRelations.put(54, "est le produit de"); // pas utilisé
		stringRelations.put(55, "s'oppose à"); // pas utilisé
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
