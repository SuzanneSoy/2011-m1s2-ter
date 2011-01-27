package model;

import java.util.Arrays;

public class GamePlayed {
	
	private int id;
	private String centre;
	private String[] cloud;
	private String[] category;
	private static GamePlayed instance = null;
	
	private GamePlayed(){
		this.id = -1;
		this.centre = "";
		this.cloud = null;
		this.category = null;
	}
	
	private GamePlayed(int id, String centre, String[] cloud, String[] category) {
		this.id = id;
		this.centre = centre;
		this.cloud = cloud;
		this.category = category;
	}
	
	public final static GamePlayed getInstance(){
		if(instance == null) instance = new GamePlayed();
		return instance;
	}
	
	public int getId() {
		return id;
	}
	public void setId(int id) {
		this.id = id;
	}
	public String getCentre() {
		return centre;
	}
	public void setCentre(String centre) {
		this.centre = centre;
	}
	public String[] getCloud() {
		return cloud;
	}
	public void setCloud(String[] cloud) {
		this.cloud = cloud;
	}
	public String[] getCategory() {
		return category;
	}
	public void setCategory(String[] category) {
		this.category = category;
	}
	
	@Override
	public String toString() {
		return "GamePlayed [id=" + id + ", centre=" + centre + ", cloud="
				+ Arrays.toString(cloud) + ", category=" + category + "]";
	}
}
