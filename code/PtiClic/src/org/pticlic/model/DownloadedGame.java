package org.pticlic.model;

import java.io.Serializable;


/**
 * @author Bertrand BRUN
 * 
 * Classe metier reprensentant n'importe quel le jeu telecharger du serveur.
 *
 */
public abstract class DownloadedGame implements Serializable {

	private static final long 	serialVersionUID = 1L;
	
	protected int				gid;
	protected int				pgid;
	protected int 				id;	
	
	public DownloadedGame() {
		this.id = -1;
		this.gid = -1;
		this.pgid = -1;
	}	

	public DownloadedGame(int id, int gid, int pgid) {
		super();
		this.id = id;
		this.gid = gid;
		this.pgid = pgid;
	}
	
	public int getGid() {
		return gid;
	}

	public void setGid(int gid) {
		this.gid = gid;
	}

	public int getPgid() {
		return pgid;
	}

	public void setPgid(int pgid) {
		this.pgid = pgid;
	}

	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}
	
}
